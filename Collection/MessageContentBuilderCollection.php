<?php

namespace Ds\Bundle\CommunicationBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Ds\Bundle\CommunicationBundle\DependencyInjection\Compiler\MessageContentBuilderPass;
use Ds\Bundle\CommunicationBundle\Entity\Content;
use Ds\Bundle\CommunicationBundle\Entity\Message;
use Ds\Bundle\CommunicationBundle\Model\ContentTemplate;
use Ds\Bundle\CommunicationBundle\Model\MessageContentBuilderInterface;
use Ds\Bundle\CommunicationBundle\Model\MessageEventHandlerInterface;
use Ds\Bundle\TransportBundle\Model\AbstractMessageEvent;

/**
 * Class MessageEventHandlerCollection
 */
class MessageContentBuilderCollection extends ArrayCollection
{

    /**
     * @param Message         $message
     * @param                 $recipient
     * @param ContentTemplate $contentTemplate
     *
     * @return bool|ContentTemplate
     */
    public function processAll(Message $message ,$recipient,  ContentTemplate $contentTemplate )
    {
        foreach($this as $provider)
        {
            /** @var MessageContentBuilderInterface $provider */
            $contentTemplate  = $provider->compileMessage($message, $recipient, $contentTemplate );
        }

        return $contentTemplate ;
    }
}
