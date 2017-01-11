<?php

namespace Ds\Bundle\CommunicationBundle\Provider;


use Doctrine\ORM\EntityManager;

use Ds\Bundle\CommunicationBundle\Entity\Content;
use Ds\Bundle\CommunicationBundle\Entity\Message;
use Ds\Bundle\CommunicationBundle\Model\ContentTemplate;
use Ds\Bundle\CommunicationBundle\Model\MessageContentBuilderInterface;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;


/**
 * Replace all {{ variables }} with the appropriate contents
 */
class MessageContentEntityVariablesProvider implements MessageContentBuilderInterface
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EmailRenderer
     */
    protected $emailRenderer;

    /**
     * @param $entityManager
     */
    public function __construct(EntityManager $entityManager, EmailRenderer $emailRenderer)
    {
        $this->entityManager = $entityManager;
        $this->emailRenderer = $emailRenderer;
    }

    public function compileMessage(Message $message, $recipient, ContentTemplate $contentTemplate)
    {
        list($subject, $content) = $this->emailRenderer->compileMessage(
            $contentTemplate,
            [ 'entity' => $recipient ]
        );

        $contentTemplate->setSubject($subject);
        $contentTemplate->setContent($content);

        return $contentTemplate ;
    }

}
