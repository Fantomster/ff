<?php

namespace api_web\modules\integration\modules\egais\helpers;

use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\modules\egais\classes\EgaisXmlFiles;
use api_web\modules\integration\modules\egais\classes\EgaisXmlParser;
use common\models\AllServiceOperation;
use common\models\egais\EgaisActWriteOn;
use common\models\egais\EgaisActWriteOnDetail;
use common\models\egais\EgaisProductOnBalance;
use common\models\egais\EgaisQueryRests;
use common\models\egais\EgaisRequestResponse;
use common\models\egais\EgaisTypeChargeOn;
use common\models\egais\EgaisTypeWriteOff;
use common\models\egais\EgaisWriteOff;
use common\models\IntegrationSettingValue;
use common\models\Journal;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\web\BadRequestHttpException;

/* Класс для работы с ЕГАИС */

class EgaisHelper extends WebApi
{
    /** Коды операций в ЕГАИС */
    const REQUEST_QUERY_RESTS = 1; // Запрос остатков
    const REQUEST_ACT_WRITE_ON = 2; // Акт постановки на баланс
    const REQUEST_ACT_WRITE_OFF = 3; // Акт списания
    const REQUEST_GET_ALL_INCOMING_DOC = 4; // Получение всех входящих документов
    const REQUEST_GET_ONE_INCOMING_DOC = 5; // Получение одного входящего документа
    const REQUEST_QUERY_BY_TYPE_DOC = 6; // Отправка запроса в утм по типу
    const REQUEST_GET_URL_DOC = 7; // Запрос url документа
    const PARSE_ONE_INCOMING_DOC = 8; // Парсинг входящего документа
    const PARSE_GET_URL = 9; // Парсинг для получения url
    const PARSE_REPLY_ID = 10; // Парсинг для получения reply_id
    const SAVE_QUERY_RESTS_IN_BD = 11; // Сохранение QueryRests в базу
    const SAVE_ACT_WRITE_ON_IN_BD = 12; // Сохранение ActWriteOn в базу
    const SAVE_ACT_WRITE_OFF_IN_BD = 13; // Сохранение ActWriteOff в базу
    const SAVE_TICKET_AND_ACT = 14; // Сохранение Ticket и Акта в базу
    const SAVE_PRODUCT_AND_ACT = 15; // Сохранение Продукта и Акта в базу
    const SAVE_INVENTORY = 16; // Сохранение Inventory в базу
    const UNKNOWN_CHARGE_ON_TYPE = 17; // Неизвестный тип ChargeOn
    const UNKNOWN_TYPE_WRITE_OFF = 18; // Неизвестный тип TypeWriteOff

    /* Типы входящих документов */
    static $type_document = [
        'TICKET',
        'REPLYRESTS',
        'INVENTORYREGINFO',
        //'WAYBILL_V2',
        //'FORMF2REGINFO',
        //'TTNHISTORYF2REG'
    ];

    /* Статусы запросов в ЕГАИС */
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

    /**
     * @param $orgId
     * @param $url
     * @param $data
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function sendQueryRests($orgId, $url, $data): void
    {
        /* Запрос на получение остатка продуктов */
        $requestResponse = $this->sendRequest([
            "method" => "POST",
            "url" => "{$url}/opt/in/QueryRests",
            "file" => [
                'field_name' => 'xml_file',
                'data' => $data
            ],
            "operation_code" => self::REQUEST_QUERY_RESTS
        ]);

        /* reply_id идентификатор документа */
        $replyId = (new EgaisXmlParser())->getReplyId($requestResponse);
        if (empty($replyId)) {
            $this->writeInJournal([
                'message' => 'Response parsing null',
                'code' => self::PARSE_REPLY_ID
            ]);
            throw new BadRequestHttpException('dictionary.parse_error_egais');
        }

        /* Запись акта в базу */
        $newAct = new EgaisQueryRests([
            'org_id' => $orgId,
            'reply_id' => $replyId,
            'status' => EgaisHelper::QUERY_SENT
        ]);
        if (!$newAct->save()) {
            $this->writeInJournal([
                'message' => 'Not saved',
                'code' => self::SAVE_QUERY_RESTS_IN_BD
            ]);
            throw new BadRequestHttpException('dictionary.save_act_error_egais');
        }
    }

    /**
     * @param array $settings
     * @param array $request
     * @return bool
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function sendActWriteOn(array $settings, array $request)
    {
        /* Параметры orgId, number, date для xml документа */
        $orgId = $this->user->organization_id;
        $numberAct = EgaisActWriteOn::find()
            ->select(['number'])
            ->where((['org_id' => $orgId]))
            ->orderBy(['number' => SORT_DESC])
            ->one();
        $date = date('Y-m-d');
        $request['date'] = $date;
        $request['number'] = !empty($numberAct) ? ++$numberAct->number : 101;

        /* Заполненый xml документ */
        $xmlFile = EgaisXmlFiles::actChargeOnV2($settings['fsrar_id'], $request);

        /* Запрос на постановку продуктов на баланс */
        $requestResponse = $this->sendRequest([
            "method" => "POST",
            "url" => "{$settings['egais_url']}/opt/in/ActChargeOn_v2",
            "file" => [
                'field_name' => 'xml_file',
                'data' => $xmlFile
            ],
            "operation_code" => self::REQUEST_ACT_WRITE_ON
        ]);

        /* reply_id идентификатор документа */
        $reply_id = (new EgaisXmlParser())->getReplyId($requestResponse);
        if (empty($replyId)) {
            $this->writeInJournal([
                'message' => 'Response parsing null',
                'code' => self::PARSE_REPLY_ID
            ]);
            throw new BadRequestHttpException('dictionary.parse_error_egais');
        }

        /* ID типа документа по названию */
        $typeWriteOn = EgaisTypeChargeOn::findOne(['type' => $request['type']]);
        if (empty($typeWriteOn)) {
            $this->writeInJournal([
                'message' => 'Unknown ChargeOnType',
                'code' => self::UNKNOWN_CHARGE_ON_TYPE
            ]);
            throw new BadRequestHttpException('dictionary.egais_type_document_error');
        }

        /* Запись акта в базу */
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
            $this->writeInJournal([
                'message' => 'Not saved',
                'code' => self::SAVE_ACT_WRITE_ON_IN_BD
            ]);
            throw new BadRequestHttpException('dictionary.save_act_error_egais');
        }

        return true;
    }

    /* Запрос на списание продуктов с баланса */
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
        /* Параметры orgId, number, date для xml документа */
        $orgId = $this->user->organization_id;
        $numberAct = EgaisWriteOff::find()
            ->select(['act_number'])
            ->where((['org_id' => $orgId]))
            ->orderBy(['act_number' => SORT_DESC])
            ->one();
        $date = date('Y-m-d');
        $request['date'] = $date;
        $request['number'] = !empty($numberAct) ? ++$numberAct->act_number : 101;

        /* ID типа документа по названию */
        $typeWriteOff = EgaisTypeWriteOff::findOne(['type' => $request['type_write_off']]);
        if (empty($typeWriteOff)) {
            $this->writeInJournal([
                'message' => 'Unknown type WriteOff',
                'code' => self::UNKNOWN_TYPE_WRITE_OFF
            ]);
            throw new BadRequestHttpException('dictionary.egais_type_document_error');
        }

        /* Запись акта в базу */
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
            $this->writeInJournal([
                'message' => 'Not saved',
                'code' => self::SAVE_ACT_WRITE_OFF_IN_BD
            ]);
            throw new BadRequestHttpException('dictionary.save_act_error_egais');
        }

        /* Заполненый xml документ */
        $xmlFile = EgaisXmlFiles::actWriteOffV3($settings['fsrar_id'], $request);

        return $this->sendQueryByTypeDoc($settings['egais_url'], $xmlFile, $queryType);
    }

    /**
     * @param $url
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function getAllIncomingDoc($url, $request)
    {
        /* Пагинация */
        $page = (isset($request['pagination']['page']) ? $request['pagination']['page'] : 1);
        $pageSize = (isset($request['pagination']['page_size']) ? $request['pagination']['page_size'] : 12);

        /* Тип документа */
        $type = !empty($request["type"]) ? '/' . $request["type"] : null;

        /* Запрос на получение всех входящих документов */
        $requestResponse = $this->sendRequest([
            "method" => "GET",
            "url" => "{$url}/opt/out{$type}",
            "operation_code" => self::REQUEST_GET_ALL_INCOMING_DOC
        ]);

        /* Парсинг входящих документов */
        $docs = (new EgaisXmlParser())->parseIncomingDocs($requestResponse);

        /* Пагинация */
        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);

        /* Фаорматирование данных */
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
    public function getOneIncomingDoc($url, $request)
    {
        if (empty($request)) {
            $this->writeInJournal([
                'message' => 'Empty Document',
                'code' => self::PARSE_GET_URL
            ]);
            throw new BadRequestHttpException('dictionary.parse_error_egais');
        }
        /* Запрос на получение входящего документа */
        $requestResponse = $this->sendRequest([
            "method" => "GET",
            "url" => "{$url}/opt/out/{$request['type']}/{$request['id']}",
            "operation_code" => self::REQUEST_GET_ONE_INCOMING_DOC
        ]);

        $parser = "parse{$request['type']}";

        /* Парсинг документа по его типу */
        try {
            $result = (new EgaisXmlParser())->$parser($requestResponse);
        } catch (\Exception $e) {
            $this->writeInJournal([
                'message' => $e->getMessage(),
                'code' => self::PARSE_ONE_INCOMING_DOC
            ]);
            throw new BadRequestHttpException('dictionary.parse_error_egais');
        }

        return $result;
    }

    /**
     * @param $url
     * @param $data
     * @param $queryType
     * @return bool|string
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function sendQueryByTypeDoc($url, $data, $queryType)
    {
        /* Запрос в УТМ в зависимости от типа документа */
        $requestResponse = $this->sendRequest([
            "method" => "POST",
            "url" => "{$url}/opt/in/{$queryType}",
            "file" => [
                'field_name' => 'xml_file',
                'data' => $data
            ],
            "operation_code" => self::REQUEST_QUERY_BY_TYPE_DOC
        ]);

        /* reply_id идентификатор документа */
        $replyId = (new EgaisXmlParser())->getReplyId($requestResponse);
        if (empty($replyId)) {
            $this->writeInJournal([
                'message' => 'Response parsing null',
                'code' => self::PARSE_REPLY_ID
            ]);
            throw new BadRequestHttpException('dictionary.parse_error_egais');
        }

        sleep(3);

        /* Запрос на получение url документа */
        $requestResponse = $this->sendRequest([
            "method" => "GET",
            "url" => "{$url}/opt/out?replyId={$replyId}",
            "operation_code" => self::REQUEST_GET_URL_DOC
        ]);

        /* Получение тикета о ошибке если он есть иначе все верно */
        $getDataDoc = (new EgaisXmlParser())->getUrlDoc($requestResponse);
        if (!empty($getDataDoc)) {
            return $this->getOneIncomingDoc($url, $getDataDoc[0]);
        }

        return true;
    }

    /* Работа с cron */

    /**
     * Проверка на наличие тикетов и успешной постановки на баланс
     * @throws ValidationException
     */
    public function checkActWriteOn()
    {
        /* Все новые акты о потановке на баланс */
        $acts = EgaisActWriteOn::find()
            ->where(['status' => null])
            ->all();

        $transaction = \Yii::$app->db_api->beginTransaction();
        foreach ($acts as $act) {
            /* Настройки ЕГАИС организации */
            $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $act->org_id);
            $egaisUrl = $settings['egais_url'];

            try {
                /* Получение ссылок на документы о постановке на баланс */
                $requestResponse = $this->sendRequest([
                    "method" => "GET",
                    "url" => "{$egaisUrl}/opt/out?replyId={$act->reply_id}",
                    "operation_code" => self::REQUEST_GET_URL_DOC
                ]);
                $urlDocs = (new EgaisXmlParser())->getUrlDoc($requestResponse);

                /* Проверка типа полученных документов и запись их в базу */
                $this->checkTypeAndSaveDoc($act, $egaisUrl, $urlDocs);
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                $this->writeInJournal([
                    'message' => $e->getMessage(),
                    'code' => self::REQUEST_ACT_WRITE_ON
                ]);
                continue;
            }

        }
    }

    /**
     * Проверка типа полученных документов и запись их в базу
     * @param EgaisActWriteOn $act
     * @param string $egais_url
     * @param array $urlDocs
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function checkTypeAndSaveDoc(EgaisActWriteOn $act, string $egais_url, array $urlDocs): void
    {
        if (empty($urlDocs)) {
            $this->writeInJournal([
                'message' => 'Empty Documents',
                'code' => self::PARSE_GET_URL
            ]);
            throw new BadRequestHttpException('dictionary.parse_error_egais');
        }

        if (count($urlDocs) == 1) {
            $doc = $this->getOneIncomingDoc($egais_url, $urlDocs[0]);
            /** @var array $doc */
            $this->saveTicket($act, $doc, $urlDocs[0]);
        } elseif (count($urlDocs) > 1) {
            foreach ($urlDocs as $idAndTypeDoc) {
                $doc = $this->getOneIncomingDoc($egais_url, $idAndTypeDoc);
                if ($idAndTypeDoc['type'] == 'Ticket') {
                    /** @var array $doc */
                    $this->saveTicket($act, $doc, $idAndTypeDoc);
                } elseif ($idAndTypeDoc['type'] == 'INVENTORYREGINFO') {
                    /** @var array $doc */
                    $this->saveInventory($act, $doc);
                }
            }
        }
    }

    /**
     * Сохранение результата тикета
     * @param EgaisActWriteOn $act
     * @param array $doc
     * @param array $docIdAndType
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function saveTicket(EgaisActWriteOn $act, array $doc, array $docIdAndType): void
    {
        $existsResponse = EgaisRequestResponse::find()
            ->where([
                'org_id' => $act->org_id,
                'act_id' => $act->id,
                'doc_id' => $docIdAndType['id']
            ])
            ->exists();

        if (!$existsResponse) {
            $egaisRequestResponse = new EgaisRequestResponse([
                'org_id' => $act->org_id,
                'act_id' => $act->id,
                'doc_id' => $docIdAndType['id'],
                'doc_type' => 'ActWriteOn',
                'result' => !empty($doc['Result'])
                    ? (string)$doc['Result']->Conclusion
                    : (string)$doc['OperationResult']->OperationResult,
                'date' => !empty($doc['Result'])
                    ? (string)$doc['Result']->ConclusionDate
                    : (string)$doc['OperationResult']->OperationDate,
                'comment' => !empty($doc['Result'])
                    ? (string)$doc['Result']->Comments
                    : (string)$doc['OperationResult']->OperationComment,
                'operation_name' => !empty($doc['OperationResult'])
                    ? (string)$doc['OperationResult']->OperationName
                    : null,
            ]);
            $act->status = !empty($doc['Result'])
                ? (string)$doc['Result']->Conclusion
                : (string)$doc['OperationResult']->OperationResult;

            try {
                $egaisRequestResponse->save();
                $act->save();
            } catch (\Exception $e) {
                $this->writeInJournal([
                    'message' => 'Not saved Ticket or Act',
                    'code' => self::SAVE_TICKET_AND_ACT
                ]);
                throw new BadRequestHttpException('dictionary.save_ticket_and_act_error_egais');
            }
        }
    }

    /**
     * Сохранение результата инвентаризации
     * @param EgaisActWriteOn $act
     * @param array $doc
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function saveInventory(EgaisActWriteOn $act, array $doc)
    {
        $transaction = \Yii::$app->db_api->beginTransaction();
        foreach ($doc['positions'] as $position) {
            $detail = new EgaisActWriteOnDetail([
                'org_id' => $act->org_id,
                'act_write_on_id' => $act->id,
                'act_reg_id' => $doc['ActRegId'],
                'number' => $doc['Number'],
                'identity' => $position['Identity'],
                'in_form_f1_reg_id' => $position['InformF1RegId'],
                'f2_reg_id' => $position['InformF2']['F2RegId'],
                'status' => 'Accepted'
            ]);

            if (!$detail->save()) {
                $transaction->rollBack();
                $this->writeInJournal([
                    'message' => 'Not saved Inventory',
                    'code' => self::SAVE_INVENTORY
                ]);
                throw new BadRequestHttpException('Ошибка');
            }
        }
        $transaction->commit();
    }

    /**
     * Проверка документов о запросе продуктов на балансе
     * @throws ValidationException
     */
    public function saveGoodsOnBalance(): void
    {
        /* Все акты о запросе баланса */
        $queryRests = EgaisQueryRests::find()
            ->where(['status' => self::QUERY_SENT])
            ->all();

        $transaction = \Yii::$app->db_api->beginTransaction();
        foreach ($queryRests as $queryRest) {
            /* Настройки ЕГАИС организации */
            $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $queryRest->org_id);
            $egaisUrl = $settings['egais_url'];

            try {
                /* Получение ссылок на документы о постановке на баланс */
                $requestResponse = $this->sendRequest([
                    "method" => "GET",
                    "url" => "{$egaisUrl}/opt/out?replyId={$queryRest->reply_id}",
                    "operation_code" => self::REQUEST_GET_URL_DOC
                ]);
                $urlDocs = (new EgaisXmlParser())->getUrlDoc($requestResponse);

                /* Сохранение продуктов которые находятся у организации на балансе */
                $this->saveProductOnBalance($queryRest, $egaisUrl, $urlDocs);
                $transaction->commit();
            } catch (\Exception $e) {
                $this->writeInJournal([
                    'message' => $e->getMessage(),
                    'code' => self::REQUEST_QUERY_RESTS
                ]);
                $transaction->rollBack();
                continue;
            }
        }
    }

    /**
     * Сохранение продуктов
     * @param EgaisQueryRests $queryRest
     * @param string $url
     * @param array $urlDocs
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function saveProductOnBalance(EgaisQueryRests $queryRest, string $url, array $urlDocs): void
    {
        /** @var array $doc Парсинг документа со списком продуктов */
        $doc = $this->getOneIncomingDoc($url, $urlDocs[0]);

        /* Проверка на наличие и запись в базу продуктов */
        $products = $doc["Products"]["StockPosition"];
        foreach ($products as $product) {
            $egaisProduct = EgaisProductOnBalance::find()
                ->where([
                    'org_id' => $queryRest->org_id,
                    'inform_a_reg_id' => $product['InformARegId'],
                    'inform_b_reg_id' => $product['InformBRegId']
                ])
                ->one();

            if (empty($egaisProduct)) {
                $egaisProduct = new EgaisProductOnBalance();
            }

            $egaisProduct->setAttributes([
                'org_id' => $queryRest->org_id,
                'quantity' => $product['Quantity'],
                'alc_code' => $product['Product']['AlcCode'],
                'inform_a_reg_id' => $product['InformARegId'],
                'inform_b_reg_id' => $product['InformBRegId'],
                'capacity' => $product['Product']['Capacity'],
                'full_name' => $product['Product']['FullName'],
                'alc_volume' => $product['Product']['AlcVolume'],
                'product_v_code' => $product['Product']['ProductVCode'],
                'producer_inn' => (string)$product['Product']['Producer']->INN,
                'producer_kpp' => (string)$product['Product']['Producer']->KPP,
                'producer_full_name' => (string)$product['Product']['Producer']->FullName,
                'producer_short_name' => (string)$product['Product']['Producer']->ShortName,
                'address_country' => (string)$product['Product']['Producer']->address->Country,
                'producer_client_reg_id' => (string)$product['Product']['Producer']->ClientRegId,
                'address_region_code' => (string)$product['Product']['Producer']->address->RegionCode,
                'address_description' => (string)$product['Product']['Producer']->address->description,
            ]);
            $queryRest->status = EgaisHelper::QUERY_PROCESSED;

            try {
                $egaisProduct->save();
                $queryRest->save();
            } catch (\Exception $e) {
                $this->writeInJournal([
                    'message' => 'Not saved Product or Act',
                    'code' => self::SAVE_PRODUCT_AND_ACT
                ]);
                throw new BadRequestHttpException('dictionary.save_product_and_act_error_egais');
            }
        }
    }

    /**
     * Отправка запросов
     * @param array $request
     * @return mixed
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function sendRequest(array $request)
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
            $this->writeInJournal([
                'message' => $e->getMessage(),
                'code' => $request['operation_code']
            ]);
            throw new BadRequestHttpException('dictionary.connection_error_egais');
        }

        return $response->content;
    }

    /**
     * Запись в журнал в случае ошибки
     * @param array $data
     * @throws ValidationException
     */
    private function writeInJournal(array $data): void
    {
        $operation = AllServiceOperation::findOne([
            'service_id' => Registry::EGAIS_SERVICE_ID,
            'code' => $data['code']
        ]);
        $journal = new Journal([
            'response' => is_array($data['message']) ? Json::encode($data['message']) : $data['message'],
            'service_id' => $operation->service_id,
            'type' => $operation->denom,
            'log_guide' => $operation->comment,
            'organization_id' => $this->user->organization_id,
            'user_id' => \Yii::$app instanceof \Yii\web\Application ? $this->user->id : null,
            'operation_code' => (string)$operation->code
        ]);

        if (!$journal->save()) {
            throw new ValidationException($journal->getFirstErrors());
        }
    }
}