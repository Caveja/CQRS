<?php
namespace Caveja\CQRS\Event;

use ValueObjects\Identity\UUID;

/**
 * Class DomainEvent
 * @package Caveja\CQRS\Event
 */
class DomainEvent implements EventInterface
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $data;
    /**
     * @var int
     */
    private $version;

    /**
     * @param UUID   $uuid
     * @param string $type
     * @param array  $data
     */
    public function __construct(UUID $uuid, $type, array $data)
    {
        $this->uuid = $uuid;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @return UUID
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param  array       $array
     * @return DomainEvent
     */
    public static function fromArray(array $array)
    {
        $event = new self(new UUID($array['_id']), $array['type'], $array['data']);

        $event->setVersion($array['version']);

        return $event;
    }

    /**
     * @param  string      $type
     * @param  array       $data
     * @return DomainEvent
     */
    public static function create($type, array $data)
    {
        return new self(new UUID(), $type, $data);
    }
}
