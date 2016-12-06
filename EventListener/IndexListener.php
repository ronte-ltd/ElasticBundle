<?php
/*
 * This file is part of ElasticBundle the package.
 *
 * (c) Alexey Astafev <efsneiron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RonteLtd\ElasticBundle\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Elasticsearch\ClientBuilder;
use RonteLtd\ElasticBundle\Service\ElasticService;

/**
 * IndexListener
 *
 * @author Alexey Astafev <efsneiron@gmail.com>
 */
class IndexListener
{
    /**
     * @var ElasticService
     */
    private $service;

    /**
     * IndexListener constructor.
     * @param ElasticService $service
     */
    public function __construct(ElasticService $service)
    {
        $this->service = $service;
    }

    /**
     * Creates a document in service of elastic search
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        $this->service->index($entity);
    }

    /**
     * Updates a document in service of elastic search
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        $this->service->update($entity);
    }

    /**
     * Removes a document in service of elastic search
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        $this->service->remove($entity);
    }
}