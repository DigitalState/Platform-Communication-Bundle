<?php

namespace Ds\Bundle\CommunicationBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\EntityBundle\Provider\AbstractExclusionProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

/**
 * Provide exclude logic to filter entities with "contact_information" data
 */
class ContactInformationExclusionProvider extends AbstractExclusionProvider
{
    /**
     * @var ConfigProvider
     */
    protected $entityConfigProvider;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @param ConfigProvider  $entityConfigProvider
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ConfigProvider $entityConfigProvider, ManagerRegistry $managerRegistry)
    {
        $this->entityConfigProvider = $entityConfigProvider;
        $this->managerRegistry      = $managerRegistry;
    }


    /**
     * @param string          $className
     * @param ConfigInterface $config
     *
     * @return bool
     */
    private function hasRequirements($className,ConfigInterface $config)
    {
        /// @see Ds\Bundle\CommunicationBundle\Channel\Channel::canSendTo()
        if (! $config->has('contact_information')) {
            //return false;
        }

        return
            is_a($className , 'Oro\Bundle\EmailBundle\Model\EmailHolderInterface', true)
            && is_a($className , 'Oro\Bundle\LocaleBundle\Model\FirstNameInterface', true)
            && is_a($className , 'Oro\Bundle\LocaleBundle\Model\LastNameInterface', true)
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function isIgnoredEntity($className)
    {
        if (!$this->entityConfigProvider->hasConfig($className)) {
            return true;
        }

        $entityConfig = $this->entityConfigProvider->getConfig($className);
        if($this->hasRequirements($className, $entityConfig ))
        {
            return false;
        }


        /** @var ClassMetadata $metadata */
        $metadata = $this->managerRegistry->getManagerForClass($className)->getClassMetadata($className);

        foreach ($metadata->getFieldNames() as $fieldName) {
            if ($this->entityConfigProvider->hasConfig($className, $fieldName)) {
                $fieldConfig = $this->entityConfigProvider->getConfig($className, $fieldName);
                if($this->hasRequirements($className, $fieldConfig ))
                {
                    return false;
                }
            }
        }

        return true;
    }
}
