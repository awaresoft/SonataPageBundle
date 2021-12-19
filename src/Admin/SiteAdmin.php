<?php

namespace Awaresoft\Sonata\PageBundle\Admin;

use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\PageBundle\Admin\SiteAdmin as BaseSiteAdmin;

/**
 * Extended SiteAdmin class.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class SiteAdmin extends BaseSiteAdmin
{
    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            $collection->remove('delete');
        }
    }
}
