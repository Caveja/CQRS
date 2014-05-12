<?php
namespace Caveja\CQRS\Model;

use Caveja\CQRS\Event\EventInterface;
use ValueObjects\Identity\UUID;

/**
 * Class AggregateRoot
 * @package Caveja\CQRS\Model
 */
abstract class AggregateRoot
{
    /**
     * @var array
     */
    private $handlers;

    /**
     * @var array
     */
    private $changes = [];

    /**
     * @var int
     */
    private $version = 0;

    /**
     * @param array $handlers
     */
    protected function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @return EventInterface[]
     */
    public function getUncommittedChanges()
    {
        return $this->changes;
    }

    /**
     * @param \Iterator $events
     */
    public function loadFromHistory(\Iterator $events)
    {
        foreach ($events as $event) {
            $this->apply($event, false);
        }

        if (isset($event)) {
            $this->setVersion($event->getVersion());
        }
    }

    /**
     * Empties pending changes
     */
    public function markChangesAsCommitted()
    {
        $this->changes = [];
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Should be used by EventStore only
     *
     * @param $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return UUID
     */
    abstract public function getId();

    /**
     * @param  EventInterface            $event
     * @param  bool                      $new
     * @throws \InvalidArgumentException
     */
    protected function apply(EventInterface $event, $new)
    {
        $type = $event->getType();
        $this->handlers[$type]($event);

        if ($new) {
            $this->changes[] = $event;
        }
    }
}
