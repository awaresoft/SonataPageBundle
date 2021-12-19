<?php

namespace Awaresoft\Sonata\PageBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\PageBundle\Admin\PageAdmin as BasePageAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

/**
 * Extended PageAdmin class.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class PageAdmin extends BasePageAdmin
{
    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->remove('parent')
            ->add('parent', null, [], null, null, [
                'admin_code' => 'sonata.page.admin.page',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $formMapper
            ->with('form_page.group_main_label')
                ->add('routeName', null, [
                    'required' => false,
                    'attr' => [
                        'readonly' => true,
                    ]
                ])
            ->end();
    }
}
