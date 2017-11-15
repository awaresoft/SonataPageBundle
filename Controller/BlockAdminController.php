<?php

namespace Awaresoft\Sonata\PageBundle\Controller;

use Sonata\PageBundle\Controller\BlockAdminController as BaseBlockAdminController;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Extended BlockAdminController class
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class BlockAdminController extends BaseBlockAdminController
{
    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request = null)
    {
        $this->admin->checkAccess('create');

        $sharedBlockAdminClass = $this->container->getParameter('sonata.page.admin.shared_block.class');
        if (!$this->admin->getParent() && get_class($this->admin) !== $sharedBlockAdminClass) {
            throw new PageNotFoundException('You cannot create a block without a page');
        }

        $parameters = $this->admin->getPersistentParameters();
        $disabledBlocks = $this->getParameter('awaresoft.page.shared_block.disabled_block_list');

        if (!$parameters['type']) {
            return $this->render('SonataPageBundle:BlockAdmin:select_type.html.twig', [
                'services' => $this->get('sonata.block.manager')->getServicesByContext('sonata_page_bundle'),
                'disabledServices' => $disabledBlocks,
                'base_template' => $this->getBaseTemplate(),
                'admin' => $this->admin,
                'action' => 'create',
            ]);
        }

        return parent::createAction();
    }
}
