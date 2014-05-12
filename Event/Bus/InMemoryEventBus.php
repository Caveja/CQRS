<?php
namespace Caveja\CQRS\Event\Bus;

use Caveja\CQRS\Event\EventInterface;

/**
 * Class InMemoryEventBus
 * @package Caveja\CQRS\Event\Bus
 */
class InMemoryEventBus implements EventBusInterface
{
    /**
     * @var EventSubscriberInterface[]
     */
    private $handlers = [];

    /**
     * @param \Caveja\CQRS\Event\EventInterface $event
     */
    public function publish(EventInterface $event)
    {
        foreach ($this->handlers as $handler) {
            $handler->handle($event);
        }
    }

    /**
     * @param EventSubscriberInterface $handler
     */
    public function subscribe(EventSubscriberInterface $handler)
    {
        $this->handlers[] = $handler;
    }
}
