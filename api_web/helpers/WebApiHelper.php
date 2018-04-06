<?php

namespace api_web\helpers;
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
     */
    public static function convertLogoFile($imageSourceBase64)
    {
        $temp_filename = dirname(__DIR__) . "/runtime/" . uniqid() . '.png';
        $source = explode('base64,', $imageSourceBase64, 2)[1];
        imagepng(imagecreatefromstring(base64_decode($source)), $temp_filename);
        $type = pathinfo($temp_filename, PATHINFO_EXTENSION);
        $data = file_get_contents($temp_filename);
        $return = 'data:image/' . $type . ';base64,' . base64_encode($data);
        unlink($temp_filename);
        return $return;
    }
}