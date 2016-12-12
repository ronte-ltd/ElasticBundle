<?php
/*
 * This file is part of ElasticBundle the package.
 *
 * (c) Alexey Astafev <efsneiron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RonteLtd\ElasticBundle\Model;

use RonteLtd\CommonBundle\Entity\AbstractEntity;

/**
 * Index
 *
 * @author Alexey Astafev <efsneiron@gmail.com>
 */
class Index extends AbstractEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $index;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $body;

    /**
     * @var array
     */
    private $settings;

    /**
     * @var array
     */
    private $mappings;

    /**
     * Index constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->setBody([
            $this->getType() => [
                'settings' => $this->getSettings(),
                'mappings' => $this->getMappings()
            ]
        ]);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Index
     */
    public function setId(int $id): Index
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @param string $index
     * @return Index
     */
    public function setIndex(string $index): Index
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Index
     */
    public function setType(string $type): Index
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @param array $body
     * @return Index
     */
    public function setBody(array $body): Index
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Gets settings
     *
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Sets settings
     *
     * @param array $settings
     * @return Index
     */
    public function setSettings(array $settings): Index
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Gets mappings
     *
     * @return array
     */
    public function getMappings(): array
    {
        return $this->mappings;
    }

    /**
     * Sets mappings
     *
     * @param array $mappings
     * @return Index
     */
    public function setMappings(array $mappings): Index
    {
        $this->mappings = $mappings;

        return $this;
    }

    /**
     * Gets some defined data in array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'index' => $this->getIndex(),
            'type' =>  $this->getType()
        ];
    }

    /**
     * Gets an array for creating index
     *
     * @return array
     */
    public function toCreateArray()
    {
        return [
            'index' => $this->getIndex(),
            'body' => [
                'settings' => $this->getSettings(),
                'mappings' => [
                    $this->getType() => $this->getMappings()
                ]
            ]
        ];
    }

    /**
     * Gets an array for deleting index
     *
     * @return array
     */
    public function toDeleteArray()
    {
        return [
            'index' => $this->getIndex(),
        ];
    }
}