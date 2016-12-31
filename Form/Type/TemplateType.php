<?php

namespace Ds\Bundle\CommunicationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\QueryDesignerBundle\Form\Type\AbstractQueryDesignerType;

/**
 * Class TemplateType
 */
class TemplateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('entityName', 'ds_communication_contact_information_entity_choice', [
            'required' => true
        ]);

        $builder->add('title', 'text', [
            'label' => 'ds.communication.template.title.label'
        ]);

        $builder->add('presentation', 'oro_rich_text', [
            'label' => 'ds.communication.template.presentation.label'
        ]);

        $builder->add('owner', 'oro_business_unit_select', [
            'label' => 'ds.communication.template.owner.label'
        ]);

        // disable some fields for non editable email template
        $setDisabled = function (&$options)
        {
            if (isset( $options['auto_initialize'] ))
            {
                $options['auto_initialize'] = false;
            }
            $options['disabled'] = true;
        };

        $factory     = $builder->getFormFactory();
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory, $setDisabled)
            {
                $data = $event->getData();
                if ($data && $data->getId())
                {
                    $form = $event->getForm();
                    // entityName field
                    $options = $form->get('entityName')->getConfig()->getOptions();
                    $setDisabled($options);
                    $form->add($factory->createNamed('entityName', 'oro_entity_choice', null, $options));
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Ds\Bundle\CommunicationBundle\Entity\Template',
            'intention' => 'ds_communication_template'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ds_communication_template';
    }
}
