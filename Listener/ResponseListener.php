<?php

namespace Awaresoft\Sonata\PageBundle\Listener;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\Translator;
use Sonata\PageBundle\Listener\ResponseListener as BaseResponseListener;

/**
 * Extension of Sonata ResponseListener class.
 * Prevent before showing internal error for users.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ResponseListener extends BaseResponseListener
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @param CmsManagerSelectorInterface $cmsSelector CMS manager selector
     * @param PageServiceManagerInterface $pageServiceManager Page service manager
     * @param DecoratorStrategyInterface $decoratorStrategy Decorator strategy
     * @param EngineInterface $templating The template engine
     * @param ContainerInterface $container
     */
    public function __construct(CmsManagerSelectorInterface $cmsSelector, PageServiceManagerInterface $pageServiceManager, DecoratorStrategyInterface $decoratorStrategy, EngineInterface $templating, ContainerInterface $container)
    {
        parent::__construct($cmsSelector, $pageServiceManager, $decoratorStrategy, $templating);

        $this->translator = $container->get('translator');
    }

    /**
     * @inheritdoc
     */
    public function onCoreResponse(FilterResponseEvent $event)
    {
        try {
            parent::onCoreResponse($event);
        } catch (InternalErrorException $ex) {
            if ($this->cmsSelector->isEditor()) {
                throw new InternalErrorException('No page instance available for the url, run the sonata:page:update-core-routes and sonata:page:create-snapshots commands');
            }

            throw new NotFoundHttpException($this->translator->trans('site_not_exists'));
        }
    }
}
