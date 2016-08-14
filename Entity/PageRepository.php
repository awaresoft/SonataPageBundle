<?php

namespace Awaresoft\Sonata\PageBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Sonata\PageBundle\Model\PageInterface;

/**
 * Page repository class
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class PageRepository extends EntityRepository
{

    /**
     * @param Site $site
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findCmsPages($site = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.inCms = :inCms')
            ->andWhere('p.routeName != :routeName')
            ->setParameter('inCms', true)
            ->setParameter('routeName', '_page_internal_global');

        if ($site) {
            $qb
                ->andWhere('p.site = :site')
                ->setParameter('site', $site);
        }

        return $qb;
    }

    /**
     * @param Site $site
     * @return null|Page
     */
    public function findHomepage($site = null)
    {
        if ($site === null) {
            $site = $this->_em->getRepository('AwaresoftSonataPageBundle:Site')->findOneBy(array('isDefault' => true));
        }

        return $this->findOneBy(array(
            'site' => $site,
            'routeName' => 'homepage'
        ), array(
            'id' => 'ASC'
        ));
    }

    /**
     * @param PageInterface $page
     * @param array $orderBy
     * @return Page[]
     */
    public function findChildrenByPage(PageInterface $page, $orderBy = [])
    {
        return $this->findBy(array(
            'parent' => $page,
            'enabled' => true,
        ), $orderBy);
    }

    /**
     * @param PageInterface $page
     * @param array $orderBy
     * @return Page[]
     */
    public function findVisibleChildrenByPage(PageInterface $page, $orderBy = [])
    {
        return $this->findBy(array(
            'parent' => $page,
            'enabled' => true,
            'hidden' => false
        ), $orderBy);
    }

}