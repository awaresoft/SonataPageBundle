<?php

namespace Awaresoft\Sonata\PageBundle\Controller;

use Sonata\PageBundle\Controller\PageAdminController as BasePageAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Extended PageAdminController class
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class PageAdminController extends BasePageAdminController
{
    /**
     * @param Request|null $request
     *
     * @return Response
     */
    public function treeAction(Request $request = null)
    {
        if ($this->getRequest()->attributes->get('_sonata_name') != 'admin_awaresoft_cms_tree') {
            return parent::treeAction($request);
        }

        $this->admin->checkAccess('tree');

        $sites = $this->get('sonata.page.manager.site')->findBy([]);
        $pageManager = $this->get('sonata.page.manager.page');

        $currentSite = null;
        $siteId = $request->get('site');
        foreach ($sites as $site) {
            if ($siteId && $site->getId() == $siteId) {
                $currentSite = $site;
            } elseif (!$siteId && $site->getIsDefault()) {
                $currentSite = $site;
            }
        }
        if (!$currentSite && count($sites) == 1) {
            $currentSite = $sites[0];
        }

        if ($currentSite) {
            $pages = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AwaresoftSonataPageBundle:Page')
                ->findBy(['inCms' => true, 'site' => $currentSite], ['position' => 'ASC']);
        } else {
            $pages = [];
        }

        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();

        $twig = $this->get('twig');
        $theme = $this->admin->getFilterTheme();
        $twig->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->setTheme($formView, $theme);

        return $this->render($this->admin->getTemplate('tree'), [
            'action' => 'tree',
            'sites' => $sites,
            'currentSite' => $currentSite,
            'pages' => $pages,
            'form' => $formView,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function editAction($id = null)
    {
        $request = $this->getRequest();
        if ($request->attributes->get('_sonata_name') == 'admin_awaresoft_cms_edit') {
            $id = $request->get($this->admin->getIdParameter());
            $object = $this->admin->getObject($id);

            if (!$object) {
                throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
            }

            if (!$object->isInCms()) {
                throw $this->createAccessDeniedException(sprintf('this object can not be edited: %s', $id));
            }
        }

        return parent::editAction($id);
    }
}
