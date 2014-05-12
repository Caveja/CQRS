<?php
namespace Caveja\CQRS\Tests\Event\Store;

use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\Database;
use Caveja\CQRS\Event\Store\MongoEventStore;
use Caveja\CQRS\Event\Bus\EventPublisherInterface;
use Caveja\CQRS\Event\Bus\InMemoryEventBus;
use Caveja\CQRS\Tests\MongoTestUtils;

/**
 * Class MongoEventStoreTest
 * @package Caveja\CQRS\Tests\Event\Store
 * @group database
 */
class MongoEventStoreTest extends EventStoreTest
{
    use \Caveja\CQRS\Tests\MongoTestUtils;

    /**
     * @var Database
     */
    private $db;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param  EventPublisherInterface $eventPublisher
     * @return MongoEventStore
     */
    protected function createEventStore(EventPublisherInterface $eventPublisher)
    {
        return new MongoEventStore($this->db, $eventPublisher);
    }

    protected function setUp()
    {
        $this->connection = new Connection();
        $this->db = $this->connection->selectDatabase('caveja-cqrs-test');
    }

    protected function tearDown()
    {
        //$this->db->drop();
        $this->connection->close();
    }

    public function testFieldsAreIndexed()
    {
        $this->createEventStore(new InMemoryEventBus());
        $collection = $this->db->selectCollection('events');

        $this->assertTrue($collection->isFieldIndexed('aggregate_id'), 'There should be an index on aggregate_id');
        $this->assertTrue($collection->isFieldIndexed('version'), 'There should be and index on version');
        $this->assertTrue($collection->isFieldIndexed('type'), 'There should be and index on type');
    }
}
