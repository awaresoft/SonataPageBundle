<?php

namespace Awaresoft\Sonata\PageBundle\Block;

use Awaresoft\Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RawBlock
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class RawBlock extends BaseBlockService
{

    /**
     * Set default settings
     *
     * @param OptionsResolver $resolver
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'template' => 'AwaresoftSonataPageBundle:Block:raw_block.html.twig',
            'text' => null
        ));
    }

    /**
     * @param FormMapper $formMapper
     * @param BlockInterface $block
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('text', 'textarea', array('required' => true)),
            )
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
        return $this->renderResponse($blockContext->getTemplate(), array(
            'text' => $blockContext->getBlock()->getSetting('text'),
            'block_context' => $blockContext,
            'block' => $blockContext->getBlock(),
        ), $response);
    }
}
