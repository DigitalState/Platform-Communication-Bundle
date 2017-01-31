<?php

namespace Ds\Bundle\CommunicationBundle\Channel;

use Ds\Bundle\TransportBundle\Transport\Transport;
use Ds\Bundle\UserPersonaBundle\Entity\Persona;
use Ds\Bundle\UserPersonaBundle\Manager\PersonaManager;
use Ds\Bundle\DataBundle\Data\Data;
use Ds\Bundle\CommunicationBundle\Entity\Message;
use Ds\Bundle\TransportBundle\Model\Message as MessageModel;

/**
 * Class AbstractChannel
 */
abstract class AbstractChannel implements Channel
{
    /**
     * @var \Ds\Bundle\UserPersonaBundle\Manager\PersonaManager
     */
    protected $personaManager;

    /**
     * @var \Ds\Bundle\DataBundle\Data\Data
     */
    protected $data;

    /**
     * @var \Ds\Bundle\TransportBundle\Transport\Transport
     */
    protected $transport; # region accessors

    /**
     * {@inheritdoc}
     */
    public function setTransport(Transport $transport)
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransport()
    {
        return $this->transport;
    }

    # endregion

    /**
     * Constructor
     *
     * @param \Ds\Bundle\UserPersonaBundle\Manager\PersonaManager $personaManager
     * @param \Ds\Bundle\DataBundle\Data\Data $data
     */
    public function __construct(PersonaManager $personaManager, Data $data)
    {
        $this->personaManager = $personaManager;
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Message $message , $recipient)
    {
        $messageModel = new MessageModel;
        $messageModel
            ->setRecipient($recipient)
            ->setTitle($message->getTitle())
            ->setContent($message->getPresentation());

        $messageModel = $this->getTransport()->send($messageModel );


        $message
            ->setDeliveryStatus($messageModel->getDeliveryStatus())
            ->setMessageUID($messageModel->getMessageUID())
        ;

        return $message;
    }
}
