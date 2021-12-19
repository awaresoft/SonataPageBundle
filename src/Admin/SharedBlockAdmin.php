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
        parent::configureFormFields($formMapper);

        $formMapper
            ->with('form.field_group_additional')
            ->add('identifier', null, [
                'required' => false,
            ])
            ->add('site', null, [
                'required' => false,
            ])
            ->end();
    }
}
