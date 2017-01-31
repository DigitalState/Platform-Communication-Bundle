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
     * Check if the transition to the new delivery status is possible.
     *
     * @param $oldStatus
     * @param $newStatus
     *
     * @return mixed
     */
    private static function isTransitionPossible($oldStatus, $newEventType)
    {

        $status = [
            Message::STATUS_UNKNOWN => 0,
            Message::STATUS_QUEUED  => 5,
            Message::STATUS_PROCESSING => 10,
            Message::STATUS_SENDING => 15,
            Message::STATUS_SENT    => 20,

            Message::STATUS_CANCELLED => 100,
            Message::STATUS_OPEN      => 100,
            Message::STATUS_FAILED    => 100,
        ];

        $newEventType = strtolower($newEventType);

        return $status[$oldStatus] < $status[$newEventType];
    }


    public function handle(AbstractMessageEvent $event)
    {
        $eventType = $event->getEventType();
        $message   = $event->getMessage();

        if(!$message)
            return;

        if (in_array($eventType, [
            Message::STATUS_UNKNOWN,
            Message::STATUS_QUEUED,
            Message::STATUS_PROCESSING,
            Message::STATUS_SENDING,
            Message::STATUS_OPEN,
            Message::STATUS_SENT,
            Message::STATUS_CANCELLED,
            Message::STATUS_FAILED,
        ]))
        {

            // We might receive events in a different order that they occured, avoid setting the status to a previous state
            if (self::isTransitionPossible($message->getDeliveryStatus(), $eventType))
            {
                $message->setDeliveryStatus($eventType);
            }

            $this->entityManager->persist($message);
        }

    }
}
