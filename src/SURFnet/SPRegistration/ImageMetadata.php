<?php


namespace SURFnet\SPRegistration;


use RuntimeException;

class ImageMetadata
{
    /**
     * @var ImageDimensions
     */
    private $dimensions;

    /**
     * @var array
     */
    private $imageType;

    /**
     * Image constructor.
     * @param ImageDimensions $dimensions
     * @param string $mimeType
     */
    public function __construct(ImageDimensions $dimensions, $imageType)
    {
        $this->dimensions = $dimensions;
        $this->imageType = $imageType;
    }

    /**
     * @param $url
     * @return null|ImageMetadata
     */
    public static function fromUrl($url)
    {
        $imgData = @getimagesize($url);

        if ($imgData === false) {
            return null;
        }

        list($width, $height, $mimeType) = $imgData;

        return new ImageMetadata(
            new ImageDimensions($width, $height),
            $mimeType
        );
    }

    /**
     * @return ImageDimensions
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }


    public function getExtension()
    {
        if ($this->imageType === IMAGETYPE_GIF) {
            return 'gif';
        }
        if ($this->imageType === IMAGETYPE_JPEG) {
            return 'jpg';
        }
        if ($this->imageType === IMAGETYPE_JPEG2000) {
            return 'jpg';
        }
        if ($this->imageType === IMAGETYPE_PNG) {
            return 'png';
        }
        throw new RuntimeException(
            'Unknown extension for image type' . $this->imageType
        );
    }
}
