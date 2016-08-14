<?php

namespace Awaresoft\Sonata\PageBundle\Listener;

use Awaresoft\SettingBundle\Service\SettingService;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;

/**
 * ControllerListenerClass
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ControllerListener
{
    /**
     * @var CmsManagerSelectorInterface
     */
    protected $cmsSelector;

    /**
     * @var PageServiceManagerInterface
     */
    protected $pageServiceManager;

    /**
     * @var SeoPageInterface
     */
    protected $seoPage;

    /**
     * @var SettingService
     */
    protected $setting;

    /**
     * ControllerListener constructor.
     *
     * @param CmsManagerSelectorInterface $cmsSelector
     * @param PageServiceManagerInterface $pageServiceManager
     * @param SeoPageInterface $seoPage
     * @param SettingService $setting
     */
    public function __construct(CmsManagerSelectorInterface $cmsSelector, PageServiceManagerInterface $pageServiceManager, SeoPageInterface $seoPage, SettingService $setting)
    {
        $this->cmsSelector = $cmsSelector;
        $this->pageServiceManager = $pageServiceManager;
        $this->seoPage = $seoPage;
        $this->setting = $setting;
    }

    /**
     * onKernelController method
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $cms = $this->cmsSelector->retrieve();
        $page = $cms->getCurrentPage();

        if (!$page) {
            return;
        }

        if ($page->getTitle()) {
            $this->seoPage->setTitle($page->getTitle());
        }

        if ($page->getMetaDescription()) {
            $this->seoPage->addMeta('name', 'description', $page->getMetaDescription());
        }

        if ($page->getMetaKeyword()) {
            $this->seoPage->addMeta('name', 'keywords', $page->getMetaKeyword());
        }

        $googleBot = $this->setting->getField('GOOGLE', 'BOT');
        if ($googleBot && $googleBot->isEnabled() && $googleBot->getValue()) {
            $this->seoPage->addMeta('name', 'robots', $googleBot->getValue());
        }

        $googleSearchConsole = $this->setting->getField('GOOGLE', 'SEARCH_CONSOLE');
        if ($googleSearchConsole && $googleSearchConsole->isEnabled() && $googleSearchConsole->getValue()) {
            $this->seoPage->addMeta('name', 'google-site-verification', $googleSearchConsole->getValue());
        }
    }
}
