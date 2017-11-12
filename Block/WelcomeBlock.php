<?php

namespace Awaresoft\Sonata\PageBundle\Block;

use Awaresoft\Sonata\BlockBundle\Block\BaseBlockService;
use Awaresoft\Sonata\PageBundle\Entity\PageRepository;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WelcomeService
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class WelcomeBlock extends BaseBlockService
{

    /**
     * Set default settings
     *
     * @param OptionsResolver $resolver
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'template' => 'AwaresoftSonataPageBundle:Block:welcome_block.html.twig',
            'containerClass' => null
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
                array('containerClass', 'text', array('required' => false)),
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
        $children = [];
        $page = $blockContext->getBlock()->getPage();

        foreach ($page->getChildren() as $child) {
            if ($child->getEnabled() && !$child->getHidden()) {
                $children[] = $child;
            }
        }

        return $this->renderResponse($blockContext->getTemplate(), array(
            'children' => $children,
            'block_context' => $blockContext,
            'block' => $blockContext->getBlock(),
        ), $response);
    }

    /**
     * @return PageRepository
     */
    protected function getPageRepository()
    {
        return $this->getEntityManager()->getRepository('AwaresoftSonataPageBundle:Page');
    }
}
