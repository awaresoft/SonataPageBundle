<?php

namespace Awaresoft\Sonata\PageBundle\Page;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Sonata\PageBundle\Page\PageServiceManager as BasePageServiceManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Extension for PageServiceManager
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class PageServiceManager extends BasePageServiceManager
{

    /**
     * Create a response for given page.
     * Added redirect url support.
     *
     * @param PageInterface $page
     *
     * @return Response
     */
    protected function createResponse(PageInterface $page)
    {
        if ($page->getRedirectUrl()) {
            $page->addHeader('Location', $page->getRedirectUrl());
            return new Response('', 302, $page->getHeaders() ?: array());
        }

        return parent::createResponse($page);
    }
}
