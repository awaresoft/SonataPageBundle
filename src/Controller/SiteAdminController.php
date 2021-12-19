<?php

namespace Awaresoft\Sonata\PageBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sonata\PageBundle\Controller\SiteAdminController as BaseSiteAdminController;

/**
 * Site Admin controller.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class SiteAdminController extends BaseSiteAdminController
{
    /**
     * {@inheritdoc}
     */
    public function snapshotsAction()
    {
        if (false === $this->get('sonata.page.admin.snapshot')->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        /**
         * @var $request Request
         */
        $request = $this->get('request_stack')->getCurrentRequest();
        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->setSubject($object);

        if ($request->getMethod() == 'POST') {
            $this->get('sonata.notification.backend')
                ->createAndPublish('sonata.page.create_snapshots', array(
                    'siteId' => $object->getId(),
                    'mode' => 'async',
                ));

            $this->addFlash('sonata_flash_success', $this->admin->trans('flash_snapshots_created_success'));

            return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $object->getId())));
        }

        return $this->render('SonataPageBundle:SiteAdmin:create_snapshots.html.twig', array(
            'action' => 'snapshots',
            'object' => $object,
        ));
    }
}
