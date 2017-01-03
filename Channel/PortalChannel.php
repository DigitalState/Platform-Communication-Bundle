<?php

namespace Ds\Bundle\CommunicationBundle\Channel;

/**
 * Class PortalChannel
 */
class PortalChannel extends AbstractChannel
{


    /**
     * @param $recipient
     *
     * @return bool
     */
    public function canSendTo($recipient)
    {
        return true;
    }
}
