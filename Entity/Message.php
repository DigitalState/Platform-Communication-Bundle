<?php

namespace Ds\Bundle\CommunicationBundle\Entity;

use Ds\Bundle\EntityBundle\Entity\Attribute;
use Ds\Bundle\TransportBundle\Entity\Attribute as TransportAttribute;
use Ds\Bundle\TransportBundle\Entity\Profile;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\LocaleBundle\Entity\FallbackTrait;
use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Bridge\Doctrine\Validator\Constraints as ORMAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Message
 *
 * @Config(
 *      routeName="ds_communication_message_index",
 *      routeView="ds_communication_message_view",
 *      routeCreate="ds_communication_message_create",
 *      routeEdit="ds_communication_message_edit",
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-list-alt",
 *              "type"="communication_message",
 *              "alias"=""
 *          },
 *          "ownership"={
 *              "owner_type"="BUSINESS_UNIT",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="business_unit_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "permissions"="All"
 *          },
 *          "manager"={
 *              "default"="ds.communication.manager.message"
 *          },
 *          "form"={
 *              "form_type"="ds_communication_message"
 *          },
 *          "grid"={
 *              "default"="ds-communication-message"
 *          }
 *      }
 * )
 * @ORM\Entity(repositoryClass="Ds\Bundle\CommunicationBundle\Repository\MessageRepository")
 * @ORM\Table(name="ds_comm_message", indexes={
 *     @ORM\Index(name="IDX_5F3E5701A745698", columns={"recipient_entity_name","recipient_entity_id"}),
 *     @ORM\Index(name="IDX_701A74ADD04A420", columns={"message_uid"})
 *      }
 * )
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string")
 * @ORM\HasLifecycleCallbacks()
 */
class Message
{
    use Attribute\Id;
    use Attribute\CreatedAt;
    use Attribute\UpdatedAt;
    use Attribute\Title;
    use Attribute\Presentation;
    use Attribute\SentAt;

    use Ownership\BusinessUnitAwareTrait;
    use TransportAttribute\DeliveryStatus;

    use FallbackTrait;

    /**
     * @var \Ds\Bundle\CommunicationBundle\Entity\Communication
     * @ORM\ManyToOne(targetEntity="Ds\Bundle\CommunicationBundle\Entity\Communication")
     * @ORM\JoinColumn(name="communication_id", referencedColumnName="id")
     */
    protected $communication; # region accessors

    /**
     * Set communication
     *
     * @param \Ds\Bundle\CommunicationBundle\Entity\Communication $communication
     * @return \Ds\Bundle\CommunicationBundle\Entity\Message
     */
    public function setCommunication(Communication $communication = null)
    {
        $this->communication = $communication;

        return $this;
    }

    /**
     * Get communication
     *
     * @return \Ds\Bundle\CommunicationBundle\Entity\Communication
     */
    public function getCommunication()
    {
        return $this->communication;
    }

    # endregion


    /**
     * @var string
     * @ORM\Column(name="message_uid", type="string", length=255)
     */
    protected $message_uid = ''; #region accessors

    /**
     * @return string
     */
    public function getMessageUID()
    {
        return $this->message_uid;
    }

    /**
     * @param string $message_uid
     *
     * @return Message
     */
    public function setMessageUID(string $message_uid)
    {
        $this->message_uid = $message_uid;

        return $this;
    }


    # endregion


    /**
     * @var Profile
     * @ORM\ManyToOne(targetEntity="Ds\Bundle\TransportBundle\Entity\Profile" )
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")

     */
    protected $profile; # region accessors

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }


    /**
     * @param Profile $profile
     *
     * @return Message
     */
    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;

        return $this;
    }
    # endregion

    /**
     * @var Content
     * @ORM\ManyToOne(targetEntity="Ds\Bundle\CommunicationBundle\Entity\Content")
     * @ORM\JoinColumn(name="content_id", referencedColumnName="id")

     */
    protected $content;  # region accessors

    /**
     * @return Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param Content $content
     *
     * @return Message
     */
    public function setContent(Content $content)
    {
        $this->content = $content;

        return $this;
    }



    # endregion

    /**
     * @var string
     * @ORM\Column(name="recipient_entity_name", type="string", length=255)
     */
    protected $recipientEntityName;

    /**
     * @var int
     * @ORM\Column(name="recipient_entity_id", type="integer")
     */
    protected $recipientEntityId;

    /**
     * Sets the entity class and id
     *
     * @param object $entity
     */
    public function setRecipient(EmailHolderInterface $entity)
    {
        $this->recipientEntityName = ClassUtils::getClass($entity);
        $this->recipientEntityId   = $entity->getId();

        return $this;
    }

    /**
     * Returns the tagged resource type
     *
     * @return string
     */
    public function getRecipientEntityName()
    {
        return $this->recipientEntityName;
    }

    /**
     * Returns the tagged resource id
     *
     * @return int
     */
    public function getRecipientEntityId()
    {
        return $this->recipientEntityId;
    }
    # endregion

    /**
     * @var \Ds\Bundle\CommunicationBundle\Entity\Channel
     * @ORM\ManyToOne(targetEntity="Ds\Bundle\CommunicationBundle\Entity\Channel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
     */
    protected $channel; # region accessors

    /**
     * Set channel
     *
     * @param \Ds\Bundle\CommunicationBundle\Entity\Channel $channel
     * @return \Ds\Bundle\CommunicationBundle\Entity\Message
     */
    public function setChannel(Channel $channel = null)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get channel
     *
     * @return \Ds\Bundle\CommunicationBundle\Entity\Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    # endregion



    protected $recipientFullName = '';

    /**
     * @return string
     */
    public function getRecipientFullName()
    {
        return $this->recipientFullName;
    }

    /**
     * @param string $recipientFullName
     *
     * @return Message
     */
    public function setRecipientFullName($recipientFullName)
    {
        $this->recipientFullName = $recipientFullName;

        return $this;
    }


}
