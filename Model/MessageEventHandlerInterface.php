<?php
namespace Ds\Bundle\CommunicationBundle\Model;

use Ds\Bundle\TransportBundle\Model\AbstractMessageEvent;

interface MessageEventHandlerInterface
{

    /**
     * @param AbstractMessageEvent $event
     *
     * @return void
     */
    public function handle(AbstractMessageEvent $event);

}