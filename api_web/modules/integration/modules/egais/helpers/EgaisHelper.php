<?php

namespace api_web\modules\integration\modules\egais\helpers;

use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\modules\egais\classes\EgaisXmlFiles;
use api_web\modules\integration\modules\egais\classes\XmlParser;
use common\models\egais\EgaisActWriteOn;
use common\models\egais\EgaisQueryRests;
use common\models\egais\EgaisTypeChargeOn;
use common\models\egais\EgaisTypeWriteOff;
use common\models\egais\EgaisWriteOff;
use common\models\Journal;
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
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function sendQueryRests($orgId, $url, $data): void
    {
        $querySettings = [
            "method" => "POST",
            "url" => "{$url}/opt/in/QueryRests",
            "file" => [
                'field_name' => 'xml_file',
                'data' => $data
            ]
        ];
        $requestResponse = $this->sendRequest($querySettings);
        $replyId = (new XmlParser())->parseEgaisQuery($requestResponse);

        (new EgaisQueryRests([
            'org_id' => $orgId,
            'reply_id' => $replyId,
            'status' => EgaisHelper::QUERY_SENT
        ]))->save();
    }

    /**
     * @param array $settings
     * @param array $request
     * @param string $queryType
     * @return string
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function sendActWriteOff(array $settings, array $request, string $queryType)
    {
        $orgId = $this->user->organization_id;
        $numberAct = EgaisWriteOff::find()
            ->select(['act_number'])
            ->where((['org_id' => $orgId]))
            ->orderBy(['act_number' => SORT_DESC])
            ->one();
        $date = date('Y-m-d');

        $request['date'] = $date;
        $request['number'] = !empty($numberAct) ? ++$numberAct->act_number : 101;

        $typeWriteOff = EgaisTypeWriteOff::findOne(['type' => $request['type_write_off']]);

        $newAct = new EgaisWriteOff([
            'org_id' => $orgId,
            'identity' => $request['identity'],
            'act_number' => $request['number'],
            'act_date' => $request['date'],
            'type_write_off' => $typeWriteOff->id,
            'note' => $request['note'],
            'status' => null,
        ]);

        if (!$newAct->save()) {
            throw new BadRequestHttpException('Could not save to database, check your xml document!');
        }

        $xmlFile = EgaisXmlFiles::actWriteOffV3($settings['fsrar_id'], $request);

        return $this->sendEgaisQuery($settings['egais_url'], $xmlFile, $queryType);
    }


    /**
     * @param array $settings
     * @param array $request
     * @param string $queryType
     * @return bool
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function sendActWriteOn(array $settings, array $request, string $queryType)
    {
        $orgId = $this->user->organization_id;
        $numberAct = EgaisActWriteOn::find()
            ->select(['number'])
            ->where((['org_id' => $orgId]))
            ->orderBy(['number' => SORT_DESC])
            ->one();
        $date = date('Y-m-d');

        $request['date'] = $date;
        $request['number'] = !empty($numberAct) ? ++$numberAct->number : 101;

        $xmlFile = EgaisXmlFiles::actChargeOnV2($settings['fsrar_id'], $request);

        $querySettings = [
            "method" => "POST",
            "url" => "{$settings['egais_url']}/opt/in/{$queryType}",
            "file" => [
                'field_name' => 'xml_file',
                'data' => $xmlFile
            ]
        ];
        $requestResponse = $this->sendRequest($querySettings);
        $reply_id = (new XmlParser())->parseEgaisQuery($requestResponse);

        $typeWriteOn = EgaisTypeChargeOn::findOne(['type' => $request['type']]);

        if (empty($typeWriteOn)) {
            throw new BadRequestHttpException('dictionary.egais_type_document_error');
        }

        $newAct = new EgaisActWriteOn([
            'org_id' => $orgId,
            'number' => $request['number'],
            'act_date' => $request['date'],
            'type_charge_on' => $typeWriteOn->id,
            'note' => $request['note'],
            'status' => null,
            'reply_id' => $reply_id
        ]);

        if (!$newAct->save()) {
            throw new BadRequestHttpException('dictionary.request_error');
        }

        return true;
    }

    /**
     * @param $url
     * @param $data
     * @param $queryType
     * @return bool|string
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function sendEgaisQuery($url, $data, $queryType)
    {
        $querySettings = [
            "method" => "POST",
            "url" => "{$url}/opt/in/{$queryType}",
            "file" => [
                'field_name' => 'xml_file',
                'data' => $data
            ]
        ];
        $requestResponse = $this->sendRequest($querySettings);
        $replyId = (new XmlParser())->parseEgaisQuery($requestResponse);

        sleep(3);

        $querySettings = [
            "method" => "GET",
            "url" => "{$url}/opt/out?replyId={$replyId}",
        ];
        $requestResponse = $this->sendRequest($querySettings);
        $getDataDoc = (new XmlParser())->parseUrlDoc($requestResponse);

        if (!empty($getDataDoc)) {
            return $this->getOneDocument($url, $getDataDoc[0]);
        }

        return true;
    }

    // Получение всех входящих документов типа $request['type'] или без

    /**
     * @param $url
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function getAllIncomingDoc($url, $request)
    {
        $page = (isset($request['pagination']['page']) ? $request['pagination']['page'] : 1);
        $pageSize = (isset($request['pagination']['page_size']) ? $request['pagination']['page_size'] : 12);

        $type = !empty($request["type"]) ? '/' . $request["type"] : null;
        $querySettings = [
            "method" => "GET",
            "url" => "{$url}/opt/out{$type}",
        ];
        $requestResponse = $this->sendRequest($querySettings);
        $docs = (new XmlParser())->parseIncomingDocs($requestResponse);

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
            $result[] = $model;
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
     * @throws ValidationException
     */
    public function getOneDocument($url, $request)
    {
        $querySettings = [
            "method" => "GET",
            "url" => "{$url}/opt/out/{$request['type']}/{$request['id']}",
        ];
        $requestResponse = $this->sendRequest($querySettings);

        $parser = "parse{$request['type']}";

        return (new XmlParser())->$parser($requestResponse);
    }

    /**
     * @param array $request
     * @return mixed
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function sendRequest(array $request)
    {
        try {
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod($request['method'])
                ->setUrl($request['url']);

            if (!empty($request['file'])) {
                $file = $request['file'];
                $response->addFileContent($file['field_name'], $file['data']);
            }

            $response->send();

            if (!empty($response->isOk) && !$response->isOk) {
                throw new BadRequestHttpException('dictionary.request_error');
            }
        } catch (\Exception $e) {
            $this->writeInJournal(
                $e->getMessage(),
                Registry::EGAIS_SERVICE_ID,
                $this->user->organization_id,
                'ERROR'
            );
            throw new BadRequestHttpException('dictionary.connection_error_egais');
        }

        return $response->content;
    }

    /* запись в журнал в случае ошибки */
    /**
     * @param $message
     * @param $service_id
     * @param int $orgId
     * @param string $type
     * @throws ValidationException
     */
    private function writeInJournal($message, $service_id, int $orgId = 0, $type = 'success'): void
    {
        $journal = new Journal();
        $journal->response = is_array($message) ? json_encode($message) : $message;
        $journal->service_id = (int)$service_id;
        $journal->type = $type;
        $journal->log_guide = 'CreateWaybill';
        $journal->organization_id = $orgId;
        $journal->user_id = \Yii::$app instanceof \Yii\web\Application ? $this->user->id : null;
        $journal->operation_code = (string)(Registry::$operation_code_send_waybill[$service_id] ?? 0);

        if (!$journal->save()) {
            throw new ValidationException($journal->getFirstErrors());
        }
    }
}