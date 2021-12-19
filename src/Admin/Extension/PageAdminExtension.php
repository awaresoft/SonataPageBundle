<?php

namespace Awaresoft\Sonata\PageBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class PageAdminExtension
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class PageAdminExtension extends AbstractAdminExtension
{
    /**
     * Extension of parent method
     *
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        // add new field
        $formMapper->add('inCms', ChoiceType::class, array(
            'choices' => array(
                $formMapper->getAdmin()->trans('admin.admin.label.type_no') => 0,
                $formMapper->getAdmin()->trans('admin.admin.label.type_yes') => 1,
            ),
            'help' => $formMapper->getAdmin()->trans('page.admin.help.cms')
        ));
    }
}
