<?php

namespace Awaresoft\Sonata\PageBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;

/**
 * Block repository class
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class BlockRepository extends EntityRepository
{
    /**
     * Find block by Page object and type value
     *
     * @param PageInterface $page
     * @param string $type
     *
     * @return Block
     */
    public function findOneByPageAndType(PageInterface $page, $type)
    {
        return $this->findOneBy(['page' => $page, 'type' => $type]);
    }

    /**
     * Find block by slug and optionally by site object
     *
     * @param string $slug
     * @param SiteInterface $site
     *
     * @return Block
     */
    public function findOneBySlugAndSite($slug, SiteInterface $site = null)
    {
        return $this->findOneBy(['slug' => $slug, 'site' => $site]);
    }
}
