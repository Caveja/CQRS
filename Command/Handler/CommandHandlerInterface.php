<?php
namespace Caveja\CQRS\Command\Handler;

use Caveja\CQRS\Command\CommandInterface;
use Caveja\CQRS\Event\EventInterface;

/**
 * Interface CommandHandlerInterface
 * @package Caveja\CQRS\Command\Handler
 */
interface CommandHandlerInterface
{
    /**
     * @param  CommandInterface $command
     * @return bool
     */
    public function canHandle(CommandInterface $command);

    /**
     * @param  CommandInterface                    $command
     * @return \Caveja\CQRS\Event\EventInterface[]
     */
    public function handle(CommandInterface $command);
}
