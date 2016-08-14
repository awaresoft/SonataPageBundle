<?php

namespace Awaresoft\Sonata\PageBundle\Page\Service;

use Sonata\PageBundle\Page\Service\DefaultPageService as BaseDefaultPageService;
use Sonata\PageBundle\Model\PageInterface;

/**
 * Class DefaultPageService
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class DefaultPageService extends BaseDefaultPageService
{
    /**
     * @inheritdoc
     */
    protected function updateSeoPage(PageInterface $page)
    {
        if (!$this->seoPage) {
            return;
        }

        // fixed setting title
        if ($page->getTitle()) {
            $this->seoPage->setTitle($page->getTitle());
        }

        if ($page->getMetaDescription()) {
            $this->seoPage->addMeta('name', 'description', $page->getMetaDescription());
        }

        if ($page->getMetaKeyword()) {
            $this->seoPage->addMeta('name', 'keywords', $page->getMetaKeyword());
        }

        $this->seoPage->addMeta('property', 'og:type', 'article');
        $this->seoPage->addHtmlAttributes('prefix', 'og: http://ogp.me/ns#');
    }
}
