<?php

namespace Awaresoft\Sonata\PageBundle\Controller;

use Sonata\PageBundle\Controller\BlockAdminController as BaseBlockAdminController;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    /**
     * {@inheritdoc}
     */
    public function savePositionAction(Request $request = null)
    {
        $this->admin->checkAccess('savePosition');

        try {
            $params = $request->get('disposition');

            $request->attributes->set('id', $params[0]['page_id']);

            if (!is_array($params)) {
                throw new HttpException(400, 'wrong parameters');
            }

            $result = $this->get('sonata.page.block_interactor')->saveBlocksPosition($params, true);

            $status = 200;

            $pageAdmin = $this->get('sonata.page.admin.page');
            $pageAdmin->setRequest($request);
            $pageAdmin->update($pageAdmin->getSubject());
        } catch (HttpException $e) {
            $status = $e->getStatusCode();
            $result = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            $status = 500;
            $result = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }

        $result = (true === $result) ? 'ok' : $result;

        return $this->renderJson(['result' => $result], $status);
    }
}
