<?php

namespace api_web\modules\integration\modules\egais\helpers;

use api_web\components\WebApi;
use api_web\modules\integration\modules\egais\classes\XmlParser;
use common\models\egais\EgaisActWriteOn;
use common\models\egais\EgaisQueryRests;
use common\models\egais\EgaisTypeChargeOn;
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
        'INVENTORYREGINFO',
        //'WAYBILL_V2',
        //'FORMF2REGINFO',
        //'TTNHISTORYF2REG'
    ];

    /** - Статусы запросов в ЕГАИС - */
    const QUERY_SENT = 1;
    const QUERY_PROCESSED = 2;
    const QUERY_NOT_PROCESSED = 3;
    const QUERY_ERROR = 4;

    static $status_query = [
        self::QUERY_SENT => 'sent',
        self::QUERY_PROCESSED => 'processed',
        self::QUERY_NOT_PROCESSED => 'not processed',
        self::QUERY_ERROR => 'error'
    ];
    /* - - */

    /**
     * @param $orgId
     * @param $url
     * @param $data
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function sendQueryRests($orgId, $url, $data): void
    {
        $client = new Client();
        $queryRests = $client->createRequest()
            ->setMethod('POST')
            ->setUrl("{$url}/opt/in/QueryRests")
            ->addFileContent('xml_file', $data)
            ->send();

        $replyId = (new XmlParser())->parseEgaisQuery($queryRests->content);

        (new EgaisQueryRests([
            'org_id' => $orgId,
            'reply_id' => $replyId,
            'status' => EgaisHelper::QUERY_SENT
        ]))->save();
    }

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
        $result = (new XmlParser())->parseActWriteOffV3($data);
        $typeWriteOff = EgaisTypeWriteOff::findOne(['type' => $result['TypeWriteOff']]);
        $orgId = $this->user->organization_id;

        if (EgaisWriteOff::find()->where(['org_id' => $orgId, 'act_number' => $result['ActNumber']])->exists()) {
            throw new BadRequestHttpException('dictionary.act_write_off_number_error');
        }

        $newAct = new EgaisWriteOff([
            'org_id' => $orgId,
            'identity' => $result['identity'],
            'act_number' => $result['ActNumber'],
            'act_date' => $result['ActDate'],
            'type_write_off' => $typeWriteOff->id,
            'note' => $result['Note'],
            'status' => null,
        ]);

        if (!$newAct->save()) {
            throw new BadRequestHttpException('Could not save to database, check your xml document!');
        }

        return self::sendEgaisQuery($url, $data, $queryType);
    }


    /**
     * @param string $url
     * @param string $data
     * @param string $queryType
     * @return bool
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function sendActWriteOn(string $url, string $data, string $queryType)
    {
        $result = (new XmlParser())->parseActChargeOnV2($data);

        $typeWriteOn = EgaisTypeChargeOn::findOne(['type' => $result['TypeChargeOn']]);
        $orgId = $this->user->organization_id;

        if (EgaisActWriteOn::find()->where(['org_id' => $orgId, 'number' => $result['Number']])->exists()) {
            throw new BadRequestHttpException('dictionary.act_write_off_number_error');
        }

        $newAct = new EgaisActWriteOn([
            'org_id' => $orgId,
            'number' => $result['Number'],
            'act_date' => $result['ActDate'],
            'type_charge_on' => $typeWriteOn->id,
            'note' => $result['Note'],
            'status' => null,
        ]);

        $client = new Client();
        $queryRests = $client->createRequest()
            ->setMethod('POST')
            ->setUrl("{$url}/opt/in/{$queryType}")
            ->addFileContent('xml_file', $data)
            ->send();

        if (!$queryRests->isOk && !$newAct->save()) {
            throw new BadRequestHttpException('dictionary.request_error');
        }

        $reply_id = (new XmlParser())->parseEgaisQuery($queryRests->content);
        $newAct->reply_id = $reply_id;

        return $newAct->save();
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
            return self::getOneDocument($url, $getDataDoc[0]);
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