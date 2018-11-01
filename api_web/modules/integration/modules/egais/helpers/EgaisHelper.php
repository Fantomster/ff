<?php

namespace api_web\modules\integration\modules\egais\helpers;

//use api\common\models\merc\MercVsd;
use api_web\classes\UserWebApi;
use api_web\components\Registry;
use common\helpers\DBNameHelper;
use common\models\IntegrationSettingValue;
use api_web\modules\integration\modules\vetis\api\cerber\cerberApi;

use api_web\modules\integration\modules\egais\classes\BodyPost;
use api_web\modules\integration\modules\egais\classes\oFile;

use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Класс для работы с ЕГАИС
 * */
class EgaisHelper
{
    /**@var int organization id */
    private $orgId;

    /**
     * EgaisHelper constructor.
     */
    public function __construct()
    {
        $this->orgId = \Yii::$app->user->identity->organization_id;
    }

    public function sendEgaisQuery($url, $queryType, $data)
    {

        $delimiter = '------------------------' . uniqid();

        // Формируем объект oFile содержащий файл
        $file = new oFile('QueryParameters.xml', 'text/xml; charset=utf-8', $data);

// Формируем тело POST запроса
        $post = BodyPost::Get(array('xml_file' => $file), $delimiter);

        // Инициализируем  CURL
        $ch = curl_init();

// Указываем на какой ресурс передаем файл
        curl_setopt($ch, CURLOPT_URL, $url . '/opt/in/' . $queryType);
// Указываем, что будет осуществляться POST запрос
        curl_setopt($ch, CURLOPT_POST, 1);
// Передаем тело POST запроса
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

//        curl_setopt($ch, CURLOPT_HEADER, true);

        /* Указываем дополнительные данные для заголовка:
             Content-Type - тип содержимого,
             boundary - разделитель и
             Content-Length - длина тела сообщения */
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data; boundary=' . $delimiter, 'Content-Length: ' . strlen($post)));

// Отправляем POST запрос на удаленный Web сервер
        $data = curl_exec($ch);

        print_r($data);
        die();

        return ['result' => $data];
    }
}