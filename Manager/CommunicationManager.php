<?php

namespace Ds\Bundle\CommunicationBundle\Manager;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ds\Bundle\CommunicationBundle\Entity\Content;
use Ds\Bundle\CommunicationBundle\Entity\Message;
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
     * Constructor
     *
     * @param string $class
     * @param \Doctrine\Common\Persistence\ObjectManager $om
     * @param \Oro\Bundle\UserBundle\Entity\UserManager $userManager
     * @param \Ds\Bundle\CommunicationBundle\Manager\MessageManager $messageManager
     */
    public function __construct($class,
                                ObjectManager $om,
                                UserManager $userManager,
                                MessageManager $messageManager,
                                DynamicSegmentQueryBuilder $dynamicSegmentQueryBuilder,
                                OwnershipMetadataProvider $ownershipMetadataProvider    )
    {
        parent::__construct($class, $om);

        $this->userManager = $userManager;
        $this->messageManager = $messageManager;
        $this->dynamicSegmentQueryBuilder = $dynamicSegmentQueryBuilder;
        $this->ownershipMetadataProvider = $ownershipMetadataProvider;
    }

    /**
     * Send communication
     *
     * @param \Ds\Bundle\CommunicationBundle\Entity\Communication $communication
     * @return \Ds\Bundle\CommunicationBundle\Manager\CommunicationManager
     */
    public function send(Communication $communication)
    {
        $contents = $communication->getContents();

        /** @var Content $content */
        foreach ($contents as $content) {

            $recipients = $this->getUsers($communication, $content->getChannel());

            foreach ($recipients as $recipient) {

                /** @var Message $message */
                $message = $this->messageManager->createEntity();

                $message
                    ->setCommunication($communication)
                    ->setRecipient($recipient)
                    ->setChannel($content->getChannel())
                    ->setTitle($content->getTitle())
                    ->setPresentation($content->getPresentation());

                $this->messageManager->send($message, $recipient, $content->getProfile());
            }
        }

        return $this;
    }

    /**
     * Get users
     *
     * @param array $criteria
     * @param \Ds\Bundle\CommunicationBundle\Entity\Channel $channel
     * @return QueryBuilder
     */
    public function getCriteriaQueryBuilder(Communication $communication, $extraFields = [])
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
            $qb->addSelect($alias );

            foreach($extraFields as $field)
            {
                $qb->addSelect($alias  . '.' . $field);
            }

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
     * @param array $criteria
     * @param \Ds\Bundle\CommunicationBundle\Entity\Channel $channel
     * @return array
     */
    public function getUsers(Communication $communication, Channel $channel = null, $extraFields = [])
    {
        $qb= $this->getCriteriaQueryBuilder($communication , $extraFields);

        // @todo check for $channel

        return $qb->getQuery()->getResult();
    }
}
