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

    public function setUp()
    {
        $client = static::createClient();
        $parameters = $client->getContainer()->getParameter('ronte_ltd_elastic');
        $em = $client->getContainer()->get('doctrine')->getManager();
        $this->service = new ElasticService($parameters, $em);
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
     */
    public function testGetClient()
    {
        self::assertInstanceOf('Elasticsearch\Client', $this->service->getClient());
    }
}