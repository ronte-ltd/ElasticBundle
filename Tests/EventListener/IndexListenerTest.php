<?php
/*
 * This file is part of ElasticBundle the package.
 *
 * (c) Alexey Astafev <efsneiron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RonteLtd\ElasticBundle\Tests;

use RonteLtd\ElasticBundle\EventListener\IndexListener;
use RonteLtd\ElasticBundle\Tests\Fixtures\Entity;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * IndexListenerTest
 *
 * @author Alexey Astafev <efsneiron@gmail.com>
 */
class IndexListenerTest extends WebTestCase
{
    /**
     * @var \RonteLtd\ElasticBundle\Service\ElasticService
     */
    private $service;

    /**
     * @var \RonteLtd\ElasticBundle\EventListener\IndexListener
     */
    private $listener;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \RonteLtd\ElasticBundle\Tests\Fixtures\Entity
     */
    private $entity;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $client = static::createClient();
        $this->service = $client->getContainer()
            ->get('ronte_ltd_elastic.elastic_service');
        $this->em = $client->getContainer()->get('doctrine')->getManager();
        $this->entity = new Entity([
            'name' => 'Vasia',
        ]);
        $this->em->persist($this->entity);
        $this->em->flush($this->entity);
    }

    /**
     * Tests constructor
     */
    public function testConstruct()
    {
        $listener = new IndexListener($this->service);

        self::assertInstanceOf('\RonteLtd\ElasticBundle\EventListener\IndexListener', $listener);
    }

    /**
     * Tests postUpdate
     *
     * @covers \RonteLtd\ElasticBundle\EventListener\IndexListener::postUpdate()
     */
    public function testPostUpdate()
    {
        $this->entity->setName('Mark');
        $this->em->persist($this->entity);
        $this->em->flush();
    }

    /**
     * Tests preRemove
     *
     * @covers \RonteLtd\ElasticBundle\EventListener\IndexListener::preRemove()
     */
    public function testPreRemove()
    {
        $this->em->remove($this->entity);
        $this->em->flush();
    }
}