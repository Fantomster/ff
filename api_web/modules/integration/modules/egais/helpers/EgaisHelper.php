<?php

namespace api_web\modules\integration\modules\egais\helpers;

use api_web\modules\integration\modules\egais\classes\XmlParser;
use api\common\models\egais\egaisSettings;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\httpclient\Client;
use yii\web\BadRequestHttpException;

/**
 * Класс для работы с ЕГАИС
 * */
class EgaisHelper
{
    /**@var int organization id */
    public $orgId;

    /**@var array типы входящих документов */
    static $type_document = [
        'TICKET',
        //'WAYBILL_V2',
        //'FORMF2REGINFO',
        //'TTNHISTORYF2REG'
    ];

    /**
     * EgaisHelper constructor.
     */
    public function __construct()
    {
        $this->orgId = \Yii::$app->user->identity->organization_id;
    }

    /**
     * @param $url
     * @param $data
     * @param $queryType
     * @return bool|string
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function sendEgaisQuery($url, $data, $queryType)
    {
        $client = new Client();
        $queryRests = $client->createRequest()
            ->setMethod('POST')
            ->setUrl("{$url}/opt/in/{$queryType}")
            ->addFileContent('xml_file', $data)
            ->send();

        if (!$queryRests->isOk) {
            throw new BadRequestHttpException('The response invalid!');
        }

        $replyId = (new XmlParser())->parseEgaisQuery($queryRests->content);

        sleep(3);

        $getUrlDoc = $client->createRequest()
            ->setMethod('get')
            ->setUrl("{$url}/opt/out?replyId={$replyId}")
            ->send();

        if (!$getUrlDoc->isOk) {
            throw new BadRequestHttpException('The response invalid!');
        }

        $getDataDoc = (new XmlParser())->parseUrlDoc($getUrlDoc->content);

        if (!empty($getDataDoc)) {
            return $this->getOneDocument($url, $getDataDoc);
        }

        return true;
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

    // Получение всех входящих документов типа $request['type'] или без

    /**
     * @param $url
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function getAllIncomingDoc($url, $request)
    {
        $page = (isset($request['pagination']['page']) ? $request['pagination']['page'] : 1);
        $pageSize = (isset($request['pagination']['page_size']) ? $request['pagination']['page_size'] : 12);

        (!empty($request["type"])) ? $type = '/' . $request["type"] : $type = null;

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl($url . '/opt/out' . $type)
            ->send();

        if (!$response->isOk) {
            throw new BadRequestHttpException('The response invalid!');
        }

        $docs = (new XmlParser())->parseIncomingDocs($response->content);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $docs,
            'pagination' => $pagination,
            'sort' => [
                'attributes' => ['id'],
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ],
        ]);

        $result = [];
        foreach ($dataProvider->getModels() as $model) {
            array_push($result, $model);
        }

        return [
            'document' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];
    }

    /**
     * @param $url
     * @param $request
     * @return string
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function getOneDocument($url, $request)
    {
        $query = "{$url}/opt/out/{$request['type']}/{$request['id']}";
        $parser = "parse{$request['type']}";

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl($query)
            ->send();

        if (!$response->isOk) {
            throw new BadRequestHttpException('The response invalid!');
        }

        return (new XmlParser())->$parser($response->content);
    }

    public function setSettings($request)
    {
        $settings = new egaisSettings();
        $result = $settings->setSettings($request, $this->orgId);

        return $result;
    }
}