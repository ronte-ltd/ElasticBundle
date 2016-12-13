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

/**
 * Entity
 *
 * @author Alexey Astafev <efsneiron@gmail.com>
 */
class Entity extends AbstractEntity
{

    /**
     * Gets some defined data in array
     *
     * @return array
     */
    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }

    /**
     * Gets id
     *
     * @return int
     */
    public function getId(): int
    {
        // TODO: Implement getId() method.
    }
}