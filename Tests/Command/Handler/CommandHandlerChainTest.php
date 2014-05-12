<?php
namespace Caveja\CQRS\Tests\Command\Handler;

use Caveja\CQRS\Command\Handler\CommandHandlerChain;
use Caveja\Domain\Exception\InvalidArgumentException;

class CommandHandlerChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandHandlerChain
     */
    private $chain;

    protected function setUp()
    {
        $this->chain = new CommandHandlerChain();
    }

    public function testCanHandleIsCalled()
    {
        $chain = $this->chain;

        $commandHandler = $this->getMock('Caveja\\CQRS\\Command\\Handler\\CommandHandlerInterface');
        $chain->register($commandHandler);

        $command = $this->getMockCommand();

        $commandHandler
            ->expects($this->once())
            ->method('canHandle')
            ->with($command)
            ->willReturn(true)
        ;

        $this->assertTrue($chain->canHandle($command));
    }

    public function testCanHandleIsCalledInTwoHandlers()
    {
        $commandHandler1 = $this->createCommandHandlerAndRegister();
        $commandHandler1->trick = true;

        $commandHandler2 = $this->createCommandHandlerAndRegister();
        $commandHandler2->trick = false;

        $command = $this->getMock('Caveja\\CQRS\\Command\\CommandInterface');

        $commandHandler1
            ->expects($this->once())
            ->method('canHandle')
            ->with($command)
            ->willReturn(false)
        ;

        $commandHandler2
            ->expects($this->once())
            ->method('canHandle')
            ->with($command)
            ->willReturn(true)
        ;

        $this->assertTrue($this->chain->canHandle($command));
    }

    public function testEmptyChainReturnsFalse()
    {
        $this->assertFalse($this->chain->canHandle($this->getMock('Caveja\\CQRS\\Command\\CommandInterface')));
    }

    public function testHandleIsPropagated()
    {
        $commandHandler = $this->createCommandHandlerAndRegister();

        $command = $this->getMockCommand();

        $commandHandler
            ->expects($this->once())
            ->method('canHandle')
            ->with($command)
            ->willReturn(true)
        ;

        $commandHandler
            ->expects($this->once())
            ->method('handle')
            ->with($command)
        ;

        $this->chain->handle($command);
    }

    public function testOnlyFirstHandlerIsCalled()
    {
        $commandHandler1 = $this->createCommandHandlerAndRegister();
        $commandHandler2 = $this->createCommandHandlerAndRegister();

        $command = $this->getMockCommand();

        $commandHandler1
            ->expects($this->once())
            ->method('canHandle')
            ->with($command)
            ->willReturn(true)
        ;

        $commandHandler1
            ->expects($this->once())
            ->method('handle')
            ->with($command)
        ;

        $commandHandler2
            ->expects($this->never())
            ->method('canHandle')
            ->willReturn(true)
        ;

        $commandHandler2
            ->expects($this->never())
            ->method('handle')
        ;

        $this->chain->handle($command);
    }

    public function testCountOnEmptyChainReturnsZero()
    {
        $this->assertSame(0, count($this->chain));
    }

    public function testCountReturnsOneAfterAddingOneHandler()
    {
        $this->createCommandHandlerAndRegister();
        $this->assertSame(1, count($this->chain));
    }

    public function testCommandIsNotRegisteredTwice()
    {
        $handler = $this->createCommandHandlerAndRegister();
        $this->chain->register($handler);

        $this->assertSame(1, count($this->chain));
    }

    public function testAnEventIsReturned()
    {
        $event = $this->getMock('Caveja\\CQRS\\Event\\EventInterface');

        $handler = $this->createCommandHandlerAndRegister();

        $handler
            ->expects($this->any())
            ->method('canHandle')
            ->willReturn(true)
        ;

        $handler
            ->expects($this->any())
            ->method('handle')
            ->willReturn($event)
        ;

        $command = $this->getMockCommand();

        $this->assertEquals($event, $this->chain->handle($command));
    }

    /**
     * @expectedException Caveja\CQRS\Exception\InvalidArgumentException
     */
    public function testNotHandledEventThrowsException()
    {
        $this->chain->handle($this->getMockCommand());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createCommandHandlerAndRegister()
    {
        $commandHandler = $this->getMock('Caveja\\CQRS\\Command\\Handler\\CommandHandlerInterface');
        $this->chain->register($commandHandler);

        return $commandHandler;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockCommand()
    {
        return $this->getMock('Caveja\\CQRS\\Command\\CommandInterface');
    }
}
