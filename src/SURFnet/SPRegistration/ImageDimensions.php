<?php

namespace SURFnet\SPRegistration;

class ImageDimensions
{
    /**
     * @var int
     */
    private $widthPx;

    /**
     * @var int
     */
    private $heightPx;

    /**
     * ImageDimensions constructor.
     * @param int $widthPx
     * @param int $heightPx
     */
    public function __construct($widthPx, $heightPx)
    {
        $this->widthPx = $widthPx;
        $this->heightPx = $heightPx;
    }

    /**
     * @return int
     */
    public function getWidthPx()
    {
        return $this->widthPx;
    }

    /**
     * @return int
     */
    public function getHeightPx()
    {
        return $this->heightPx;
    }

    /**
     * @param ImageDimensions $dimensions
     */
    public function isGreaterThan(ImageDimensions $dimensions)
    {
        return $this->getHeightPx() > $dimensions->getHeightPx() ||
                $this->getWidthPx() > $dimensions->getWidthPx();
    }

    public function __toString()
    {
        return $this->widthPx . 'x' . $this->heightPx;
    }
}
