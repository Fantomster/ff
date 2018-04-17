<?php

namespace api_web\helpers;

use common\models\Organization;
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

    /**
     * Собираем массив для отдачи, из модели
     * @param Organization $model
     * @return mixed
     */
    public static function prepareOrganization($model)
    {
        if (empty($model)) {
            return null;
        }

        $item['id'] = (int)$model->id;
        $item['name'] = $model->name ?? "";
        $item['legal_entity'] = $model->legal_entity ?? "";
        $item['contact_name'] = $model->contact_name ?? "";
        $item['phone'] = $model->phone ?? "";
        $item['email'] = $model->email ?? "";
        $item['site'] = $model->website ?? "";
        $item['address'] = $model->address ?? "";
        $item['image'] = $model->pictureUrl;
        $item['type_id'] = (int)$model->type_id;
        $item['type'] = $model->type->name ?? "";
        $item['rating'] = round($model->ratingStars, 1);
        $item['house'] = ($model->street_number === 'undefined' ? "" : $model->street_number ?? "");
        $item['route'] = ($model->route === 'undefined' ? "" : $model->route ?? "");
        $item['city'] = ($model->locality === 'undefined' ? "" : $model->locality ?? "");
        $item['administrative_area_level_1'] = ($model->administrative_area_level_1 === 'undefined' ? "" : $model->administrative_area_level_1 ?? "");
        $item['country'] = ($model->country === 'undefined' ? "" : $model->country ?? "");
        $item['place_id'] = ($model->place_id === 'undefined' ? "" : $model->place_id ?? "");
        $item['about'] = $model->about ?? "";

        if ($model->type_id == Organization::TYPE_SUPPLIER) {
            $item['allow_editing'] = $model->allow_editing;
        }

        return $item;
    }

    public static $clearValue = ['d.m.Y', ''];

    public static function clearRequest(&$post)
    {
        if (is_array($post)) {
            foreach ($post as $key => &$value) {
                if (is_array($value)) {
                    self::clearRequest($value);
                } else {
                    if (in_array($value, self::$clearValue) || empty($value)) {
                        unset($post[$key]);
                    }
                }
            }
        } else {
            if (in_array($post, self::$clearValue) || empty($post)) {
                $post = null;
            }
        }
    }
}