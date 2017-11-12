<?php

namespace Awaresoft\Sonata\PageBundle\Listener;

use Awaresoft\RedirectBundle\Exception\RedirectException;
use Awaresoft\Sonata\AdminBundle\Traits\ControllerHelperTrait;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Listener\ExceptionListener as BaseExceptionListener;
use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
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
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof NotFoundHttpException) {
            $redirectFactory = $this->container->get(
                'awaresoft.redirect.provider.factory',
                ContainerInterface::NULL_ON_INVALID_REFERENCE
            );

            if ($redirectFactory) {
                $response = $redirectFactory->chainValidator($event->getRequest());

                if ($response instanceof Response) {
                    return $event->setResponse($response);
                }
            }
        }
    }
}
