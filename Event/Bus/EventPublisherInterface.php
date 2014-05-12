<?php
namespace Caveja\CQRS\Event\Bus;

use Caveja\CQRS\Event\EventInterface;

/**
 * Interface EventPublisherInterface
 * @package Caveja\CQRS\Event\Bus
 */
interface EventPublisherInterface
{
    /**
     * @param EventInterface $event
     */
    public function publish(EventInterface $event);
}
