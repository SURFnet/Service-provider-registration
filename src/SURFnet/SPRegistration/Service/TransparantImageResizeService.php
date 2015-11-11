<?php

namespace SURFnet\SPRegistration\Service;

use PHPImageWorkshop\Exception\ImageWorkshopException;
use PHPImageWorkshop\ImageWorkshop;
use SURFnet\SPRegistration\ImageDimensions;
use SURFnet\SPRegistration\ImageMetadata;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class TransparantImageResizeService
{
    const TEMP_FILE_PREFIX = 'sp-registration-logo';

    /**
     * @var string
     */
    private $imageFolder;

    /**
     * @var Router
     */
    private $router;

    /**
     * RemoteImageResizeService constructor.
     * @param string $imageFolder
     */
    public function __construct($imageFolder, Router $router)
    {
        $this->imageFolder = $imageFolder;
        $this->router = $router;
    }

    /**
     * @param string $url
     * @param ImageDimensions $requiredDimensions
     * @return string
     */
    public function requireDimensions($url, ImageDimensions $requiredDimensions)
    {
        if (empty($url)) {
            return $url;
        }

        $image = ImageMetadata::fromUrl($url);

        if (!$image) {
            return $url;
        }

        $urlDimensions = $image->getDimensions();

        if ($requiredDimensions->isGreaterThan($urlDimensions)) {
            return $url;
        }

        $tempFile = $this->downloadFile($url);
        $fileName = md5($url)
            . '-'
            . $requiredDimensions->__toString()
            . '.'
            . $image->getExtension();

        $this->resize($fileName, $tempFile, $requiredDimensions);

        $context = $this->router->getContext();

        $newUrl = $context->getScheme()
            . '://'
            . $context->getHost()
            . '/img/logos/'
            . $fileName;

        return $newUrl;
    }

    /**
     * @param string $url
     */
    private function downloadFile($url)
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), self::TEMP_FILE_PREFIX);

        $fp = fopen($tempFilePath, 'w+');
        $ch = curl_init($url);//Here is the file we are downloading, replace spaces with %20
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $tempFilePath;
    }

    /**
     * @param string $fileName
     * @param string $tempFile
     * @param ImageDimensions $requiredDimensions
     * @throws ImageWorkshopException
     */
    private function resize($fileName, $tempFile, ImageDimensions $requiredDimensions)
    {
        $image = ImageWorkshop::initFromPath($tempFile);
        $image->resizeInPixel(
            $requiredDimensions->getWidthPx(),
            $requiredDimensions->getHeightPx(),
            true
        );
        $image->save($this->imageFolder, $fileName);
    }
}
