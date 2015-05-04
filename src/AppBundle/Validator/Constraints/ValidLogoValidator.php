<?php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidLogoValidator
 */
class ValidLogoValidator extends ConstraintValidator
{
    /**
     * @param string     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        $imgData = @getimagesize($value);

        if ($imgData === false) {
            $this->context->addViolation($constraint->message);

            return;
        }

        list($width, $height, $type) = $imgData;

        if ($type !== IMAGETYPE_PNG && $type !== IMAGETYPE_GIF) {
            $this->context->addViolation('Logo should be a PNG or GIF.');

            return;
        }

        if ($width > 300 || $height > 500) {
            $this->context->addViolation('Logo is too big, it should be max. 500 x 300 px.');

            return;
        }

        $fileSize = $this->remoteFileSize($value);

        if ($fileSize === false) {
            $this->context->addViolation('Unable to determine file size of logo.');

            return;
        }

        if ($fileSize > 1024 * 1024) {
            $this->context->addViolation('Logo is too large, it should be max. 1MiB (1.048.576 bytes)');

            return;
        }
    }

    /**
     * @param string $url
     *
     * @return int
     */
    private function remoteFileSize($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_exec($ch);

        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);

        return $size;
    }
}
