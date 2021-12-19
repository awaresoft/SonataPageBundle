<?php

namespace Awaresoft\Sonata\PageBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Block\SharedBlockBlockService as BaseSharedBlockBlock;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Extended SharedBlockBlock class
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class SharedBlockBlock extends BaseSharedBlockBlock
{

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'template' => 'SonataPageBundle:Block:shared_block_block.html.twig',
            'blockId' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $block = $blockContext->getBlock();

        if (!$block->getSetting('blockId') instanceof BlockInterface) {
            $this->load($block);
        }

        /** @var Block $sharedBlock */
        $sharedBlock = $block->getSetting('blockId');

        return $this->renderResponse($blockContext->getTemplate(), array(
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'sharedBlock' => $sharedBlock,
        ), $response);
    }
}
