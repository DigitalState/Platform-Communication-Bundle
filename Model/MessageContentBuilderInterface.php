<?php
namespace Ds\Bundle\CommunicationBundle\Model;

use Ds\Bundle\CommunicationBundle\Entity\Content;
use Ds\Bundle\CommunicationBundle\Entity\Message;
use Ds\Bundle\TransportBundle\Model\AbstractMessageEvent;

interface MessageContentBuilderInterface
{


    /**
     * @param Message $message
     * @param Content $content
     *
     * @return boolean
     */
    public function compileMessage(Message $message , $recipient, ContentTemplate $contentTemplate );

}