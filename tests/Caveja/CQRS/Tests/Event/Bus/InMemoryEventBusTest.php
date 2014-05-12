<?php
namespace Caveja\CQRS\Tests\Event\Bus;

use Caveja\CQRS\Event\EventInterface;
use Caveja\CQRS\Event\Bus\EventSubscriberInterface;
use Caveja\CQRS\Event\Bus\InMemoryEventBus;
use Caveja\CQRS\Event\DomainEvent;

class InMemoryEventBusTest extends \PHPUnit_Framework_TestCase implements EventSubscriberInterface
{
    /**
     * @var InMemoryEventBus
     */
    private $publisher;

    /**
     * @var EventInterface
     */
    private $event;

    protected function setUp()
    {
        $this->publisher = new InMemoryEventBus();
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
