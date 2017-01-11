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
        $this->forAll(function ($i,$provider) use ($event)
        {
            /** @var MessageEventHandlerInterface $provider */
            $provider->handle($event);
        });
    }
}
