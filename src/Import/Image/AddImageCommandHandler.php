<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Image\Exception\NoAddImageCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class AddImageCommandHandler implements CommandHandler
{
    /**
     * @var AddImageCommand
     */
    private $command;

    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    public function __construct(Message $message, DomainEventQueue $domainEventQueue)
    {
        $this->command = AddImageCommand::fromMessage($message);
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $eventPayload = json_encode(['file_path' => $this->command->getImageFilePath()]);
        $this->domainEventQueue->addVersioned('image_was_added', $eventPayload, $this->command->getDataVersion());
    }
}
