<?php

namespace Ds\Bundle\CommunicationBundle\Model;
use Ds\Bundle\CommunicationBundle\Entity\Message;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;

/**
 * Class EmailChannel
 */
interface EmailChannelRecipient extends
    FirstNameInterface,
    EmailHolderInterface,
    LastNameInterface
{
}
