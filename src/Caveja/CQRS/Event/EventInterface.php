<?php
namespace Caveja\CQRS\Event;
use ValueObjects\Identity\UUID;

/**
 * Interface EventInterface
 * @package Caveja\Domain\Event
 */
interface EventInterface
{
    /**
     * @return UUID
     */
    public function getUuid();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return array
     */
    public function getData();

    /**
     * @return int
     */
    public function getVersion();

    /**
     * @param $id
     */
    public function setVersion($id);
}
