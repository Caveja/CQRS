<?php
namespace Caveja\CQRS\Tests\Event\Bus;

use Caveja\CQRS\Event\EventInterface;
use Caveja\CQRS\Event\Bus\EventSubscriberInterface;
use Caveja\CQRS\Event\Bus\InMemoryEventBus;
use Caveja\CQRS\Event\DomainEvent;

class InMemoryEventBusTest extends \PHPUnit_Framework_TestCase implements \Caveja\CQRS\Event\Bus\EventSubscriberInterface
{
    /**
     * @var \Caveja\CQRS\Event\Bus\InMemoryEventBus
     */
    private $publisher;

    /**
     * @var EventInterface
     */
    private $event;

    protected function setUp()
    {
        $this->publisher = new \Caveja\CQRS\Event\Bus\InMemoryEventBus();
    }

    protected function tearDown()
    {
        $this->event = null;
    }

    public function testSimple()
    {
        $this->publisher->subscribe($this);

        $event = DomainEvent::create('SomethingHappened', []);

        $this->publisher->publish($event);

        $this->assertEquals($event, $this->event);
    }

    /**
     * @param EventInterface $event
     */
    public function handle(EventInterface $event)
    {
        $this->event = $event;
    }
}
