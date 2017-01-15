<?php

namespace Ds\Bundle\CommunicationBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Ds\Bundle\CommunicationBundle\Model\MessageEventHandlerInterface;
use Ds\Bundle\TransportBundle\Model\AbstractMessageEvent;

/**
 * Class MessageEventHandlerCollection
 */
class MessageEventHandlerCollection extends ArrayCollection
{


    public function dispatch(AbstractMessageEvent $event)
    {
        /** @var MessageEventHandlerInterface $provider */
        foreach ($this as $provider)
        {
            $provider->handle($event);
        };
    }
}
