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
     * @return array
     * @Route("/view/{id}", requirements={"id"="\d+"})
     * @Template()
     * @AclAncestor("ds.communication.communication.view")
     */
    public function viewAction(Communication $entity)
    {

        $query          = '[empty query]';

        if(!empty($entity->getCriteria()))
        {

            $entityFields = $this->getDoctrine()->getManager()->getClassMetadata($entity->getEntityName())->getFieldNames();

            $fields = array_intersect($entityFields  , [
                'email',
                'firstName',
                'lastName',
                'updatedAt'
            ]);

            $users = $this->get('ds.communication.manager.communication')->getUsers($entity , null , $fields);

            $entity->setUsers($users);
        }

        $config = $this->getConfig('entity', $entity);

        return [
            'entity' => $entity,
            'context' => $config->get('alias') ?: null
        ];
    }

    /**
     * Create action
     *
     * @param string $alias
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
}
