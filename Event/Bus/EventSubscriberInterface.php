<?php
namespace Caveja\CQRS\Event\Bus;

use Caveja\CQRS\Event\EventInterface;

/**
 * Interface EventSubscriberInterface
 * @package Caveja\CQRS\Event\Bus
 */
interface EventSubscriberInterface
{
    /**
     * @param EventInterface $event
     */
    public function handle(EventInterface $event);
}
