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
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var ContainerInterface
     */
    private $container;

    /**
     * ElasticService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->parameters = $container->getParameter('ronte_ltd_elastic');
        $this->container = $container;

        $this->client = ClientBuilder::create()
            ->setHosts($this->parameters['hosts'])
            ->build();
    }

    /**
     * Searches documents
     *
     * @param array $parameters
     * @return array
     */
    public function search(array $parameters = []): array
    {
        return $this->client->search($parameters);
    }

    /**
     * Indexes a document
     *
     * @param EntityInterface $entity
     * @return array
     */
    public function index(EntityInterface $entity): array
    {
        return $this->manipulate($entity, 'index');
    }

    /**
     * Updates an document
     *
     * @param EntityInterface $entity
     * @return array
     */
    public function update(EntityInterface $entity): array
    {
        return $this->manipulate($entity, 'update');
    }

    /**
     * Removes a document
     *
     * @param EntityInterface $entity
     * @return array
     */
    public function remove(EntityInterface $entity): array
    {
        return $this->manipulate($entity, 'remove');
    }

    /**
     * Manipulates with a document
     *
     * @param EntityInterface $entity
     * @param $action
     * @return array
     */
    private function manipulate(EntityInterface $entity, $action): array
    {
        $className = get_class($entity);

        foreach ($this->parameters['entities'] as $p) {
            if ($p['namespace'] == $className) {
                $indexArray = explode('\\', $className);
                $index = strtolower(array_pop($indexArray));
                $entityData = $this->container->get('serializer')->normalize($entity);

                if ($p['groups']) {
                    $entityData = $this->container->get('serializer')->normalize(
                        $entity, null, ['groups' => $p['groups']]
                    );
                }

                $elasticData = [
                    'index' => $index,
                    'type' => $index,
                    'id' => $entity->getId(),
                    'body' => $entityData,
                ];

                switch ($action) {
                    case 'index':
                        return $this->client->index($elasticData);
                        break;
                    case 'update':
                        $elasticData['body'] = [
                            'doc' => $entityData
                        ];

                        return $this->client->update($elasticData);
                        break;
                    case 'remove':
                        return $this->client->delete($elasticData);
                        break;
                }
            }
        }
    }
}