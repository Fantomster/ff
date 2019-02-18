<?php

namespace api_web\modules\integration\modules\egais\helpers;

use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\modules\egais\classes\EgaisXmlFiles;
use api_web\modules\integration\modules\egais\classes\EgaisXmlParser;
use api_web\modules\integration\modules\egais\models\EgaisCronHelper;
use common\models\egais\EgaisActWriteOn;
use common\models\egais\EgaisQueryRests;
use common\models\egais\EgaisTypeChargeOn;
use common\models\egais\EgaisTypeWriteOff;
use common\models\egais\EgaisWriteOff;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
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
        self::QUERY_SENT          => 'sent',
        self::QUERY_PROCESSED     => 'processed',
        self::QUERY_NOT_PROCESSED => 'not processed',
        self::QUERY_ERROR         => 'error'
    ];

    private $cronHelper;

    public function __construct()
    {
        parent::__construct();

        $this->cronHelper = new EgaisCronHelper();
    }

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
        $requestResponse = $this->cronHelper->sendRequest([
            "method"         => "POST",
            "url"            => "{$url}/opt/in/QueryRests",
            "file"           => [
                'field_name' => 'xml_file',
                'data'       => $data
            ],
            "operation_code" => self::REQUEST_QUERY_RESTS
        ]);

        /* reply_id идентификатор документа */
        $replyId = (new EgaisXmlParser())->getReplyId($requestResponse);
        if (empty($replyId)) {
            $this->cronHelper->writeInJournal([
                'message' => 'Response parsing null',
                'code'    => self::PARSE_REPLY_ID
            ]);
            throw new BadRequestHttpException('dictionary.parse_error_egais');
        }

        /* Запись акта в базу */
        $newAct = new EgaisQueryRests([
            'org_id'   => $orgId,
            'reply_id' => $replyId,
            'status'   => EgaisHelper::QUERY_SENT
        ]);
        if (!$newAct->save()) {
            $this->cronHelper->writeInJournal([
                'message' => 'Not saved',
                'code'    => self::SAVE_QUERY_RESTS_IN_BD
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
        $requestResponse = $this->cronHelper->sendRequest([
            "method"         => "POST",
            "url"            => "{$settings['egais_url']}/opt/in/ActChargeOn_v2",
            "file"           => [
                'field_name' => 'xml_file',
                'data'       => $xmlFile
            ],
            "operation_code" => self::REQUEST_ACT_WRITE_ON
        ]);

        /* reply_id идентификатор документа */
        $replyId = (new EgaisXmlParser())->getReplyId($requestResponse);
        if (empty($replyId)) {
            $this->cronHelper->writeInJournal([
                'message' => 'Response parsing null',
                'code'    => self::PARSE_REPLY_ID
            ]);
            throw new BadRequestHttpException('dictionary.parse_error_egais');
        }

        /* ID типа документа по названию */
        $typeWriteOn = EgaisTypeChargeOn::findOne(['type' => $request['type']]);
        if (empty($typeWriteOn)) {
            $this->cronHelper->writeInJournal([
                'message' => 'Unknown ChargeOnType',
                'code'    => self::UNKNOWN_CHARGE_ON_TYPE
            ]);
            throw new BadRequestHttpException('dictionary.egais_type_document_error');
        }

        /* Запись акта в базу */
        $newAct = new EgaisActWriteOn([
            'org_id'         => $orgId,
            'number'         => $request['number'],
            'act_date'       => $request['date'],
            'type_charge_on' => $typeWriteOn->id,
            'note'           => $request['note'],
            'status'         => null,
            'reply_id'       => $replyId
        ]);

        if (!$newAct->save()) {
            $this->cronHelper->writeInJournal([
                'message' => 'Not saved',
                'code'    => self::SAVE_ACT_WRITE_ON_IN_BD
            ]);
            throw new BadRequestHttpException('dictionary.save_act_error_egais');
        }

        return true;
    }

    /* Запрос на списание продуктов с баланса */
    /**
     * @param array  $settings
     * @param array  $request
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
            $this->cronHelper->writeInJournal([
                'message' => 'Unknown type WriteOff',
                'code'    => self::UNKNOWN_TYPE_WRITE_OFF
            ]);
            throw new BadRequestHttpException('dictionary.egais_type_document_error');
        }

        /* Запись акта в базу */
        $newAct = new EgaisWriteOff([
            'org_id'         => $orgId,
            'identity'       => $request['identity'],
            'act_number'     => $request['number'],
            'act_date'       => $request['date'],
            'type_write_off' => $typeWriteOff->id,
            'note'           => $request['note'],
            'status'         => null,
        ]);

        if (!$newAct->save()) {
            $this->cronHelper->writeInJournal([
                'message' => 'Not saved',
                'code'    => self::SAVE_ACT_WRITE_OFF_IN_BD
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
        $requestResponse = $this->cronHelper->sendRequest([
            "method"         => "GET",
            "url"            => "{$url}/opt/out{$type}",
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
            'allModels'  => $docs,
            'pagination' => $pagination,
            'sort'       => [
                'attributes'   => ['id'],
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
            'document'   => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];
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
        $requestResponse = $this->cronHelper->sendRequest([
            "method"         => "POST",
            "url"            => "{$url}/opt/in/{$queryType}",
            "file"           => [
                'field_name' => 'xml_file',
                'data'       => $data
            ],
            "operation_code" => self::REQUEST_QUERY_BY_TYPE_DOC
        ]);

        /* reply_id идентификатор документа */
        $replyId = (new EgaisXmlParser())->getReplyId($requestResponse);
        if (empty($replyId)) {
            $this->cronHelper->writeInJournal([
                'message' => 'Response parsing null',
                'code'    => self::PARSE_REPLY_ID
            ]);
            throw new BadRequestHttpException('dictionary.parse_error_egais');
        }

        sleep(3);

        /* Запрос на получение url документа */
        $requestResponse = $this->cronHelper->sendRequest([
            "method"         => "GET",
            "url"            => "{$url}/opt/out?replyId={$replyId}",
            "operation_code" => self::REQUEST_GET_URL_DOC
        ]);

        /* Получение тикета о ошибке если он есть иначе все верно */
        $getDataDoc = (new EgaisXmlParser())->getUrlDoc($requestResponse);
        if (!empty($getDataDoc)) {
            return $this->cronHelper->getOneIncomingDoc($url, $getDataDoc[0]);
        }

        return true;
    }
}