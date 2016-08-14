<?php

namespace Awaresoft\Sonata\PageBundle\Entity;

use Awaresoft\Sonata\PageBundle\Model\Page;

/**
 * The class stores Page information (changed extension)
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
abstract class BasePage extends Page
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blocks = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function prePersist()
    {
        $this->createdAt = new \DateTime;
        $this->updatedAt = new \DateTime;
    }

    public function preUpdate()
    {
        $this->updatedAt = new \DateTime;
    }
}
