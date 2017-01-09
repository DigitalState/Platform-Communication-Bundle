<?php

namespace Ds\Bundle\CommunicationBundle\Model;

use Ds\Bundle\CommunicationBundle\Entity\Communication;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;

class DatagridCommunicationProxy extends AbstractQueryDesigner
{

    private $communication = null;

    /**
     * Constructor
     *
     * @param Communication $communication
     */
    public function __construct(Communication $communication)
    {
        $this->communication = $communication;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return $this->communication->getEntityName();
    }

    /**
     * {@inheritdoc}
     */
    public function setEntity($entity)
    {
        $this->communication->setEntityName($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinition($definition)
    {
        $this->communication->setCriteria($definition);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return $this->communication->getCriteria();
    }
}
