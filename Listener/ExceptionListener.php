<?php

namespace Awaresoft\Sonata\PageBundle\Listener;

use Awaresoft\RedirectBundle\Exception\RedirectException;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Listener\ExceptionListener as BaseExceptionListener;
use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Templating\EngineInterface;

/**
 * ExceptionListener.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ExceptionListener extends BaseExceptionListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * ExceptionListener constructor.
     *
     * @param SiteSelectorInterface $siteSelector
     * @param CmsManagerSelectorInterface $cmsManagerSelector
     * @param bool $debug
     * @param EngineInterface $templating
     * @param PageServiceManagerInterface $pageServiceManager
     * @param DecoratorStrategyInterface $decoratorStrategy
     * @param array $httpErrorCodes
     * @param null|\Psr\Log\LoggerInterface $logger
     * @param ContainerInterface $container
     */
    public function __construct(SiteSelectorInterface $siteSelector, CmsManagerSelectorInterface $cmsManagerSelector, $debug, EngineInterface $templating, PageServiceManagerInterface $pageServiceManager, DecoratorStrategyInterface $decoratorStrategy, array $httpErrorCodes, $logger, ContainerInterface $container)
    {
        parent::__construct($siteSelector, $cmsManagerSelector, $debug, $templating, $pageServiceManager, $decoratorStrategy, $httpErrorCodes, $logger);

        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof NotFoundHttpException) {
            if (class_exists('Awaresoft\RedirectBundle\AwaresoftRedirectBundle')) {
                $redirectFactory = $this->container->get('awaresoft.redirect.provider.factory');

                $response = $redirectFactory->chainValidator($event->getRequest());

                if ($response instanceof Response) {
                    return $event->setResponse($response);
                }
            }
        }

        return parent::onKernelException($event);
    }
}
