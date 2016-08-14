<?php

namespace Awaresoft\Sonata\PageBundle\Entity;

use Sonata\PageBundle\Entity\BaseBlock as BaseBlock;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="page__block")
 * @ORM\Entity(repositoryClass="Awaresoft\Sonata\PageBundle\Entity\BlockRepository")
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Block extends BaseBlock
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Gedmo\Slug(fields={"name"}, separator="_")
     *
     * @var string
     */
    protected $identifier;

    /**
     * @ORM\ManyToOne(targetEntity="Awaresoft\Sonata\PageBundle\Entity\Site")
     *
     * @var Site
     */
    protected $site;

    /**
     * Get id
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param mixed $identifier
     *
     * @return Block
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param Site $site
     *
     * @return Block
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }
}