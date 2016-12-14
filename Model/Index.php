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
    private $settings;

    /**
     * @var array
     */
    private $mappings;

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
     * Gets settings
     *
     * @return array
     */
    public function getSettings(): ?array
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
    public function getMappings(): ?array
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
            'index' => $this->getIndex(),
        ];
    }

    /**
     * Gets array for creating an index
     *
     * @returna array
     */
    public function toCreateIndexArray(): array
    {
        $toArray = $this->toArray();

        if ($this->getSettings()) {
            $toArray['body']['settings'] = $this->getSettings();
        }

        if ($this->getMappings()) {
            $toArray['body']['mappings'][$this->getType()] = $this->getMappings();
        }

        return $toArray;
    }

    /**
     * Gets array for removing a document
     *
     * @return array
     */
    public function toDocumentArray(): array
    {
        $toArray = $this->toArray();
        $toArray['type'] = $this->getType();
        $toArray['id'] = $this->getId();

        return $toArray;
    }
}