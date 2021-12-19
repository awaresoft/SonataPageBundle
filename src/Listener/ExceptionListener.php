<?php

namespace Awaresoft\Sonata\PageBundle\Listener;

use Sonata\PageBundle\Listener\ExceptionListener as BaseExceptionListener;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
