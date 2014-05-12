<?php
namespace Caveja\CQRS\Event\Bus;

/**
 * Interface EventReceiverInterface
 * @package Caveja\CQRS\Event\Bus
 */
interface EventReceiverInterface
{
    /**
     * @param EventSubscriberInterface $eventHandler
     */
    public function subscribe(EventSubscriberInterface $eventHandler);
}
