<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-18
 * Time: 10:44
 */

namespace api_web\modules\integration\modules\egais\models;

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\modules\egais\classes\EgaisXmlParser;
use api_web\modules\integration\modules\egais\helpers\EgaisHelper;
use common\models\AllServiceOperation;
use common\models\egais\EgaisActWriteOn;
use common\models\egais\EgaisActWriteOnDetail;
use common\models\egais\EgaisProductOnBalance;
use common\models\egais\EgaisQueryRests;
use common\models\egais\EgaisRequestResponse;
use common\models\IntegrationSettingValue;
use common\models\Journal;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\web\BadRequestHttpException;

class EgaisCronHelper
{
    /**
     * Проверка на наличие тикетов и успешной постановки на баланс
     *
     * @throws ValidationException
     */
    public function checkActWriteOn()
    {
        /* Все новые акты о потановке на баланс */
        $acts = EgaisActWriteOn::find()
            ->where(["status" => null])
            ->all();

        $transaction = \Yii::$app->db_api->beginTransaction();
        /** @var EgaisActWriteOn $act */
        foreach ($acts as $act) {
            /* Настройки ЕГАИС организации */
            $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $act->org_id);
            $egaisUrl = $settings["egais_url"];

            try {
                /* Получение ссылок на документы о постановке на баланс */
                $requestResponse = EgaisCronHelper::sendRequest([
                    "method"         => "GET",
                    "url"            => "{$egaisUrl}/opt/out?replyId={$act->reply_id}",
                    "operation_code" => EgaisHelper::REQUEST_GET_URL_DOC,
                    "org_id"         => $act->org_id
                ]);
                $urlDocs = (new EgaisXmlParser())->getUrlDoc($requestResponse);
                print_r($urlDocs);
                /* Проверка типа полученных документов и запись их в базу */
                $this->checkTypeAndSaveDoc($act, $egaisUrl, $urlDocs);
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                $this->writeInJournal([
                    "message" => $e->getMessage(),
                    "code"    => EgaisHelper::REQUEST_ACT_WRITE_ON,
                    "org_id"  => $act->org_id
                ]);
                continue;
            }

        }
    }

    /**
     * Проверка типа полученных документов и запись их в базу
     *
     * @param EgaisActWriteOn $act
     * @param string          $egais_url
     * @param array           $urlDocs
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function checkTypeAndSaveDoc(EgaisActWriteOn $act, string $egais_url, array $urlDocs): void
    {
        if (empty($urlDocs)) {
            $this->writeInJournal([
                "message" => "Empty Documents",
                "code"    => EgaisHelper::PARSE_GET_URL,
                "org_id"  => $act->org_id
            ]);
            throw new BadRequestHttpException("dictionary.parse_error_egais");
        }

        if (count($urlDocs) == 1) {
            $doc = $this->getOneIncomingDoc($egais_url, current($urlDocs), $act->org_id);
            /** @var array $doc */
            $this->saveTicket($act, $doc, current($urlDocs));
        } elseif (count($urlDocs) > 1) {
            foreach ($urlDocs as $idAndTypeDoc) {
                $doc = $this->getOneIncomingDoc($egais_url, $idAndTypeDoc, $act->org_id);
                if ($idAndTypeDoc["type"] == "Ticket") {
                    /** @var array $doc */
                    $this->saveTicket($act, $doc, $idAndTypeDoc);
                } elseif ($idAndTypeDoc["type"] == "INVENTORYREGINFO") {
                    /** @var array $doc */
                    $this->saveInventory($act, $doc);
                }
            }
        }
    }

    /**
     * Сохранение результата тикета
     *
     * @param EgaisActWriteOn $act
     * @param array           $doc
     * @param array           $docIdAndType
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function saveTicket(EgaisActWriteOn $act, array $doc, array $docIdAndType): void
    {
        $existsResponse = EgaisRequestResponse::find()
            ->where([
                "org_id" => $act->org_id,
                "act_id" => $act->id,
                "doc_id" => $docIdAndType["id"]
            ])
            ->exists();

        if (!$existsResponse) {
            $egaisRequestResponse = new EgaisRequestResponse([
                "org_id"         => $act->org_id,
                "act_id"         => $act->id,
                "doc_id"         => $docIdAndType["id"],
                "doc_type"       => "ActWriteOn",
                "result"         => !empty($doc["Result"])
                    ? (string)$doc["Result"]->Conclusion
                    : (string)$doc["OperationResult"]->OperationResult,
                "date"           => !empty($doc["Result"])
                    ? (string)$doc["Result"]->ConclusionDate
                    : (string)$doc["OperationResult"]->OperationDate,
                "comment"        => !empty($doc["Result"])
                    ? (string)$doc["Result"]->Comments
                    : (string)$doc["OperationResult"]->OperationComment,
                "operation_name" => !empty($doc["OperationResult"])
                    ? (string)$doc["OperationResult"]->OperationName
                    : null,
            ]);
            $act->status = !empty($doc["Result"])
                ? (string)$doc["Result"]->Conclusion
                : (string)$doc["OperationResult"]->OperationResult;

            try {
                $egaisRequestResponse->save();
                $act->save();
            } catch (\Exception $e) {
                $this->writeInJournal([
                    "message" => "Not saved Ticket or Act",
                    "code"    => EgaisHelper::SAVE_TICKET_AND_ACT,
                    "org_id"  => $act->org_id
                ]);
                throw new BadRequestHttpException("dictionary.save_ticket_and_act_error_egais");
            }
        }
    }

    /**
     * Сохранение результата инвентаризации
     *
     * @param EgaisActWriteOn $act
     * @param array           $doc
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function saveInventory(EgaisActWriteOn $act, array $doc)
    {
        $isSave = true;
        $transaction = \Yii::$app->db_api->beginTransaction();
        foreach ($doc["positions"] as $position) {
            $detail = new EgaisActWriteOnDetail([
                "org_id"            => $act->org_id,
                "act_write_on_id"   => $act->id,
                "act_reg_id"        => $doc["ActRegId"],
                "number"            => $doc["Number"],
                "identity"          => $position["Identity"],
                "in_form_f1_reg_id" => $position["InformF1RegId"],
                "f2_reg_id"         => $position["InformF2"]["F2RegId"],
                "status"            => "Accepted"
            ]);

            if (!$detail->save()) {
                $isSave = false;
                $this->writeInJournal([
                    "message" => "Not saved Inventory",
                    "code"    => EgaisHelper::SAVE_INVENTORY,
                    "org_id"  => $act->org_id
                ]);
                break;
            }
        }

        if ($isSave) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }
    }

    /**
     * Проверка документов о запросе продуктов на балансе
     *
     * @throws ValidationException
     */
    public function saveGoodsOnBalance(): void
    {
        /**
         * Все акты о запросе баланса
         *
         * @var EgaisQueryRests $queryRests
         */
        $queryRests = EgaisQueryRests::find()
            ->where(["status" => EgaisHelper::QUERY_SENT])
            ->all();

        $transaction = \Yii::$app->db_api->beginTransaction();
        /** @var EgaisQueryRests $queryRest */
        foreach ($queryRests as $queryRest) {
            /* Настройки ЕГАИС организации */
            $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $queryRest->org_id);
            $egaisUrl = $settings["egais_url"];

            try {
                /* Получение ссылок на документы о постановке на баланс */
                $requestResponse = $this->sendRequest([
                    "method"         => "GET",
                    "url"            => "{$egaisUrl}/opt/out?replyId={$queryRest->reply_id}",
                    "operation_code" => EgaisHelper::REQUEST_GET_URL_DOC,
                    "org_id"         => $queryRest->org_id
                ]);
                $urlDocs = (new EgaisXmlParser())->getUrlDoc($requestResponse);

                /* Сохранение продуктов которые находятся у организации на балансе */
                $this->saveProductOnBalance($queryRest, $egaisUrl, $urlDocs);
                $transaction->commit();
            } catch (\Exception $e) {
                $this->writeInJournal([
                    "message" => $e->getMessage(),
                    "code"    => EgaisHelper::REQUEST_QUERY_RESTS,
                    "org_id"  => $queryRest->org_id
                ]);
                $transaction->rollBack();
                continue;
            }
        }
    }

    /**
     * Сохранение продуктов
     *
     * @param EgaisQueryRests $queryRest
     * @param string          $url
     * @param array           $urlDocs
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function saveProductOnBalance(EgaisQueryRests $queryRest, string $url, array $urlDocs): void
    {
        /** @var array $doc Парсинг документа со списком продуктов */
        $doc = $this->getOneIncomingDoc($url, current($urlDocs), $queryRest->org_id);

        /* Проверка на наличие и запись в базу продуктов */
        $products = $doc["Products"]["StockPosition"];
        foreach ($products as $product) {
            $egaisProduct = EgaisProductOnBalance::find()
                ->where([
                    "org_id"          => $queryRest->org_id,
                    "inform_a_reg_id" => $product["InformARegId"],
                    "inform_b_reg_id" => $product["InformBRegId"]
                ])
                ->one();

            if (empty($egaisProduct)) {
                $egaisProduct = new EgaisProductOnBalance();
            }

            $egaisProduct->setAttributes([
                "org_id"                 => $queryRest->org_id,
                "quantity"               => $product["Quantity"],
                "alc_code"               => $product["Product"]["AlcCode"],
                "inform_a_reg_id"        => $product["InformARegId"],
                "inform_b_reg_id"        => $product["InformBRegId"],
                "capacity"               => $product["Product"]["Capacity"],
                "full_name"              => $product["Product"]["FullName"],
                "alc_volume"             => $product["Product"]["AlcVolume"],
                "product_v_code"         => $product["Product"]["ProductVCode"],
                "producer_inn"           => (string)$product["Product"]["Producer"]->INN,
                "producer_kpp"           => (string)$product["Product"]["Producer"]->KPP,
                "producer_full_name"     => (string)$product["Product"]["Producer"]->FullName,
                "producer_short_name"    => (string)$product["Product"]["Producer"]->ShortName,
                "address_country"        => (string)$product["Product"]["Producer"]->address->Country,
                "producer_client_reg_id" => (string)$product["Product"]["Producer"]->ClientRegId,
                "address_region_code"    => (string)$product["Product"]["Producer"]->address->RegionCode,
                "address_description"    => (string)$product["Product"]["Producer"]->address->description,
            ]);
            $queryRest->status = EgaisHelper::QUERY_PROCESSED;

            try {
                $egaisProduct->save();
                $queryRest->save();
            } catch (\Exception $e) {
                $this->writeInJournal([
                    "message" => "Not saved Product or Act",
                    "code"    => EgaisHelper::SAVE_PRODUCT_AND_ACT,
                    "org_id"  => $queryRest->org_id
                ]);
                throw new BadRequestHttpException("dictionary.save_product_and_act_error_egais");
            }
        }
    }

    /**
     * @param      $url
     * @param      $request
     * @param null $orgId
     * @return string
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function getOneIncomingDoc($url, $request, $orgId = null)
    {
        if (empty($request)) {
            $this->writeInJournal([
                "message" => "Empty Document",
                "code"    => EgaisHelper::PARSE_GET_URL,
                "org_id"  => $orgId
            ]);
            throw new BadRequestHttpException("dictionary.parse_error_egais");
        }
        /* Запрос на получение входящего документа */
        $requestResponse = EgaisCronHelper::sendRequest([
            "method"         => "GET",
            "url"            => "{$url}/opt/out/{$request["type"]}/{$request["id"]}",
            "operation_code" => EgaisHelper::REQUEST_GET_ONE_INCOMING_DOC,
            "org_id"         => $orgId
        ]);

        $parser = "parse{$request["type"]}";

        /* Парсинг документа по его типу */
        try {
            $result = (new EgaisXmlParser())->$parser($requestResponse);
        } catch (\Exception $e) {
            $this->writeInJournal([
                "message" => $e->getMessage(),
                "code"    => EgaisHelper::PARSE_ONE_INCOMING_DOC,
                "org_id"  => $orgId
            ]);
            throw new BadRequestHttpException("dictionary.parse_error_egais");
        }

        return $result;
    }

    /**
     * Отправка запросов
     *
     * @param array $request
     * @return mixed
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function sendRequest(array $request)
    {
        try {
            $client = new Client();
            $query = $client->createRequest()
                ->setMethod($request["method"])
                ->setUrl($request["url"]);

            if (!empty($request["file"])) {
                $file = $request["file"];
                $query->addFileContent($file["field_name"], $file["data"]);
            }

            $response = $query->send();

            if (!empty($response->isOk) && !$response->isOk) {
                throw new BadRequestHttpException("dictionary.request_error");
            }
        } catch (\Exception $e) {
            $this->writeInJournal([
                "message" => $e->getMessage(),
                "code"    => $request["operation_code"],
                "user_id" => $request["user_id"] ?? null,
                "org_id"  => $request["org_id"] ?? null
            ]);
            throw new BadRequestHttpException("dictionary.connection_error_egais");
        }

        return $response->content;
    }

    /**
     * Запись в журнал в случае ошибки
     *
     * @param array $data
     * @throws ValidationException
     */
    public function writeInJournal(array $data): void
    {
        $operation = AllServiceOperation::findOne([
            "service_id" => Registry::EGAIS_SERVICE_ID,
            "code"       => $data["code"]
        ]);
        $journal = new Journal([
            "response"        => is_array($data["message"]) ? Json::encode($data["message"]) : $data["message"],
            "service_id"      => $operation->service_id,
            "type"            => $operation->denom,
            "log_guide"       => $operation->comment,
            "organization_id" => $data["org_id"] ?? null,
            "user_id"         => $data["user_id"] ?? null,
            "operation_code"  => (string)$operation->code
        ]);

        if (!$journal->save()) {
            throw new ValidationException($journal->getFirstErrors());
        }
    }
}