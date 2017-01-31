<?php

namespace Ds\Bundle\CommunicationBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommunicationType
 */
class CommunicationType extends AbstractType
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
            'label' => 'ds.communication.title.label'
        ]);

        $builder->add('description', 'textarea', [
            'label' => 'ds.communication.description.label',
            'required' => false
        ]);

        $builder->add('contents', 'oro_collection', [
            'label' => 'ds.communication.contents.label',
            'entry_type' => 'ds_communication_content',
            'allow_add' => true,
            'options' => [
                'communication' => false
            ]
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

        $builder->add('criteria', 'hidden', ['required' => false]);

        $factory = $builder->getFormFactory();
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($factory) {
                $form = $event->getForm();
                $data = $event->getData();
                $entity = $data ? $data['entity'] : null;
                $filterOptions = [
                    'mapped'             => false,
                    'column_choice_type' => null,
                    'entity'             => $entity,
                    'auto_initialize'    => false
                ];
                $form->add(
                    $factory->createNamed('filter', 'oro_query_designer_filter', null, $filterOptions)
                );
            }
        );
        $builder->get('criteria')
                ->addViewTransformer(new ArrayToJsonTransformer());


        $builder->add('owner', 'oro_business_unit_select', [
            'label' => 'ds.communication.owner.label'
        ]);


    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Ds\Bundle\CommunicationBundle\Entity\Communication',
            'intention' => 'ds_communication_communication'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ds_communication_communication';
    }
}
