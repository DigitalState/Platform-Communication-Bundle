<?php

namespace Ds\Bundle\CommunicationBundle\Channel;

/**
 * Class SmsChannel
 */
class SmsChannel extends AbstractChannel
{


    /**
     * @param $recipient
     *
     * @return bool
     */
    public function canSendTo($recipient)
    {
        /// @todo
        return true;
    }

}
