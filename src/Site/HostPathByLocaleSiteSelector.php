<?php

namespace Awaresoft\Sonata\PageBundle\Site;

use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Request\SiteRequestInterface;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sonata\PageBundle\Site\HostPathByLocaleSiteSelector as BaseHostPathByLocaleSiteSelector;

/**
 * Extended HostPathByLocaleSiteSelector
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class HostPathByLocaleSiteSelector extends BaseHostPathByLocaleSiteSelector
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @inheritdoc
     *
     * @param SiteManagerInterface $siteManager
     * @param DecoratorStrategyInterface $decoratorStrategy
     * @param SeoPageInterface $seoPage
     * @param ContainerInterface $container
     */
    public function __construct(SiteManagerInterface $siteManager, DecoratorStrategyInterface $decoratorStrategy, SeoPageInterface $seoPage, ContainerInterface $container)
    {
        $this->siteManager = $siteManager;
        $this->decoratorStrategy = $decoratorStrategy;
        $this->seoPage = $seoPage;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function handleKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request instanceof SiteRequestInterface) {
            throw new \RuntimeException('You must change the main Request object in the front controller (app.php) in order to use the `host_with_path_by_locale` strategy');
        }

        $enabledSites = array();
        $pathInfo = null;

        foreach ($this->getSites($request) as $site) {
            if (!$site->isEnabled()) {
                continue;
            }

            $enabledSites[] = $site;

            $match = $this->matchRequest($site, $request);

            if (false === $match) {
                continue;
            }

            $this->site = $site;
            $pathInfo = $match;

            if (!$this->site->isLocalhost()) {
                break;
            }
        }

        if ($this->site) {
            $request->setPathInfo($pathInfo ?: '/');
        }

        // no valid site, but try to find a default site for the current request
        if (!$this->site && count($enabledSites) > 0) {
            $defaultSite = $this->getPreferredSite($enabledSites, $request);

            $event->setResponse(new RedirectResponse($request->getBaseUrl().$defaultSite->getUrl()));
        } elseif ($this->site && $this->site->getLocale()) {
            $request->attributes->set('_locale', $this->site->getLocale());
        }
    }
}
