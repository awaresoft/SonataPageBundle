<?php

namespace Awaresoft\Sonata\PageBundle\Admin;

use Sonata\PageBundle\Admin\SharedBlockAdmin as BaseSharedBlockAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\PageBundle\Entity\BaseBlock;

/**
 * Extended SharedBlockAdmin class
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class SharedBlockAdmin extends BaseSharedBlockAdmin
{
    protected $multisite = true;

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('site')
            ->add('type')
            ->add('enabled', null, ['editable' => true])
            ->add('updatedAt');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var BaseBlock $block */
        $block = $this->getSubject();

        // New block
        if ($block->getId() === null) {
            $block->setType($this->request->get('type'));
        }

        $formMapper
            ->with('form.field_group_general')
            ->add('name', null, ['required' => true])
            ->add('identifier', null, [
                'required' => false,
            ])
            ->add('site', null, [
                'required' => false,
            ])
            ->add('enabled')
            ->end();

        $formMapper->with('form.field_group_options');

        /** @var BaseBlockService $service */
        $service = $this->blockManager->get($block);

        if ($block->getId() > 0) {
            $service->buildEditForm($formMapper, $block);
        } else {
            $service->buildCreateForm($formMapper, $block);
        }

        $formMapper->end();

        $formMapper->setHelps([
            'key' => $this->trans('admin.admin.help.sluggable_field'),
        ]);
    }
}
