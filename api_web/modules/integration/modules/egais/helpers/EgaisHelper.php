<?php

namespace api_web\modules\integration\modules\egais\helpers;

use api_web\components\WebApi;
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\modules\egais\classes\XmlParser;
use common\models\egais\EgaisTypeWriteOff;
use common\models\egais\EgaisWriteOff;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\httpclient\Client;
use yii\web\BadRequestHttpException;

/**
 * Класс для работы с ЕГАИС
 * */
class EgaisHelper extends WebApi
{
    /**@var array типы входящих документов */
    static $type_document = [
        'TICKET',
        'REPLYRESTS',
        //'WAYBILL_V2',
        //'FORMF2REGINFO',
        //'TTNHISTORYF2REG'
    ];

    /**
     * @param string $url
     * @param string $data
     * @param string $queryType
     * @return bool|string
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function sendActWriteOff(string $url, string $data, string $queryType)
    {
        $result = (new XmlParser())->parseInputActWriteOffV3($data);
        $typeWriteOff = EgaisTypeWriteOff::findOne(['type' => $result['TypeWriteOff']]);
        $orgId = $this->user->organization_id;

        if (EgaisWriteOff::find()->where(['org_id' => $orgId, 'act_number' => $result['ActNumber']])->exists()) {
            throw new BadRequestHttpException('dictionary.act_write_off_number_error');
        }

        (new EgaisWriteOff([
            'org_id'         => $orgId,
            'identity'       => $result['identity'],
            'act_number'     => $result['ActNumber'],
            'act_date'       => WebApiHelper::asDatetime($result['ActDate']),
            'type_write_off' => $typeWriteOff ? $typeWriteOff->id : 8,
            'note'           => $result['Note'],
            'status'         => null,
        ]))->save();

        return self::sendEgaisQuery($url, $data, $queryType);
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
    public static function sendEgaisQuery($url, $data, $queryType)
    {
        $client = new Client();
        $queryRests = $client->createRequest()
            ->setMethod('POST')
            ->setUrl("{$url}/opt/in/{$queryType}")
            ->addFileContent('xml_file', $data)
            ->send();

        if (!$queryRests->isOk) {
            throw new BadRequestHttpException('dictionary.request_error');
        }

        $replyId = (new XmlParser())->parseEgaisQuery($queryRests->content);

        sleep(3);

        $getUrlDoc = $client->createRequest()
            ->setMethod('get')
            ->setUrl("{$url}/opt/out?replyId={$replyId}")
            ->send();

        if (!$getUrlDoc->isOk) {
            throw new BadRequestHttpException('dictionary.request_error');
        }

        $getDataDoc = (new XmlParser())->parseUrlDoc($getUrlDoc->content);

        if (!empty($getDataDoc)) {
            return self::getOneDocument($url, $getDataDoc);
        }

        return true;
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
    public static function getAllIncomingDoc($url, $request)
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
            throw new BadRequestHttpException('dictionary.request_error');
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
    public static function getOneDocument($url, $request)
    {
        $query = "{$url}/opt/out/{$request['type']}/{$request['id']}";
        $parser = "parse{$request['type']}";

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl($query)
            ->send();

        if (!$response->isOk) {
            throw new BadRequestHttpException('dictionary.request_error');
        }

        return (new XmlParser())->$parser($response->content);
    }
}