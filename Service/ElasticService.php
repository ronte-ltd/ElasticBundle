<?php
/*
 * This file is part of ElasticBundle the package.
 *
 * (c) Alexey Astafev <efsneiron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RonteLtd\ElasticBundle\Service;

use Doctrine\ORM\EntityManager;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use RonteLtd\CommonBundle\Entity\EntityInterface;
use RonteLtd\ElasticBundle\Model\Index;
use Symfony\Component\Yaml\Yaml;

/**
 * ElasticService
 *
 * @author Alexey Astafev <efsneiron@gmail.com>
 */
class ElasticService
{
    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * ElasticService constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters, EntityManager $entityManager)
    {
        $this->parameters = $parameters;
        $this->entityManager = $entityManager;
        $this->client = ClientBuilder::create()
            ->setHosts($this->parameters['hosts'])
            ->build();
    }

    /**
     * Checks whether an entity is valid
     *
     * @param EntityInterface $entity
     * @return bool
     */
    public function isValidEntity(EntityInterface $entity): bool
    {
        return in_array(get_class($entity), array_keys($this->parameters['entities']));
    }

    /**
     * Checks whether an index exists
     *
     * @param $index
     * @return bool
     */
    public function hasIndex(Index $index): bool
    {
        return $this->client->indices()->exists(['index' => $index->getIndex()]);
    }

    /**
     * Parses a yaml file by path
     *
     * @param string $path
     * @return array
     */
    public function parse(string $path): array
    {
        return Yaml::parse(file_get_contents($path));
    }

    /**
     * Creates as index
     *
     * @param EntityInterface $entity
     * @return Index
     */
    public function constructIndex(EntityInterface $entity): ? Index
    {
        if ($this->isValidEntity($entity)) {
            $schema = $this->parse($this->parameters['entities'][get_class($entity)]);
            $index = new Index($schema);

            return $index;
        }

        return null;
    }

    /**
     * Saves an index
     *
     * @param Index $index
     * @return Index
     */
    public function saveIndex(Index $index): Index
    {
        if (false === $this->hasIndex($index)) {
            $this->client->indices()->create($index->toCreateIndexArray());
        }

        return $index;
    }

    /**
     * Deletes an index
     *
     * @param Index $index
     * @return ElasticService
     */
    public function deleteIndex(Index $index): ElasticService
    {
        if ($this->hasIndex($index)) {
            $this->client->indices()->delete($index->toArray());
        }

        return $this;
    }

    /**
     * Adds documents
     *
     * @param array $data
     * @param Index $index
     * @param int $limit
     * @return ElasticService
     */
    public function addDocuments(array $data = [], Index $index, int $limit = 1000): ElasticService
    {
        $params = ['body' => []];
        $i = 1;

        foreach ($data as $d) {

            if ($this->isValidEntity($d) && $d instanceof EntityInterface) {
                $params['body'][] = [
                    'index' => [
                        '_index' => $index->getIndex(),
                        '_type' => $index->getType(),
                        '_id' => $d->getId()
                    ]
                ];
                $params['body'][] = $d->toArray();

                // Every $limit documents stop and send the bulk request
                if ($i % $limit == 0) {
                    $responses = $this->client->bulk($params);

                    // erase the old bulk request
                    $params = ['body' => []];

                    // unset the bulk response when you are done to save memory
                    unset($responses);
                }

                $i++;
            }
        }

        // Send the last batch if it exists
        if (!empty($params['body'])) {
            $this->client->bulk($params);
        }

        return $this;
    }

    /**
     * Gets a document
     *
     * @param EntityInterface $entity
     * @return array
     */
    public function getDocument(EntityInterface $entity): ? array
    {
        $index = $this->constructIndex($entity);
        $result = null;

        if ($index) {
            $index->setId($entity->getId());

            try {
                $result = $this->client->get($index->toDocumentArray());
            } catch (\Exception $exception) {
            }
        }

        return $result;
    }

    /**
     * Removes a document
     *
     * @param EntityInterface $entity
     * @return ElasticService
     */
    public function removeDocument(EntityInterface $entity): ElasticService
    {
        $index = $this->constructIndex($entity);

        if ($index && $this->getDocument($entity)) {
            $index->setId($entity->getId());
            $this->client->delete($index->toDocumentArray());
        }

        return $this;
    }

    /**
     * Updates a document
     *
     * @param EntityInterface $entity
     * @return ElasticService
     */
    public function updateDocument(EntityInterface $entity): ElasticService
    {
        $index = $this->constructIndex($entity);

        if ($index && $this->getDocument($entity)) {
            $index->setId($entity->getId());
            $params = $index->toDocumentArray();
            $params['body']['doc'] = $entity->toArray();
            $this->client->update($params);
        }

        return $this;
    }

    /**
     * Reconfigures list of entities in the elastic service
     *
     * @param string $entityNamespace
     * @return ElasticService
     */
    public function reconfigure(string $entityNamespace): ElasticService
    {
        $entity = new $entityNamespace();
        $newIndex = $this->constructIndex($entity);
        $index = $this->constructIndex($entity);

        if ($newIndex && $index) {
            $query = $this->entityManager
                ->createQuery("select u from " . $entityNamespace . " u");
            $newIndex->setIndex('new_' . $newIndex->getIndex());
            $this->deleteIndex($newIndex);
            $this->saveIndex($newIndex);
            $this->addDocuments($query->getResult(), $newIndex);
            $this->client->indices()->flush(['index' => $newIndex->getIndex()]);
            $this->deleteIndex($index);
            $this->saveIndex($index);
            $params = [
                'body' => [
                    'source' => [
                        'index' => $newIndex->getIndex()
                    ],
                    'dest' => [
                        'index' => $index->getIndex()
                    ]
                ]
            ];
            $this->client->reindex($params);
        }

        return $this;
    }

    /**
     * Gets a client of the elastic service
     *
     * @return \Elasticsearch\Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}