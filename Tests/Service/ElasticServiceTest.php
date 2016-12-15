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

use RonteLtd\ElasticBundle\Model\Index;
use RonteLtd\ElasticBundle\Service\ElasticService;
use RonteLtd\ElasticBundle\Tests\Fixtures\Entity;
use RonteLtd\ElasticBundle\Tests\Fixtures\Entity2;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * ElasticServiceTest
 *
 * @author Alexey Astafev <efsneiron@gmail.com>
 */
class ElasticServiceTest extends WebTestCase
{
    /**
     * @var ElasticService
     */
    private $service;

    /**
     * @var \Elasticsearch\Client
     */
    private $esclient;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \RonteLtd\ElasticBundle\Tests\Fixtures\Entity
     */
    private $entity;

    public function setUp()
    {
        $client = static::createClient();
        $this->parameters = $client->getContainer()->getParameter('ronte_ltd_elastic');
        $this->em = $client->getContainer()->get('doctrine')->getManager();
        $this->service = new ElasticService($this->parameters, $this->em);
        $this->esclient = $this->service->getClient();
        $this->entity = new Entity([
            'name' => 'tester',
        ]);
        $this->em->persist($this->entity);
        $this->em->flush($this->entity);
    }

    /**
     * Tests isValidEntity
     *
     * @covers \RonteLtd\ElasticBundle\Service\ElasticService::isValidEntity()
     */
    public function testIsValidEntity()
    {
        self::assertTrue($this->service->isValidEntity(new Entity()));
        self::assertFalse($this->service->isValidEntity(new Entity2()));
    }

    /**
     * Tests getter for the elastic client
     *
     * @covers \RonteLtd\ElasticBundle\Service\ElasticService::getClient()
     */
    public function testGetClient()
    {
        self::assertInstanceOf('Elasticsearch\Client', $this->service->getClient());
    }

    /**
     * Tests hasIndex
     *
     * @covers \RonteLtd\ElasticBundle\Service\ElasticService::hasIndex()
     */
    public function testHasIndex()
    {
        $indexName = bin2hex(random_bytes(10));
        $index = new Index([
            'index' => $indexName
        ]);
        self::assertFalse($this->service->hasIndex($index));
        $this->esclient->indices()->create(['index' => $indexName]);
        self::assertTrue($this->service->hasIndex($index));
        $this->esclient->indices()->delete(['index' => $indexName]);
    }

    /**
     * Tests parse
     *
     * @covers \RonteLtd\ElasticBundle\Service\ElasticService::parse()
     */
    public function testParse()
    {
        $result = $this->service->parse($this->parameters['entities']['RonteLtd\ElasticBundle\Tests\Fixtures\Entity']);
        self::assertNotEmpty($result);
    }

    /**
     * Tests constructIndex
     *
     * @covers \RonteLtd\ElasticBundle\Service\ElasticService::constructIndex()
     */
    public function testConstructIndex()
    {
        $entity2 = new Entity2();
        $index = $this->service->constructIndex($this->entity);
        $index2 = $this->service->constructIndex($entity2);
        self::assertInstanceOf('RonteLtd\ElasticBundle\Model\Index', $index);
        self::assertNull($index2);
    }

    /**
     * Tests saveIndex
     *
     * @covers \RonteLtd\ElasticBundle\Service\ElasticService::saveIndex()
     */
    public function testSaveIndex()
    {
        $index = $this->service->saveIndex($this->service->constructIndex($this->entity));
        self::assertInstanceOf('RonteLtd\ElasticBundle\Model\Index', $index);
        self::assertTrue($this->esclient->indices()->exists(['index' => $index->getIndex()]));
        $this->esclient->indices()->delete(['index' => $index->getIndex()]);
    }

    /**
     * Deletes an index
     *
     * @covers \RonteLtd\ElasticBundle\Service\ElasticService::deleteIndex()
     */
    public function testDeleteIndex()
    {
        $objIndex = $this->service->constructIndex($this->entity);
        $index = $this->service->saveIndex($objIndex);
        $this->service->deleteIndex($index);
        self::assertFalse($this->esclient->indices()->exists(['index' => $index->getIndex()]));
    }

    /**
     * Tests addDocument
     *
     * @covers \RonteLtd\ElasticBundle\Service\ElasticService::addDocuments()
     */
    public function testAddDocument()
    {
        $index = $this->service->constructIndex($this->entity);
        $this->service->saveIndex($index);
        $this->service->addDocuments([$this->entity], $index, 1);
        $document = $this->esclient->get([
            'id' => $this->entity->getId(),
            'index' => $index->getIndex(),
            'type' => $index->getType()
        ]);
        self::assertEquals($this->entity->getName(), $document['_source']['name']);
    }

    /**
     * Tests getDocument
     *
     * @covers \RonteLtd\ElasticBundle\Service\ElasticService::getDocument()
     */
    public function testGetDocument()
    {
        $document = $this->service->getDocument($this->entity);
        self::assertEquals($this->entity->getName(), $document['_source']['name']);
        $this->entity->setId(111);
        $document = $this->service->getDocument($this->entity);
        self::assertNull($document);

    }

    /**
     * Tests removeDocument
     *
     * @covers \RonteLtd\ElasticBundle\Service\ElasticService::removeDocument()
     */
    public function testRemoveDocument()
    {
        self::assertInstanceOf(
            'RonteLtd\ElasticBundle\Service\ElasticService',
            $this->service->removeDocument($this->entity)
        );

        self::assertNull($this->service->getDocument($this->entity));
    }

    /**
     * Tests updateDocument
     *
     * @covers \RonteLtd\ElasticBundle\Service\ElasticService::updateDocument()
     */
    public function testUpdateDocument()
    {
        $this->entity->setName('Vasya');
        self::assertInstanceOf(
            'RonteLtd\ElasticBundle\Service\ElasticService',
            $this->service->updateDocument($this->entity)
        );
        $document = $this->service->getDocument($this->entity);
        self::assertEquals($this->entity->getName(), $document['_source']['name']);
    }

    /**
     * Tests reconfigure
     */
    public function testReconfigure()
    {
        self::assertInstanceOf(
            'RonteLtd\ElasticBundle\Service\ElasticService',
            $this->service->reconfigure('RonteLtd\ElasticBundle\Tests\Fixtures\Entity')
        );
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        $this->service->deleteIndex($this->service->constructIndex(new Entity()));
    }
}