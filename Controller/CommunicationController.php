<?php

namespace Ds\Bundle\CommunicationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Ds\Bundle\AdminBundle\Controller\BreadController;
use Ds\Bundle\CommunicationBundle\Entity\Communication;

use Ds\Bundle\TransportBundle\Entity\Profile;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Router;

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

        $statuses_cnt = $this->getDoctrine()->getRepository('DsCommunicationBundle:Message')->getCommunicationStatus($entity);

        $config = $this->getConfig('entity', $entity);

        return [
            'entity'  => $entity,
            'context' => [
                'alias'                => $config->get('alias') ? : null,
                'gridName'             => Communication::GRID_PREFIX . $entity->getId(),
                'sending_status_stats' => $statuses_cnt,
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
        $manager->queueSend($entity);

        $this->addFlash('success', 'ds.communication.action.sent', true);
        $meta = $this->getMetaByAlias('');

        return $this->redirectToRoute($meta->getRoute('view'), [ 'id' => $entity->getId() ]);
    }

    /**
     * Get target entity
     *
     * @return object|null
     */
    protected function getTargetEntity()
    {
        $entityRoutingHelper = $this->get('oro_entity.routing_helper');
        $targetEntityClass   = $entityRoutingHelper->getEntityClassName($this->getRequest(), 'targetActivityClass');
        $targetEntityId      = $entityRoutingHelper->getEntityId($this->getRequest(), 'targetActivityId');
        if ( !$targetEntityClass || !$targetEntityId)
        {
            return null;
        }

        return $entityRoutingHelper->getEntity($targetEntityClass, $targetEntityId);
    }

    /**
     * @todo we should receive the Communication from the Datagrid route parameter
     * @deprecated
     *
     * @param Request $request
     *
     * @return Communication|null|object
     */
    private function todoGetCommunicationFromDatagridInstead(Request $request)
    {
        $refererPath = parse_url($request->headers->get('referer'));

        $matches = [];
        if ( !isset($refererPath['path']) || !preg_match('/\/communication\/view\/(\d+)/', $refererPath['path'], $matches))
            throw new \InvalidArgumentException("Referer is not set");

        return $this->getDoctrine()->getRepository('DsCommunicationBundle:Communication')->find($matches[1]);
    }

    /**
     * @Route(
     *      "/widget/info/recipients/{id}",
     *      name="ds_communication_widget_preview_content",
     *      requirements={ "recipient"="\d+"},
     * )
     * @Template("DsCommunicationBundle:Communication/widget:preview.html.twig")
     * @AclAncestor("oro_communication_view")
     */
    public function previewAction(Request $request, $id)
    {
        // @todo we should receive the Communication from the Datagrid route parameter
        $communication = $this->todoGetCommunicationFromDatagridInstead($request);

        if ( !$communication)
            throw new NotFoundHttpException();

        $manager = $this->get('ds.communication.manager.communication');

        /** @var FirstNameInterface|LastNameInterface|EmailHolderInterface $recipient */
        $recipient = $this->getDoctrine()->getRepository($communication->getEntityName())->find($id);

        $mesasges = [];
        foreach ($communication->getContents() as $content)
        {
            $message = $manager->createMessage($communication, $content, $recipient);

            $message = $manager->compileMessage($message);

            $mesasges[] = $message;
        }

        return [
            'communication' => $communication,
            'recipient'     => $recipient,
            'messages'      => $mesasges,
            'target'        => $this->getTargetEntity(),
        ];
    }

}
