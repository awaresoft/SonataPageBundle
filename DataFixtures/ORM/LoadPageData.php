<?php

namespace Awaresoft\Sonata\PageBundle\DataFixtures\ORM;

use Awaresoft\Doctrine\Common\DataFixtures\AbstractFixture as AwaresoftAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\PageInterface;

/**
 * Class LoadPageData
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 *
 */
class LoadPageData extends AwaresoftAbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * {@inheritDoc}
     */
    public function getEnvironments()
    {
        return array('dev', 'prod');
    }

    public function doLoad(ObjectManager $manager)
    {
        $site = $this->createSite();
        $this->createGlobalPage($site);
        $this->createHomePage($site);
        $this->create404ErrorPage($site);
        $this->create500ErrorPage($site);

        $this->addReference('page-site', $site);
    }

    /**
     * @return SiteInterface $site
     */
    public function createSite()
    {
        $site = $this->getSiteManager()->create();

        $site->setHost('localhost');
        $site->setEnabled(true);
        $site->setName('localhost');
        $site->setEnabledFrom(new \DateTime('now'));
        $site->setEnabledTo(new \DateTime('+10 years'));
        $site->setRelativePath("");
        $site->setIsDefault(true);

        $this->getSiteManager()->save($site);

        return $site;
    }

    /**
     * @param SiteInterface $site
     */
    public function createHomePage(SiteInterface $site)
    {
        $pageManager = $this->getPageManager();
        $blockManager = $this->getBlockManager();
        $blockInteractor = $this->getBlockInteractor();

        $this->addReference('page-homepage', $homepage = $pageManager->create());
        $homepage->setSlug('/');
        $homepage->setUrl('/');
        $homepage->setName('Home');
        $homepage->setTitle('Homepage');
        $homepage->setEnabled(true);
        $homepage->setDecorate(false);
        $homepage->setRequestMethod('GET|POST|HEAD|DELETE|PUT');
        $homepage->setTemplateCode('default');
        $homepage->setRouteName('homepage');
        $homepage->setSite($site);
        $homepage->setInCms(true);

        $pageManager->save($homepage);


        // Add homepage header container
        $homepage->addBlocks($header = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $homepage,
            'code' => 'header',
        )));

        $header->setName('Header');

        $blockManager->save($header);

        // Add homepage content top container
        $homepage->addBlocks($contentTop = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $homepage,
            'code' => 'content_top',
        )));

        $contentTop->setName('Content Top');

        $blockManager->save($contentTop);

        // add a block text
        $contentTop->addChildren($text = $blockManager->create());
        $text->setType('sonata.block.service.text');
        $text->setSetting('content', <<<CONTENT
<div class="col-md-3 welcome"><h2>Welcome</h2></div>
<div class="col-md-9">
    <p>
        Awaresoft CMS test page
    </p>
</div>
CONTENT
        );
        $text->setPosition(1);
        $text->setEnabled(true);
        $text->setPage($homepage);

        // Add homepage content container
        $homepage->addBlocks($content = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $homepage,
            'code' => 'content',
        )));

        $content->setName('Content');

        $blockManager->save($content);

        // Add homepage content bottom container
        $homepage->addBlocks($bottom = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page'    => $homepage,
            'code'    => 'content_bottom',
        )));

        $bottom->setName('Content Bottom');

        $blockManager->save($bottom);

        $pageManager->save($homepage);

        // Add homepage extra container
        $homepage->addBlocks($extra = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page'    => $homepage,
            'code'    => 'extra',
        )));

        $extra->setName('Extra');

        $blockManager->save($extra);

        $pageManager->save($homepage);
    }


    public function create404ErrorPage(SiteInterface $site)
    {
        $pageManager = $this->getPageManager();
        $blockManager = $this->getBlockManager();
        $blockInteractor = $this->getBlockInteractor();

        $page = $pageManager->create();
        $page->setName('_page_internal_error_not_found');
        $page->setTitle('Error 404');
        $page->setEnabled(true);
        $page->setDecorate(1);
        $page->setRequestMethod('GET|POST|HEAD|DELETE|PUT');
        $page->setTemplateCode('default');
        $page->setRouteName('_page_internal_error_not_found');
        $page->setSite($site);

        $page->addBlocks($block = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $page,
            'code' => 'content_top',
        )));

        // Add text content block
        $block->addChildren($text = $blockManager->create());
        $text->setType('sonata.block.service.text');
        $text->setSetting('content', '<h2>Error 404</h2><div>Page not found.</div>');
        $text->setPosition(1);
        $text->setEnabled(true);
        $text->setPage($page);

        $pageManager->save($page);
    }

    public function create500ErrorPage(SiteInterface $site)
    {
        $pageManager = $this->getPageManager();
        $blockManager = $this->getBlockManager();
        $blockInteractor = $this->getBlockInteractor();

        $page = $pageManager->create();
        $page->setName('_page_internal_error_fatal');
        $page->setTitle('Error 500');
        $page->setEnabled(true);
        $page->setDecorate(1);
        $page->setRequestMethod('GET|POST|HEAD|DELETE|PUT');
        $page->setTemplateCode('default');
        $page->setRouteName('_page_internal_error_fatal');
        $page->setSite($site);

        $page->addBlocks($block = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $page,
            'code' => 'content_top',
        )));

        // Add text content block
        $block->addChildren($text = $blockManager->create());
        $text->setType('sonata.block.service.text');
        $text->setSetting('content', '<h2>Error 500</h2><div>Internal error.</div>');
        $text->setPosition(1);
        $text->setEnabled(true);
        $text->setPage($page);

        $pageManager->save($page);
    }

    /**
     * @param SiteInterface $site
     */
    public function createGlobalPage(SiteInterface $site)
    {
        $pageManager = $this->getPageManager();
        $blockManager = $this->getBlockManager();
        $blockInteractor = $this->getBlockInteractor();

        $global = $pageManager->create();
        $global->setName('global');
        $global->setRouteName('_page_internal_global');
        $global->setTemplateCode('global');
        $global->setSite($site);

        $pageManager->save($global);


        $global->addBlocks($header = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $global,
            'code' => 'header',
        )));

        $header->setName('Header (global)');

        $blockManager->save($header);

        $global->addBlocks($breadcrumb = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $global,
            'code' => 'breadcrumb',
        )));

        $breadcrumb->setName('Breadcrumb');

        $blockManager->save($breadcrumb);

        $global->addBlocks($content = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $global,
            'code' => 'content',
        )));

        $content->setName('Content (global)');

        $blockManager->save($content);

        $global->addBlocks($footer = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page'    => $global,
            'code'    => 'footer'
        )));

        $footer->setName('Footer global');

        $blockManager->save($footer);

        $global->addBlocks($bottomFooter = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page'    => $global,
            'code'    => 'footer_bottom'
        )));

        $bottomFooter->setName('Footer Bottom (global)');

        $blockManager->save($bottomFooter);

        $global->addBlocks($extra = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page'    => $global,
            'code'    => 'extra'
        )));

        $extra->setName('Extra (global)');

        $blockManager->save($extra);

        $pageManager->save($global);
    }

    /**
     * @return \Sonata\PageBundle\Model\SiteManagerInterface
     */
    public function getSiteManager()
    {
        return $this->container->get('sonata.page.manager.site');
    }

    /**
     * @return \Sonata\PageBundle\Model\PageManagerInterface
     */
    public function getPageManager()
    {
        return $this->container->get('sonata.page.manager.page');
    }

    /**
     * @return \Sonata\BlockBundle\Model\BlockManagerInterface
     */
    public function getBlockManager()
    {
        return $this->container->get('sonata.page.manager.block');
    }

    /**
     * @return \Sonata\PageBundle\Entity\BlockInteractor
     */
    public function getBlockInteractor()
    {
        return $this->container->get('sonata.page.block_interactor');
    }
}
