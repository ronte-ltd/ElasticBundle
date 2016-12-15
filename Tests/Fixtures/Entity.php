<?php
/*
 * This file is part of ElasticBundle the package.
 *
 * (c) Alexey Astafev <efsneiron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RonteLtd\ElasticBundle\Tests\Fixtures;

use RonteLtd\CommonBundle\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="test_es_entity")
 * @author Alexey Astafev <efsneiron@gmail.com>
 */
class Entity extends AbstractEntity
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * Gets some defined data in array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name
        ];
    }

    /**
     * Gets id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Entity
     */
    public function setId(int $id): Entity
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Entity
     */
    public function setName(string $name): Entity
    {
        $this->name = $name;

        return $this;
    }
}