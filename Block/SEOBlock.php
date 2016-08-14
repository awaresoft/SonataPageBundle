<?php

namespace Awaresoft\Sonata\PageBundle\Block;

use Awaresoft\Sonata\BlockBundle\Block\BaseBlockService;
use Awaresoft\BlockBundle\Entity\Block;
use Awaresoft\BlockBundle\Exception\BlockNotExistsException;
use Awaresoft\BlockBundle\Exception\TemplateNotFoundException;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Block SEOBlock
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class SEOBlock extends BaseBlockService
{

    /**
     * Set default settings
     *
     * @param OptionsResolver $resolver
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'template' => 'AwaresoftSonataPageBundle:Block:block_seo.html.twig',
            'containerClass' => null
        ));
    }

    /**
     * Execute block
     *
     * @param BlockContextInterface $blockContext
     * @param Response|null $response
     * @return Response
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $pageManager = $this->container->get('sonata.page.manager.page');
        $sites = $this->container->get('sonata.page.manager.site')->findBy(array());
        $count = array(
            'hybrid_pages' => 0,
            'title_0' => 0,
            'title_30' => 0,
            'title_60' => 0,
            'title_100' => 0,
            'description_0' => 0,
            'description_20' => 0,
            'description_40' => 0,
            'description_60' => 0,
            'description_80' => 0,
            'description_100' => 0,
        );

        foreach ($sites as $site) {
            $pages = $pageManager->findBy(array('inCms' => true, 'site' => $site), array('position' => 'ASC'));

            foreach ($pages as $page) {
                $count = $this->prepareCountArray($page, $count);
            }
        }

        return $this->renderResponse($blockContext->getTemplate(), array(
            'blockContext' => $blockContext,
            'block' => $blockContext->getBlock(),
            'count' => $count
        ), $response);
    }

    /**
     * Prepare count array
     *
     * @param PageInterface $page
     */
    protected function prepareCountArray(PageInterface $page, $count)
    {
        if ($page->isHybrid()) {
            $count['hybrid_pages']++;
        } else {
            $titleLength = strlen($page->getTitle());
            $descriptionLength = strlen($page->getMetaDescription());

            if ($titleLength) {
                if ($titleLength > 0 && $titleLength <= 20) {
                    $count['title_30']++;
                } elseif($titleLength > 20 && $titleLength <= 40) {
                    $count['title_60']++;
                } elseif($titleLength > 40 && $titleLength <= 70) {
                    $count['title_100']++;
                } else {
                    $count['title_30']++;
                }
            } else {
                $count['title_0']++;
            }

            if ($descriptionLength) {
                if ($descriptionLength > 0 && $descriptionLength <= 32) {
                    $count['description_0']++;
                } elseif($titleLength > 32 && $titleLength <= 64) {
                    $count['description_40']++;
                } elseif($titleLength > 64 && $titleLength <= 96) {
                    $count['description_60']++;
                } elseif($titleLength > 96 && $titleLength <= 128) {
                    $count['description_80']++;
                } elseif($titleLength > 128 && $titleLength <= 160) {
                    $count['description_100']++;
                } else {
                    $count['description_20']++;
                }
            } else {
                $count['description_0']++;
            }
        }

        return $count;
    }

}