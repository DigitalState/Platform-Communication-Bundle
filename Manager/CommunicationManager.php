<?php

namespace Ds\Bundle\CommunicationBundle\Manager;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ds\Bundle\CommunicationBundle\Collection\MessageContentBuilderCollection;
use Ds\Bundle\CommunicationBundle\Entity\Content;
use Ds\Bundle\CommunicationBundle\Entity\Message;
use Ds\Bundle\CommunicationBundle\Model\ContentTemplate;
use Oro\Bundle\BatchBundle\Tests\Unit\Fixtures\Entity\Email;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\SegmentBundle\Query\DynamicSegmentQueryBuilder;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Ds\Bundle\CommunicationBundle\Entity\Communication;
use Ds\Bundle\CommunicationBundle\Entity\Channel;

/**
 * Class CommunicationManager
 */
class CommunicationManager extends ApiEntityManager
{

    /**
     * @var \Oro\Bundle\UserBundle\Entity\UserManager
     */
    protected $userManager;

    /**
     * @var \Ds\Bundle\CommunicationBundle\Manager\MessageManager
     */
    protected $messageManager;

    /**
     * @var DynamicSegmentQueryBuilder
     */
    protected $dynamicSegmentQueryBuilder;

    /**
     * @var OwnershipMetadataProvider
     */
    protected $ownershipMetadataProvider;

    /**
     * @var MessageContentBuilderCollection
     */
    protected $messageContentBuilderCollection;

    /**
     * Constructor
     *
     * @param string                                                $class
     * @param \Doctrine\Common\Persistence\ObjectManager            $om
     * @param \Oro\Bundle\UserBundle\Entity\UserManager             $userManager
     * @param \Ds\Bundle\CommunicationBundle\Manager\MessageManager $messageManager
     */
    public function __construct($class,
                                ObjectManager $om,
                                UserManager $userManager,
                                MessageManager $messageManager,
                                MessageContentBuilderCollection $messageContentBuilderCollection,
                                DynamicSegmentQueryBuilder $dynamicSegmentQueryBuilder,
                                OwnershipMetadataProvider $ownershipMetadataProvider)
    {
        parent::__construct($class, $om);

        $this->userManager                     = $userManager;
        $this->messageManager                  = $messageManager;
        $this->messageContentBuilderCollection = $messageContentBuilderCollection;
        $this->dynamicSegmentQueryBuilder      = $dynamicSegmentQueryBuilder;
        $this->ownershipMetadataProvider       = $ownershipMetadataProvider;
    }


    /**
     * @param Communication                                             $communication
     * @param Content                                                   $content
     * @param FirstNameInterface|LastNameInterface|EmailHolderInterface $recipient
     *
     * @return Message
     */
    public function compileMessage(Message $message)
    {
        $contentTemplate = new ContentTemplate($message->getContent()->getTitle(), $message->getContent()->getPresentation());

        $recipient = $this->om->getRepository($message->getRecipientEntityName())->find($message->getRecipientEntityId());

        if ( !$recipient)
            return null;

        $contentTemplate = $this->messageContentBuilderCollection->processAll($message, $recipient, $contentTemplate);

        $message
            ->setPresentation($contentTemplate->getContent())
            ->setTitle($contentTemplate->getSubject());

        return $message;
    }



    /**
     * @param Communication                              $communication
     * @param Content                                    $content
     * @param FirstNameInterface|LastNameInterface|Email $recipient
     *
     * @return Message
     */
    public function createMessage(Communication $communication, Content $content, $recipient)
    {
        /** @var Message $message */
        $message = $this->messageManager->createEntity();

        // @todo add a UNIQUE key on [communication, content, recipient]
        $message
            ->setCommunication($communication)
            ->setContent($content)
            ->setChannel($content->getChannel())
            ->setProfile($content->getProfile())
            ->setRecipientFullName(trim(sprintf("%s %s", $recipient->getFirstName(), $recipient->getLastName())))
            ->setRecipient($recipient);

        return $message;
    }

    /**
     * Send communication
     *
     * @param \Ds\Bundle\CommunicationBundle\Entity\Communication $communication
     *
     * @return \Ds\Bundle\CommunicationBundle\Manager\CommunicationManager
     */
    public function queueSend(Communication $communication)
    {
        $contents = $communication->getContents();

        /** @var Content $content */
        foreach ($contents as $content)
        {
            $recipients = $this->getUsers($communication, $content->getChannel());

            foreach ($recipients as $recipient)
            {
                $message = $this->createMessage($communication, $content, $recipient);

                $message->setDeliveryStatus(\Ds\Bundle\TransportBundle\Model\Message::STATUS_QUEUED);;

                $this->om->persist($message);
            }
        }
        $this->om->flush();

        return $this;
    }

    /**
     * Get users
     *
     * @param Communication $communication
     *
     * @return QueryBuilder
     * @throws \Exception
     * @internal param array $criteria
     * @internal param Channel $channel
     *
     */
    public function getCriteriaQueryBuilder(Communication $communication)
    {
        $segment = new Segment();
        $segment->setOrganization($communication->getOrganization());
        $segment->setDefinition(json_encode($communication->getCriteria()));
        $segment->setEntity($communication->getEntityName());

        try
        {
            $qb = $this->dynamicSegmentQueryBuilder
                ->getQueryBuilder($segment);

            $alias = current($qb->getDQLPart('from'))->getAlias();

            $qb->resetDQLPart('select');
            $qb->addSelect($alias);

            $organizationField = $this->ownershipMetadataProvider
                ->getMetadata($segment->getEntity())
                ->getGlobalOwnerFieldName();

            if ($organizationField)
            {
                $qb->andWhere(
                    sprintf(
                        '%s.%s = %s',
                        $qb->getRootAliases()[0],
                        $organizationField,
                        $segment->getOrganization()->getId()
                    )
                );
            }

            return $qb;
        }
        catch (\Exception $exception)
        {
            throw $exception;
        }
    }

    /**
     * Get users
     *
     * @param array                                         $criteria
     * @param \Ds\Bundle\CommunicationBundle\Entity\Channel $channel
     *
     * @return array
     */
    public function getUsers(Communication $communication, Channel $channel = null)
    {
        $qb = $this->getCriteriaQueryBuilder($communication);

        // @todo check for $channel

        return $qb->getQuery()->getResult();
    }
}
