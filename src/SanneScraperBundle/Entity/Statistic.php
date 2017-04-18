<?php

namespace SanneScraperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="stats")
 * @ORM\Entity(repositoryClass="SanneScraperBundle\Repository\StatisticRepository")
 */
class Statistic {

    /**
     * @ORM\Column(type="integer", length=45)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="`desc`", type="string", length=45)
     */
    private $desc;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $url;

    /**
     * @ORM\Column(name="`type`", type="integer", length=11)
     */
    private $type;

    /**
     * @ORM\Column(name="`year`", type="integer", length=11)
     * @var int 
     */
    private $year;

    /*
     * Reminder to self, the actual statistics per month are NOT saved, they go straight into the image
     */

    /**
     * @ORM\Column(name="`data`", type="string")
     * @var string
     */
    public $data;

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set desc
     *
     * @param string $desc
     *
     * @return Statistic
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;

        return $this;
    }

    /**
     * Get desc
     *
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Statistic
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
     * Set type
     *
     * @param integer $type
     *
     * @return Statistic
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Statistic
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

}
