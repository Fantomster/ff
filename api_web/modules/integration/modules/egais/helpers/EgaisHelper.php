<?php

namespace api_web\modules\integration\modules\egais\helpers;

//use api\common\models\merc\MercVsd;
use api_web\classes\UserWebApi;
use api_web\components\Registry;
use common\helpers\DBNameHelper;
use common\models\IntegrationSettingValue;
use api_web\modules\integration\modules\vetis\api\cerber\cerberApi;

use api\common\models\egais\egaisSettings;
use yii\httpclient\Client;


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
    public $orgId;

    private $docType = [
        '',
    ];

    private $infoType = [
        'total'
    ];

    private $queryType = [
        'queryrests'
    ];

    /**
     * EgaisHelper constructor.
     */
    public function __construct()
    {
        $this->orgId = \Yii::$app->user->identity->organization_id;
    }

    private function getDocument($url)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl($url)
            ->send();
        if ($response->isOk) {
            return $response->content;
        } else {
            return false;
        }
    }

    private function sendQuery($url, $data)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl($url)
            ->setData(['xml_file' => $data])
            ->send();
        if ($response->isOk) {
            return $response->content;
        } else {
            return false;
        }
    }

//    public function sendEgaisQuery($url, $data)
//    {
//
//        $delimiter = '------------------------' . uniqid();
//
//        // Формируем объект oFile содержащий файл
//        $file = new oFile('QueryParameters.xml', 'text/xml; charset=utf-8', $data);
//
//// Формируем тело POST запроса
//        $post = BodyPost::Get(array('xml_file' => $file), $delimiter);
//
//        // Инициализируем  CURL
//        $ch = curl_init();
//
//// Указываем на какой ресурс передаем файл
//        curl_setopt($ch, CURLOPT_URL, $url . '/opt/in/QueryRests');
//// Указываем, что будет осуществляться POST запрос
//        curl_setopt($ch, CURLOPT_POST, 1);
//// Передаем тело POST запроса
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
//
////        curl_setopt($ch, CURLOPT_HEADER, true);
//
//        /* Указываем дополнительные данные для заголовка:
//             Content-Type - тип содержимого,
//             boundary - разделитель и
//             Content-Length - длина тела сообщения */
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data; boundary=' . $delimiter, 'Content-Length: ' . strlen($post)));
//
//// Отправляем POST запрос на удаленный Web сервер
//        $data = curl_exec($ch);
//
//        if ($data) {
//            $res = true;
//        } else {
//            $res=false;
//        }
//
//        return $res;
//    }

    public function sendEgaisQuery($url, $data, $queryType)
    {
        $res = $this->getEgaisDocument($url, 'total', '');
        if (in_array(strtolower($queryType))) {
            $url = $url . '/opt/in/' . $queryType;
        }
        $this->sendQuery($url,$data);
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl($url . '/opt/in/' . $queryType)
            ->setData(['xml_file' => $data])
            ->send();
        if ($response->isOk) {
            return $response->content;
        } else {
            return false;
        }
    }

    /**
     * @param $url
     * @param $type
     * @param $id
     * @return bool|string
     * @throws \Exception
     */
    public function getEgaisDocument($url, $type, $id)
    {
        $client = new Client();
        if (is_int($id)) {
            $id = '/' . $id;
        } else {
            throw new \Exception('id must be integer');
        }
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl($url . '/opt/out/' . $type . $id)
            ->send();
        if ($response->isOk) {
            return $response->content;
        } else {
            return false;
        }
    }

    private function getEgaisInfo($url, $infoType)
    {

    }


    public function setSettings($request)
    {
        $settings = new egaisSettings();
        $result = $settings->setSettings($request, $this->orgId);
        return $result;
    }
}