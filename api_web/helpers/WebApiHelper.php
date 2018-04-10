<?php

namespace api_web\helpers;

use yii\web\BadRequestHttpException;

/**
 * Class WebApiHelper
 * @package api_web\helpers
 */
class WebApiHelper
{
    /**
     * @param array $response
     * @return array
     */
    public static function response(Array $response)
    {
        return $response;
    }

    /**
     * Получает картинку в base64 декодируем и конвертируем в PNG
     * Возвращаем тот-же base64 только уже png картинки, так так
     * UploadBehavior работает только с png
     * @param $imageSourceBase64
     * @return string
     * @throws BadRequestHttpException
     */
    public static function convertLogoFile($imageSourceBase64)
    {
        $temp_filename = dirname(__DIR__) . "/runtime/" . uniqid() . '.png';
        try {

            if (strstr($imageSourceBase64, 'base64,') !== false) {
                $source = explode('base64,', $imageSourceBase64, 2)[1];
            } else {
                $source = $imageSourceBase64;
            }

            imagepng(imagecreatefromstring(base64_decode($source)), $temp_filename);
            $type = pathinfo($temp_filename, PATHINFO_EXTENSION);
            $data = file_get_contents($temp_filename);
            $return = 'data:image/' . $type . ';base64,' . base64_encode($data);
            unlink($temp_filename);
            return $return;
        } catch (\Exception $e) {
            if (file_exists($temp_filename)) {
                unlink($temp_filename);
            }
            throw new BadRequestHttpException('Вы уверены, что вы прислали картинку? проверьте!');
        }
    }
}