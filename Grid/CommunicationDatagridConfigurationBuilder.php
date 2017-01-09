<?php

namespace Ds\Bundle\CommunicationBundle\Grid;

use Ds\Bundle\CommunicationBundle\Entity\Communication;
use Oro\Bundle\DataGridBundle\Extension\Export\ExportExtension;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Oro\Bundle\ReportBundle\Grid\BaseReportConfigurationBuilder;

class CommunicationDatagridConfigurationBuilder extends BaseReportConfigurationBuilder
{

    /**
     * @param AbstractQueryDesigner $source
     */
    public function setSource(AbstractQueryDesigner $source)
    {
        $em = $this->doctrine->getManagerForClass($source->getEntity());

        //$this->source = new DatagridSourceCommunicationProxy($source, $em);
        $this->source = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configuration = parent::getConfiguration();

        $className = $this->source->getEntity();

        $doctrineMetadata = $this->doctrine->getManagerForClass($className)
            ->getClassMetadata($className);
        $identifiers      = $doctrineMetadata->getIdentifier();
        $primaryKey       = array_shift($identifiers);

        $metadata = $this->configManager->getEntityMetadata($className);


        $configuration->offsetSetByPath('[source][acl_resource]', 'oro_communication_view');
        $configuration->offsetSetByPath(ExportExtension::EXPORT_OPTION_PATH, true);


        if ( !$metadata || empty($metadata->routeView))
        {
            return $configuration;
        }


        if(0) // @todo add preview link
        {
            $viewAction = [
                'view' => [
                    'type'         => 'dialog',
                    //'type'         => 'navigate',
                    'label'        => 'oro.report.datagrid.row.action.view',
                    'acl_resource' => 'VIEW;entity:' . $className,
                    'icon'         => 'eye-open',
                    'link'         => 'preview_link',
                    'rowAction'    => true,
                ],
            ];

            $properties = [
                $primaryKey    => null,
                'preview_link' => [
                    'type'   => 'url',
                    'route'  => 'ds_communication_widget_preview_content',
                    'params' => [
                        'communication' => $primaryKey,
                        'recipient'     => 123,
                    ],
                ],
            ];

            $configuration->offsetAddToArrayByPath('[properties]', $properties);
            $configuration->offsetAddToArrayByPath('[actions]', $viewAction);
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($gridName)
    {
        return ( strpos($gridName, Communication::GRID_PREFIX) === 0 );
    }
}
