<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Site
 *
 * @ORM\Table(name="site")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SiteRepository")
 */
class Site
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var string
     */
    private $url;

    /**
     * @var boolean
     */
    private $ismarfeeable;


    /**
     * Set url
     *
     * @param string $url
     *
     * @return Site
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set ismarfeeable
     *
     * @param boolean $ismarfeeable
     *
     * @return Site
     */
    public function setIsmarfeeable($ismarfeeable)
    {
        $this->ismarfeeable = $ismarfeeable;

        return $this;
    }

    /**
     * Get ismarfeeable
     *
     * @return boolean
     */
    public function getIsmarfeeable()
    {
        return $this->ismarfeeable;
    }
}
