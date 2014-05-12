<?php
namespace Caveja\CQRS\Command\Handler;

use Caveja\CQRS\Command\CommandInterface;
use Foodlogger\Domain\Exception\InvalidArgumentException;

/**
 * Class CommandHandlerChain
 * @package Caveja\CQRS\Command\Handler
 */
class CommandHandlerChain implements CommandHandlerInterface, \Countable
{
    /**
     * @var array
     */
    private $handlers = [];

    /**
     * @param  CommandInterface $command
     * @return bool
     */
    public function canHandle(CommandInterface $command)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($command)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($command)) {
                return $handler->handle($command);
            }
        }

        throw new InvalidArgumentException('Cannot handle command: '.get_class($command));
    }

    /**
     * @param CommandHandlerInterface $handler
     */
    public function register(CommandHandlerInterface $handler)
    {
        if (!in_array($handler, $this->handlers)) {
            $this->handlers[] = $handler;
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->handlers);
    }
}
