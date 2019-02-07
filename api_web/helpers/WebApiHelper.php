<?php

namespace api_web\helpers;

use common\models\Organization;
use yii\web\BadRequestHttpException;

/**
 * Class WebApiHelper
 *
 * @package api_web\helpers
 */
class WebApiHelper
{
    /**
     * Возвращать полное наименование организации
     *
     * @var bool
     */
    public static $fullNameOrganization = false;

    /**
     * Атрибуты, в которых дата
     *
     * @var array
     */
    private static $dateField = [
        'date'
    ];

    /**
     * Форматирование всех дат в ATOM
     *
     * @var array
     */
    public static $formatDate = 'php:' . \DateTime::ATOM;

    /**
     * Значения которых не должно быть в реквесте
     *
     * @var array
     */
    public static $clearValue = ['d.m.Y', ''];

    /**
     * @param array $response
     * @return array
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     */
    public static function response(Array $response)
    {
        //Форматируем все даты в ATOM
        self::formatDate($response);
        return $response;
    }

    /**
     * @param $response
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     */
    private static function formatDate(&$response)
    {
        if (is_array($response)) {
            foreach ($response as $key => &$value) {
                if (is_array($value)) {
                    self::formatDate($value);
                } else {
                    if (self::checkDateAttribute($key) && !empty($value) && preg_match('#.*[\d{4})].*#s', $value)) {
                        $response[$key] = \Yii::$app->formatter->asDatetime($value, self::$formatDate);
                    }
                }
            }
        }
    }

    /**
     * @param $date
     * @return string
     */
    public static function asDatetime($date = null)
    {
        try {
            if (!is_null($date) && !empty($date)) {
                return \Yii::$app->formatter->asDatetime($date, self::$formatDate);
            }
            return \Yii::$app->formatter->asDatetime('now', self::$formatDate);
        } catch (\Throwable $t) {
            return null;
        }
    }

    /**
     * Является ли атрибут датой
     *
     * @param       $string
     * @param array $needle_array
     * @return bool
     */
    private static function checkDateAttribute($string, $needle_array = ['_at', '_date', 'date_', '_delivery'])
    {
        if (is_numeric($string)) {
            return false;
        }

        if (in_array($string, self::$dateField)) {
            return true;
        }

        foreach ($needle_array as $item) {
            if (mb_strstr($string, $item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Получает картинку в base64 декодируем и конвертируем в PNG
     * Возвращаем тот-же base64 только уже png картинки, так так
     * UploadBehavior работает только с png
     *
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
     *
     * @param Organization $model
     * @return mixed
     */
    public static function prepareOrganization(Organization $model = null)
    {
        if (empty($model)) {
            return null;
        }

        $name = static::$fullNameOrganization ? $model->getName() : $model->name;

        $item['id'] = (int)$model->id;
        $item['name'] = self::clearValue($name);
        $item['legal_entity'] = $model->buisinessInfo->legal_entity ?? $model->legal_entity ?? "";
        $item['contact_name'] = self::clearValue($model->contact_name);
        $item['phone'] = self::clearValue($model->phone);
        $item['email'] = $model->buisinessInfo->legal_email ?? $model->email ?? "";
        $item['site'] = self::clearValue($model->website);
        $item['address'] = self::clearValue($model->address);
        $item['image'] = $model->pictureUrl;
        $item['type_id'] = (int)$model->type_id;
        $item['type'] = self::clearValue($model->type->name);
        $item['rating'] = round($model->ratingStars, 1);
        $item['house'] = self::clearValue($model->street_number);
        $item['route'] = self::clearValue($model->route);
        $item['city'] = self::clearValue($model->locality);
        $item['administrative_area_level_1'] = self::clearValue($model->administrative_area_level_1);
        $item['country'] = self::clearValue($model->country);
        $item['place_id'] = self::clearValue($model->place_id);
        $item['about'] = self::clearValue($model->about);
        $item['is_allowed_for_franchisee'] = self::clearValue($model->is_allowed_for_franchisee, 0);
        $item['gmt'] = self::clearValue($model->gmt, 0);
        $item['user_agreement'] = $model->user_agreement;
        $item['confidencial_policy'] = $model->confidencial_policy;

        $item['nds_country'] = null;
        if (!empty($model->vetis_country_uuid) && isset($model->vetisCountry)) {
            $item['nds_country'] = [
                'uuid' => $model->vetis_country_uuid,
                'name' => $model->vetisCountry->name
            ];
        }

        if ($model->type_id == Organization::TYPE_SUPPLIER) {
            $item['inn'] = $model->buisinessInfo->inn ?? $model->inn ?? null;
            $item['allow_editing'] = $model->allow_editing;
            $item['min_order_price'] = round($model->delivery->min_order_price, 2);
            $item['min_free_delivery_charge'] = round($model->delivery->min_free_delivery_charge, 2);
            $item['disabled_delivery_days'] = $model->getDisabledDeliveryDays();
            //Дни доставки
            $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            foreach ($days as $day) {
                $item['delivery_days'][$day] = (int)$model->delivery->{$day};
            }
            $item['is_edi'] = $model->isEdi();
        }
        return $item;
    }

    /**
     * @param        $value
     * @param string $defaultValue
     * @return string
     */
    private static function clearValue($value, $defaultValue = "")
    {
        if ($value === 'undefined') {
            $r = $defaultValue;
        } else {
            $r = $value ?? $defaultValue ?? "";
        }
        return $r;
    }

    /**
     * @param $post
     */
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

    /**
     * Устанавливает заголовки для асинхронного ответа
     * Ставим в начале метода, и курл получает сразу успешный ответ, а скрипт продолжает выполняться дальше
     */
    public static function setAsyncResponseHeader()
    {
        ob_start();
        header("HTTP/1.1 200 OK");
        header("Date: " . date('D, j M Y G:i:s e'));
        header("Server: Apache");
        header('Connection: close');
        header('Content-Encoding: none');
        header("Content-Length: 0");
        header("Content-Type: application/json"); // Tried without this
        ob_end_flush();
        ob_flush();
        flush();
    }

    /**
     * @param $var
     * @param $key
     * @return bool
     */
    public static function valVar($var, $key): bool
    {
        return isset($var[$key]) && !empty($var[$key]) ? true : false;
    }

    /**
     * Генератор, для ускорения работы перебора массивов/объектов
     *
     * @param $items
     * @return \Generator
     */
    public static function generator($items)
    {
        if (is_iterable($items)) {
            foreach ($items as $item) {
                yield $item;
            }
        }
    }
}
