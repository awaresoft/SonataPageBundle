<?php

namespace Awaresoft\Sonata\PageBundle\Admin;

use Sonata\PageBundle\Admin\SnapshotAdmin as BaseSnapshotAdmin;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Extended Admin definition for the Snapshot class
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class SnapshotAdmin extends BaseSnapshotAdmin
{
    protected $baseRouteName = 'admin_awaresoft_cms_snapshot';
    protected $baseRoutePattern = 'awaresoft/cms/snapshot';
    protected $parentAssociationMapping = 'page';

    public function generateUrl($name, array $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if ($name == 'list' && $this->getParent()) {
            return $this->getParent()->generateUrl('edit', ['id' => $this->getParent()->getSubject()->getId()]);
        }

        if ($name == 'edit' && $this->getParent()) {
            return $this->getParent()->generateUrl('edit', ['id' => $this->getParent()->getSubject()->getId()]);
        }

        return parent::generateUrl($name, $parameters, $absolute);
    }
}
