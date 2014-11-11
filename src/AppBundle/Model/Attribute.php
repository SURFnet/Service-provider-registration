<?php

namespace AppBundle\Model;

/**
 * Class Attribute
 */
class Attribute
{
    /**
     * @var bool
     */
    private $requested;

    /**
     * @var string
     */
    private $motivation;

    /**
     * @return boolean
     */
    public function isRequested()
    {
        return $this->requested;
    }

    /**
     * @param bool $requested
     *
     * @return $this
     */
    public function setRequested($requested)
    {
        $this->requested = $requested;

        return $this;
    }

    /**
     * @return string
     */
    public function getMotivation()
    {
        return $this->motivation;
    }

    /**
     * @param string $motivation
     *
     * @return $this
     */
    public function setMotivation($motivation)
    {
        $this->motivation = $motivation;

        return $this;
    }
}
