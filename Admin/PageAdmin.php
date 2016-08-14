<?php

namespace Awaresoft\Sonata\PageBundle\Admin;

use Sonata\PageBundle\Admin\PageAdmin as BasePageAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\PageBundle\Model\PageInterface;

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
            ->add('site')
            ->add('name')
            ->add('type', null, ['field_type' => 'sonata_page_type_choice'])
            ->add('pageAlias')
            ->add('parent', null, [], null, null, [
                'admin_code' => 'sonata.page.admin.page',
            ])
            ->add('edited')
            ->add('hybrid', 'doctrine_orm_callback', [
                'callback' => function ($queryBuilder, $alias, $field, $data) {
                    if (in_array($data['value'], ['hybrid', 'cms'])) {
                        $queryBuilder->andWhere(sprintf('%s.routeName %s :routeName', $alias, $data['value'] == 'cms' ? '=' : '!='));
                        $queryBuilder->setParameter('routeName', PageInterface::PAGE_ROUTE_CMS_NAME);
                    }
                },
                'field_options' => [
                    'required' => false,
                    'choices' => [
                        'hybrid' => $this->trans('hybrid'),
                        'cms' => $this->trans('cms'),
                    ],
                ],
                'field_type' => 'choice',
            ]);
    }
}
