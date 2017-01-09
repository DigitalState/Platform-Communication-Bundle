<?php

namespace Ds\Bundle\CommunicationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Ds\Bundle\AdminBundle\Controller\BreadController;
use Ds\Bundle\CommunicationBundle\Entity\Communication;

use Oro\Bundle\SegmentBundle\Entity\Segment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CommunicationController
 *
 * @Route("/communication")
 */
class CommunicationController extends BreadController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('communication');
    }

    /**
     * Index action
     *
     * @Route("/")
     * @Template()
     * @AclAncestor("ds.communication.communication.view")
     */
    public function indexAction()
    {
        return $this->handleIndex();
    }


    /**
     * View action
     *
     * @param \Ds\Bundle\CommunicationBundle\Entity\Communication $entity
     *
     * @return array
     * @Route("/view/{id}", requirements={"id"="\d+"})
     * @Template()
     * @AclAncestor("ds.communication.communication.view")
     */
    public function viewAction(Communication $entity)
    {

        $query = '[empty query]';

        if ( !empty($entity->getCriteria()))
        {
            $users = $this->get('ds.communication.manager.communication')->getUsers($entity, null);

            $entity->setUsers($users);
        }

        $config = $this->getConfig('entity', $entity);

        return [
            'entity'  => $entity,
            'context' => [
                'alias'    => $config->get('alias') ? : null,
                'gridName' => Communication::GRID_PREFIX . $entity->getId(),
            ],
        ];
    }

    /**
     * Create action
     *
     * @param string $alias
     *
     * @return array
     * @Route("/create/{alias}", requirements={"alias":"[a-z]*"}, defaults={"alias":""})
     * @Template("DsCommunicationBundle:Communication:edit.html.twig")
     * @AclAncestor("ds.communication.communication.create")
     */
    public function createAction($alias)
    {
        return $this->handleCreate($alias);
    }

    /**
     * Edit action
     *
     * @param \Ds\Bundle\CommunicationBundle\Entity\Communication $entity
     *
     * @return array
     * @Route("/update/{id}", requirements={"id":"\d+"}, defaults={"id":0})
     * @Template()
     * @AclAncestor("ds.communication.communication.edit")
     */
    public function editAction(Communication $entity)
    {
        return $this->handleEdit($entity);
    }

    /**
     * Send action
     *
     * @param \Ds\Bundle\CommunicationBundle\Entity\Communication $entity
     *
     * @return Response|array
     * @Route("/send/{id}", requirements={"id":"\d+"}, defaults={"id":0})
     * @AclAncestor("ds.communication.communication.edit")
     */
    public function sendAction(Communication $entity)
    {
        $manager = $this->get('ds.communication.manager.communication');
        $manager->send($entity);

        $this->addFlash('success', 'ds.communication.action.sent', true);
        $meta = $this->getMetaByAlias('');

        return $this->redirectToRoute($meta->getRoute('view'), [ 'id' => $entity->getId() ]);
    }


    /**
     * @Route("/view/widget/{id}/recipient/{recipient}", name="ds_communication_widget_preview_content")
     * @Template()
     *
     * @param string  $entityClass The entity class which activities should be rendered
     * @param integer $entityId    The entity object id which activities should be rendered
     *
     * @return array
     */
    public function widgetAction(Communication $communication, $recipient)
    {
        $entity = $this->getEntityRoutingHelper()->getEntity($entityClass, $entityId);

        /** @var ActivityListChainProvider $activitiesProvider */
        $activitiesProvider = $this->get('oro_activity_list.provider.chain');

        /** @var DateTimeRangeFilter $dateRangeFilter */
        $dateRangeFilter = $this->get('oro_filter.datetime_range_filter');

        return [
            'entity'                  => $entity,
            'configuration'           => $activitiesProvider->getActivityListOption($this->get('oro_config.user')),
            'dateRangeFilterMetadata' => $dateRangeFilter->getMetadata(),
        ];
    }
}
