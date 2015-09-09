<?php


namespace Brera\Queue;

use Brera\Log\Logger;

class LoggingQueueDecorator implements Queue
{
    /**
     * @var Queue
     */
    private $component;
    
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Queue $component, Logger $logger)
    {
        $this->component = $component;
        $this->logger = $logger;
    }
    /**
     * @return int
     */
    public function count()
    {
        return $this->component->count();
    }

    /**
     * @return bool
     */
    public function isReadyForNext()
    {
        return $this->component->isReadyForNext();
    }

    /**
     * @param mixed $data
     */
    public function add($data)
    {
        $this->logger->log(new QueueAddLogMessage($data, $this->component));
        $this->component->add($data);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return $this->component->next();
    }
}