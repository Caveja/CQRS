<?php
namespace Caveja\CQRS\Repository;

use Caveja\CQRS\Model\AggregateRoot;
use ValueObjects\Identity\UUID;

/**
 * Interface RepositoryInterface
 * @package Caveja\Domain\Tests
 */
interface RepositoryInterface
{
    /**
     * @param AggregateRoot $aggregate
     * @param int           $expectedVersion
     */
    public function save(AggregateRoot $aggregate, $expectedVersion);

    /**
     * @param  UUID          $id
     * @return AggregateRoot
     */
    public function getById(UUID $id);
}
