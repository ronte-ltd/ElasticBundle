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

    private $parameters;

    public function setUp()
    {
        $client = static::createClient();
        $this->parameters = $client->getContainer()->getParameter('ronte_ltd_elastic');
        $em = $client->getContainer()->get('doctrine')->getManager();
        $this->service = new ElasticService($this->parameters, $em);
        $this->esclient = $this->service->getClient();
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
        $entity = new Entity();
        $index = $this->service->constructIndex($entity);
        self::assertInstanceOf('RonteLtd\ElasticBundle\Model\Index', $index);
    }

    /**
     * Tests saveIndex
     *
     * @covers \RonteLtd\ElasticBundle\Service\ElasticService::saveIndex()
     */
    public function saveIndex()
    {
        $entity = new Entity();
        $index = $this->service->saveIndex($this->service->constructIndex($entity));
        self::assertInstanceOf('RonteLtd\ElasticBundle\Model\Index', $index);
        self::assertTrue($this->esclient->indices()->exists(['index' => $index->getIndex()]));
        $this->esclient->indices()->delete(['index' => $index->getIndex()]);
    }

    /**
     *
     */
    public function testDeleteIndex()
    {
        $entity = new Entity();
        $objIndex = $this->service->constructIndex($entity);
        $index = $this->service->saveIndex($objIndex);
        $this->service->deleteIndex($index);
        self::assertFalse($this->esclient->indices()->exists(['index' => $index->getIndex()]));
    }
}