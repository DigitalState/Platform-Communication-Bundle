<?php

namespace Ds\Bundle\CommunicationBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

use Ds\Bundle\TransportBundle\Model\Message;
use Ds\Bundle\CommunicationBundle\Model\MessageEventHandlerInterface;
use Ds\Bundle\TransportBundle\Entity\WebHookData;
use Ds\Bundle\TransportBundle\Model\AbstractMessageEvent;
use Oro\Bundle\EntityBundle\Provider\AbstractExclusionProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

/**
 * Update
 */
class DeliveryStatusUpdateHandler implements MessageEventHandlerInterface
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $oldStatus
     * @param $newStatus
     *
     * @return mixed
     */
    private static function getStatus($oldStatus, $newEventType)
    {
        // @todo replace by a Oro Workflow
        $newEventType = strtolower($newEventType);
        // final message states
        if ($oldStatus === Message::STATUS_FAILED
            || $oldStatus === Message::STATUS_SENT
            || $oldStatus === Message::STATUS_CANCELLED
        )
        {
            return $oldStatus;
        }

        if ($newEventType !== Message::STATUS_UNKNOWN)
        {
            // Message:: STATUS_QUEUED
            // Message::STATUS_SENDING
            return $newEventType;
        }

        return $oldStatus;
    }


    public function handle(AbstractMessageEvent $event)
    {
        $eventType = $event->getEventType();
        $message   = $event->getMessage();
        if (in_array($eventType, [
            Message::STATUS_UNKNOWN,
            Message::STATUS_QUEUED,
            Message::STATUS_SENDING,
            Message::STATUS_OPEN,
            Message::STATUS_SENT,
            Message::STATUS_CANCELLED,
            Message::STATUS_FAILED,
        ]))
        {
            $message->setDeliveryStatus(self::getStatus($message->getDeliveryStatus(), $eventType));

            $this->entityManager->persist($message);
        }

        return true;
    }
}
