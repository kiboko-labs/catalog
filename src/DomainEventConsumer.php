<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Queue\Queue;

class DomainEventConsumer
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
        $numberOfMessagesBeforeReturn = $this->maxNumberOfMessagesToProcess;

        while ($this->queue->isReadyForNext() && $numberOfMessagesBeforeReturn-- > 0) {
            try {
                $domainEvent = $this->queue->next();
                $this->processDomainEvent($domainEvent);
            } catch (\Exception $e) {
                $this->logger->log(new FailedToReadFromDomainEventQueueMessage($e));
            }
        }
    }

    private function processDomainEvent(DomainEvent $domainEvent)
    {
        try {
            $domainEventHandler = $this->handlerLocator->getHandlerFor($domainEvent);
            $domainEventHandler->process();
        } catch (\Exception $e) {
            $this->logger->log(new DomainEventHandlerFailedMessage($domainEvent, $e));
        }
    }
}
