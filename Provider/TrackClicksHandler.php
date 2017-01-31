<?php

namespace Ds\Bundle\CommunicationBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

use Ds\Bundle\TransportBundle\Model\Message;
use Ds\Bundle\CommunicationBundle\Model\MessageEventHandlerInterface;
use Ds\Bundle\TransportBundle\Entity\WebHookData;
use Ds\Bundle\TransportBundle\Model\AbstractMessageEvent;
use Ds\Bundle\TransportBundle\Model\UrlTrackingMessageEvent;
use Oro\Bundle\EntityBundle\Provider\AbstractExclusionProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

/**
 * Update
 */
class TrackClicksHandler implements MessageEventHandlerInterface
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


    public function handle(AbstractMessageEvent $event)
    {
        if ( !( $event instanceof UrlTrackingMessageEvent ))
            return;

        if(!$event->getMessage())
            return;
        
        if ($event->getEventType() !== 'click')
            return;

        /** @var UrlTrackingMessageEvent $urlEvent */
        $urlEvent = $event;

        // @todo record event !!

        return true;
    }
}
