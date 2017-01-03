<?php

namespace Ds\Bundle\CommunicationBundle\Channel;
use Ds\Bundle\CommunicationBundle\Entity\Message;
use Ds\Bundle\CommunicationBundle\Model\EmailChannelRecipient;

/**
 * Class EmailChannel
 */
class EmailChannel extends AbstractChannel
{


    /**
     * @param $recipient
     *
     * @return bool
     */
    public function canSendTo($recipient)
    {
        /// @see \Ds\Bundle\CommunicationBundle\Provider\ContactInformationExclusionProvider
        return
            is_a($recipient, 'Oro\Bundle\LocaleBundle\Model\FirstNameInterface')
            && is_a($recipient, 'Oro\Bundle\EmailBundle\Model\EmailHolderInterface')
            && is_a($recipient, 'Oro\Bundle\LocaleBundle\Model\LastNameInterface');
    }


}
