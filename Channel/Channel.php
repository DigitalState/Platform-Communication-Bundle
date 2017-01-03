<?php

namespace Ds\Bundle\CommunicationBundle\Channel;

use Ds\Bundle\TransportBundle\Transport\Transport;
use Ds\Bundle\CommunicationBundle\Entity\Message;

/**
 * Interface Channel
 */
interface Channel
{
    /**
     * Set transport
     *
     * @param \Ds\Bundle\TransportBundle\Transport\Transport $transport
     * @return \Ds\Bundle\CommunicationBundle\Channel\Channel
     */
    public function setTransport(Transport $transport);

    /**
     * Get transport
     *
     * @return \Ds\Bundle\TransportBundle\Transport\Transport
     */
    public function getTransport();


    /**
     * @param $recipient
     *
     * @return bool
     */
    public function canSendTo($recipient);

    /**
     * Send message
     *
     * @param \Ds\Bundle\CommunicationBundle\Entity\Message $message
     * @return \Ds\Bundle\CommunicationBundle\Entity\Message
     */
    public function send(Message $message , $recipient);
}
