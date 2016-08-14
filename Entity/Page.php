<?php

namespace Awaresoft\Sonata\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page__page", indexes={
 *     @ORM\Index(name="idx_route_name", columns={"route_name"})
 * })
 * @ORM\Entity(repositoryClass="Awaresoft\Sonata\PageBundle\Entity\PageRepository")
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Page extends BasePage
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $inCms;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $redirectUrl;

    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->inCms = false;
        $this->hidden = false;
    }

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isInCms()
    {
        return $this->inCms;
    }

    /**
     * @param boolean $inCms
     * return void
     */
    public function setInCms($inCms)
    {
        $this->inCms = $inCms;
    }

    /**
     * @return mixed
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param mixed $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param mixed $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

}