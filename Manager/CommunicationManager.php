<?php

namespace Ds\Bundle\CommunicationBundle\Manager;

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
     * Constructor
     *
     * @param string $class
     * @param \Doctrine\Common\Persistence\ObjectManager $om
     * @param \Oro\Bundle\UserBundle\Entity\UserManager $userManager
     * @param \Ds\Bundle\CommunicationBundle\Manager\MessageManager $messageManager
     */
    public function __construct($class, ObjectManager $om, UserManager $userManager, MessageManager $messageManager)
    {
        parent::__construct($class, $om);

        $this->userManager = $userManager;
        $this->messageManager = $messageManager;
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

        foreach ($contents as $content) {

            $users = $this->getUsers($content->getChannel());

            foreach ($users as $user) {
                $message = $this->messageManager->createEntity();
                $message
                    ->setCommunication($communication)
                    ->setUser($user)
                    ->setChannel($content->getChannel())
                    ->setTitle($content->getTitle())
                    ->setPresentation($content->getPresentation());
                $this->messageManager->send($message, $content->getProfile());
            }
        }

        return $this;
    }

    /**
     * Get users
     *
     * @param array $criteria
     * @param \Ds\Bundle\CommunicationBundle\Entity\Channel $channel
     * @return array
     */
    public function getUsers(Channel $channel = null)
    {
        // @todo

        $query = $this->om->createQueryBuilder();

        return $query->getQuery()->getResult();
    }
}
