<?php

namespace Ds\Bundle\CommunicationBundle\Grid;

use Ds\Bundle\CommunicationBundle\Entity\Communication;
use Ds\Bundle\CommunicationBundle\Grid\CommunicationDatagridConfigurationBuilder;
use Ds\Bundle\CommunicationBundle\Model\DatagridCommunicationProxy;
use Symfony\Bridge\Doctrine\ManagerRegistry;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\QueryDesignerBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\QueryDesignerBundle\Grid\BuilderAwareInterface;
use Oro\Bundle\SegmentBundle\Entity\Segment;

class ConfigurationProvider implements ConfigurationProviderInterface, BuilderAwareInterface
{

    /** @var CommunicationDatagridConfigurationBuilder */
    protected $builder;

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var DatagridConfiguration[] */
    private $configuration = [];

    /**
     * Constructor
     *
     * @param CommunicationDatagridConfigurationBuilder $builder
     * @param ManagerRegistry                           $doctrine
     */
    public function __construct(
        CommunicationDatagridConfigurationBuilder $builder,
        ManagerRegistry $doctrine
    )
    {
        $this->builder  = $builder;
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($gridName)
    {
        return $this->builder->isApplicable($gridName);
    }

    /**
     *
     */
    private function getDefaultColumns(Communication $communication)
    {
        $definition = $communication->getCriteria();

        $entityFields = $this->doctrine->getManager()->getClassMetadata($communication->getEntityName())->getFieldNames();

        if (empty($definition['columns']))
        {

            // @todo filter based on Entity
            $definition['columns'] = [];
            $definition['columns'] =
                [
                    [
                        "name"    => "id",
                        "label"   => "ID",
                        "sorting" => "",
                        "func"    => null,
                    ],
                    [
                        "name"    => "firstName",
                        "label"   => "First name",
                        "sorting" => "",
                        "func"    => null,
                    ],
                    [
                        "name"    => "lastName",
                        "label"   => "First name",
                        "sorting" => "",
                        "func"    => null,
                    ],
                    [
                        "name"    => "primaryEmail",
                        "label"   => "Primary Email",
                        "sorting" => "",
                        "func"    => null,
                    ],
                ];
        }

        /*
        {
             "columns": [
                  {
                       "name": "firstName",
                       "label": "First name",
                       "sorting": "",
                       "func": null
                  },
                  {
                       "name": "primaryEmail",
                       "label": "Primary Email",
                       "sorting": "",
                       "func": null
                  }
             ],
             "filters": [
                  {
                       "columnName": "primaryEmail",
                       "criterion": {
                            "filter": "string",
                            "data": {
                                 "value": "",
                                 "type": "filter_not_empty_option"
                            }
                       }
                  }
             ]
        }
         */

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration($gridName)
    {
        if (empty($this->configuration[$gridName]))
        {
            $id                = intval(substr($gridName, strlen(Communication::GRID_PREFIX)));
            $segmentRepository = $this->doctrine->getRepository('DsCommunicationBundle:Communication');
            $segment           = $segmentRepository->find($id);

            $proxy = new DatagridCommunicationProxy($segment);

            $this->builder->setGridName($gridName);


            $proxy->setDefinition(json_encode($this->getDefaultColumns($segment)));

            $this->builder->setSource($proxy);

            $this->configuration[$gridName] = $this->builder->getConfiguration();
        }

        return $this->configuration[$gridName];
    }

    /**
     * Check whether a segment grid ready for displaying
     *
     * @param string $gridName
     *
     * @return bool
     */
    public function isConfigurationValid($gridName)
    {
        try
        {
            $this->getConfiguration($gridName);
        }
        catch (InvalidConfigurationException $invalidConfigEx)
        {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getBuilder()
    {
        return $this->builder;
    }
}
