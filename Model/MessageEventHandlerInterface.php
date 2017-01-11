<?php
namespace Ds\Bundle\CommunicationBundle\Model;

use Ds\Bundle\TransportBundle\Model\AbstractMessageEvent;

interface MessageEventHandlerInterface
{

    public function handle(AbstractMessageEvent $event);

}