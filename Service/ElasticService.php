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

use Elasticsearch\ClientBuilder;
use RonteLtd\CommonBundle\Entity\EntityInterface;
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
     * @var \Symfony\Component\Yaml\Yaml
     */
    private $yaml;

    /**
     * ElasticService constructor.
     *
     * @param array $parameters
     * @param \Symfony\Component\Yaml\Yaml $yaml
     */
    public function __construct(array $parameters, Yaml $yaml)
    {
        $this->parameters = $parameters;
        $this->yaml = $yaml;

        $this->client = ClientBuilder::create()
            ->setHosts($this->parameters['hosts'])
            ->build();
    }

    /**
     * Adds a document
     *
     * @param EntityInterface $entity
     * @return ElasticService
     */
    public function addDocument(EntityInterface $entity): ElasticService
    {
        $data = $this->prepareData($entity);

        if ($data) {
            $this->createIndex($data['schema']);
            $this->client->index($data['params']);
        }

        return $this;
    }

    /**
     * Removes a document
     *
     * @param EntityInterface $entity
     * @return ElasticService
     */
    public function removeDocument(EntityInterface $entity): ElasticService
    {
        $data = $this->prepareData($entity);

        if ($data) {
            $params = [
                'index' => $data['params']['index'],
                'type' => $data['params']['type'],
                'id' => $data['params']['id']
            ];
            $this->client->delete($params);
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
        $data = $this->prepareData($entity);

        if ($data) {
            $data = $data['params'];
            $data['body']['doc'] = $data['body'];
            $this->client->update($data);
        }

        return $this;
    }

    /**
     * Prepares some data from an entity
     *
     * @param EntityInterface $entity
     * @return array
     */
    private function prepareData(EntityInterface $entity)
    {
        $data = [];

        if ($this->getSchema($entity)) {
            $schema = $this->getSchema($entity);
            $data = [
                'schema' => $schema,
                'params' => [
                    'index' => $schema['index'],
                    'type' => array_keys($schema['body']['mappings'])[0],
                    'id' => $entity->getId(),
                    'body' => $entity->toArray()
                ]
            ];
        }

        return $data;
    }

    /**
     * Checks whether an index exists
     *
     * @param $index
     * @return bool
     */
    private function hasIndex($index): bool
    {
        return $this->client->indices()->exists(['index' => $index]);
    }

    /**
     * Creates an index
     *
     * @param array $indexParams
     * @return ElasticService
     */
    private function createIndex(array $indexParams): ElasticService
    {
        if (false === $this->hasIndex($indexParams['index'])) {
            $this->client->indices()->create($indexParams);
        }

        return $this;
    }

    /**
     * Gets schema of an entity
     *
     * @param $entity
     * @return array
     */
    private function getSchema(EntityInterface $entity): array
    {
        $schema = [];

        foreach ($this->parameters['entities'] as $e) {
            if (get_class($entity) == $e['namespace']) {
                $schema = $this->yaml->parse(file_get_contents($e['schema']));
            }
        }

        return $schema;
    }
}