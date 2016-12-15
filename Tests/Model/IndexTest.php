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

use PHPUnit\Framework\TestCase;
use RonteLtd\ElasticBundle\Model\Index;

/**
 * IndexTest
 *
 * @author Alexey Astafev <efsneiron@gmail.com>
 */
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    private $index;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->index = new Index([
            'index' => 'index'
        ]);

    }

    /**
     * Tests setter and getter for id
     *
     * @covers \RonteLtd\ElasticBundle\Model\Index::setId()
     * @covers \RonteLtd\ElasticBundle\Model\Index::getId()
     */
    public function testSetGetId() {
        $this->index->setId(1);
        self::assertEquals(1, $this->index->getId());
    }

    /**
     * Tests setter and getter for
     *
     * @covers \RonteLtd\ElasticBundle\Model\Index::setIndex()
     * @covers \RonteLtd\ElasticBundle\Model\Index::getIndex()
     */
    public function testSetGetIndex()
    {
        $this->index->setIndex('index');
        self::assertEquals('index', $this->index->getIndex());
    }

    /**
     * Tests setter and getter for type
     *
     * @covers \RonteLtd\ElasticBundle\Model\Index::setType()
     * @covers \RonteLtd\ElasticBundle\Model\Index::getType()
     */
    public function testSetGetType()
    {
        $this->index->setType('my_type');
        self::assertEquals('my_type', $this->index->getType());
    }

    /**
     * Tests setter and getter for settings
     *
     * @covers \RonteLtd\ElasticBundle\Model\Index::setSettings()
     * @covers \RonteLtd\ElasticBundle\Model\Index::getSettings()
     */
    public function testSetGetSettings()
    {
        $this->index->setSettings(['test' => 'test']);
        self::assertEquals(['test' => 'test'], $this->index->getSettings());
    }

    /**
     * Tests setter and getter for mappings
     *
     * @covers \RonteLtd\ElasticBundle\Model\Index::setMappings()
     * @covers \RonteLtd\ElasticBundle\Model\Index::getMappings()
     */
    public function testGetSetMappings()
    {
        $this->index->setMappings(['test' => 'test']);
        self::assertEquals(['test' => 'test'], $this->index->getMappings());
    }

    /**
     * Tests toArray
     *
     * @covers \RonteLtd\ElasticBundle\Model\Index::toArray()
     */
    public function testToArray()
    {
        $data = ['index' => 'new_index'];
        $index = new Index($data);
        self::assertEquals($data, $index->toArray());
    }

    /**
     * Tests toCreateIndexArray
     *
     * @covers \RonteLtd\ElasticBundle\Model\Index::toCreateIndexArray()
     */
    public function testToCreateIndexArray()
    {
        $data = [
            'index' => 'new_index',
            'type' => 'new_type',
            'settings' => [
                'flag' => true
            ],
            'mappings' => [
                'flag' => false
            ]
        ];

        $dataExpected = [
            'index' => 'new_index',
            'body' => [
                'settings' => [
                    'flag' => true
                ],
                'mappings' => [
                    'new_type' => [
                        'flag' => false
                    ]
                ]
            ]
        ];

        $index = new Index($data);
        self::assertEquals($dataExpected, $index->toCreateIndexArray());
    }

    /**
     * Tests toRemoveUpdateDocumentArray
     *
     * @covers \RonteLtd\ElasticBundle\Model\Index::toDocumentArray()
     */
    public function testToDocumentArray()
    {
        $data = [
            'index' => 'new_index',
            'type' => 'new_type',
            'id' => 1
        ];

        $index = new Index($data);
        self::assertEquals($data, $index->toDocumentArray() );
    }
}