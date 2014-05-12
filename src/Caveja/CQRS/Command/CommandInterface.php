<?php
namespace Caveja\CQRS\Command;

use ValueObjects\Identity\UUID;

/**
 * Interface CommandInterface
 * @package Caveja\Domain\Command
 */
interface CommandInterface
{
    /**
     * @return UUID
     */
    public function getAggregateId();
}
