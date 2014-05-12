<?php
namespace Caveja\CQRS\Event\Bus;

/**
 * Interface EventBusInterface
 * @package Caveja\CQRS\Event\Bus
 */
interface EventBusInterface extends EventPublisherInterface, EventReceiverInterface
{
}
