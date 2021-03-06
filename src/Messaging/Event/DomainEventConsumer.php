<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Event\Exception\DomainEventHandlerFailedMessage;
use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\QueueMessageConsumer;

class DomainEventConsumer implements QueueMessageConsumer, MessageReceiver
{
    private $maxNumberOfMessagesToProcess = 200;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var DomainEventHandlerLocator
     */
    private $handlerLocator;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Queue $queue, DomainEventHandlerLocator $locator, Logger $logger)
    {
        $this->queue = $queue;
        $this->handlerLocator = $locator;
        $this->logger = $logger;
    }

    public function process()
    {
        try {
            $messageReceiver = $this;
            $this->queue->consume($messageReceiver, $this->maxNumberOfMessagesToProcess);
        } catch (\Exception $e) {
            $this->logger->log(new FailedToReadFromDomainEventQueueMessage($e));
        }
    }

    public function receive(Message $message)
    {
        try {
            $domainEventHandler = $this->handlerLocator->getHandlerFor($message);
            $domainEventHandler->process();
        } catch (\Exception $e) {
            $this->logger->log(new DomainEventHandlerFailedMessage($message, $e));
        }
    }
}
