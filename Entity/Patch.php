<?php

namespace kujaff\VersionsBundle\Entity;

/**
 * Patch already called
 */
class Patch
{
    /**
     * @var string
     */
    private $bundle;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * Set bundle
     *
     * @param string $bundle
     * @return Patch
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
        return $this;
    }

    /**
     * Get bundle
     *
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Patch
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}