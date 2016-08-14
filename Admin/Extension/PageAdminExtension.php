<?php

namespace Awaresoft\Sonata\PageBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $formMapper->add('inCms', 'choice', array(
            'choices' => array(
                '0' => $formMapper->getAdmin()->trans('admin.admin.label.type_no'),
                '1' => $formMapper->getAdmin()->trans('admin.admin.label.type_yes'),
            ),
        ));

        $formMapper->setHelps(array(
           'inCms' => $formMapper->getAdmin()->trans('page.admin.help.cms')
        ));
    }
}