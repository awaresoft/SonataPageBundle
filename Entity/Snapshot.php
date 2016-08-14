<?php

namespace Awaresoft\Sonata\PageBundle\Entity;

use Sonata\PageBundle\Entity\BaseSnapshot as BaseSnapshot;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page__snapshot")
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Snapshot extends BaseSnapshot
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