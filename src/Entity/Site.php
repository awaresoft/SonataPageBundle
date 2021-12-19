<?php

namespace Awaresoft\Sonata\PageBundle\Entity;

use Sonata\PageBundle\Entity\BaseSite as BaseSite;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page__site", indexes={
 *     @ORM\Index(name="idx_enabled", columns={"enabled"})
 * })
 * @ORM\Entity(repositoryClass="Awaresoft\Sonata\PageBundle\Entity\SiteRepository")
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Site extends BaseSite
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
}
