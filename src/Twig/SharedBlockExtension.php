<?php

namespace Awaresoft\Sonata\PageBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SharedBlockExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('awaresoft_block_render', [$this, 'renderSharedBlock'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'shared_block_extensions';
    }

    /**
     * Render shared block by slug
     *
     * @param $slug
     *
     * @return null
     */
    public function renderSharedBlock($slug)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $cmsPage = $this->container->get('sonata.page.cms_manager_selector')->retrieve();
        $page = $cmsPage->getCurrentPage();
        $site = $page->getSite();

        $sharedBlock = $em->getRepository('AwaresoftSonataPageBundle:Block')->findOneBySlugAndSite($slug, $site);

        if (!$sharedBlock) {
            $sharedBlock = $em->getRepository('AwaresoftSonataPageBundle:Block')->findOneBySlug($slug);
        }

        if (!$sharedBlock) {
            return null;
        }

        $blockInterface = $this->container->get('sonata.block.context_manager.default');
        $blockService = $this->container->get('sonata.page.block.shared_block');
        $blockContext = $blockInterface->get(['type' => 'sonata.page.block.shared_block']);
        $blockContext->getBlock()->setSetting('blockId', $sharedBlock->getId());
        $blockContent = $blockService->execute($blockContext);

        return $blockContent->getContent();
    }


    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
