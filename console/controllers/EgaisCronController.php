<?php

namespace console\controllers;

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\modules\egais\classes\XmlParser;
use api_web\modules\integration\modules\egais\helpers\EgaisHelper;
use common\models\egais\EgaisActWriteOn;
use common\models\egais\EgaisActWriteOnDetail;
use common\models\egais\EgaisRequestResponse;
use common\models\IntegrationSettingValue;
use common\models\Journal;
use yii\console\Controller;
use yii\httpclient\Client;
use yii\web\BadRequestHttpException;

class EgaisCronController extends Controller
{
    // Проверка на наличие тикетов и успешной постановки на баланс

    /**
     * @throws ValidationException
     */
    public function actionCheckTicketActWriteOn()
    {
        $acts = EgaisActWriteOn::find()
            ->where(['status' => null])
            ->all(\Yii::$app->db_api);

        $transaction = \Yii::$app->db_api->beginTransaction();
        foreach ($acts as $act) {
            $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $act->org_id);

            try {
                $idAndTypeDocs = $this->getIdAndTypeDocs($settings['egais_url'], $act->reply_id);
                print_r($idAndTypeDocs);
                $this->checkingResult($act, $settings['egais_url'], $idAndTypeDocs);
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                $this->writeInJournal(
                    'Error operation getting ticket',
                    Registry::EGAIS_SERVICE_ID,
                    $act->org_id,
                    'ERROR'
                );
                continue;
            }

        }
    }

    /**
     * @param $url
     * @param $reply_id
     * @return array|bool
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    // Получение типа и id документа из ссылки
    private function getIdAndTypeDocs(string $url, string $reply_id): array
    {
        $client = new Client();
        $tickets = $client->createRequest()
            ->setMethod('get')
            ->setUrl("{$url}/opt/out?replyId={$reply_id}")
            ->send();

        $urlDoc = (new XmlParser())->parseUrlDoc($tickets->content);

        if (empty($urlDoc)) {
            throw new BadRequestHttpException();
        }

        return $urlDoc;
    }


    /**
     * @param EgaisActWriteOn $act
     * @param string $egais_url
     * @param array $idAndTypeDocs
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    private function checkingResult(EgaisActWriteOn $act, string $egais_url, array $idAndTypeDocs): void
    {
        if (count($idAndTypeDocs) == 1) {
            $doc = EgaisHelper::getOneDocument($egais_url, $idAndTypeDocs);
            /** @var array $doc */
            $this->saveTicket($act, $doc, $idAndTypeDocs);
        } else {
            foreach ($idAndTypeDocs as $idAndTypeDoc) {
                $doc = EgaisHelper::getOneDocument($egais_url, $idAndTypeDoc);
                if ($idAndTypeDoc['type'] == 'Ticket') {
                    /** @var array $doc */
                    $this->saveTicket($act, $doc, $idAndTypeDoc);
                } elseif ($idAndTypeDoc['type'] == 'INVENTORYREGINFO') {
                    // Делаю запрос Парсим ответ и записываем результат детальной страницы
                    /** @var array $doc */
                    $this->saveInventory($act, $doc);
                }
            }
        }
    }

    // Сохранение результата тикета

    /**
     * @param EgaisActWriteOn $act
     * @param array $doc
     * @param array $idAndTypeDoc
     */
    private function saveTicket(EgaisActWriteOn $act, array $doc, array $idAndTypeDoc): void
    {
        $existsResponse = EgaisRequestResponse::find()
            ->where([
                'org_id' => $act->org_id,
                'act_id' => $act->id,
                'doc_id' => $idAndTypeDoc['id']
            ])
            ->exists();

        if (!$existsResponse) {
            (new EgaisRequestResponse([
                'org_id' => $act->org_id,
                'act_id' => $act->id,
                'doc_id' => $idAndTypeDoc['id'],
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
            ]))->save();

            $act->status = !empty($doc['Result'])
                ? (string)$doc['Result']->Conclusion
                : (string)$doc['OperationResult']->OperationResult;
            $act->save();
        }
    }

    // Сохранение результата инвентаризации

    /**
     * @param EgaisActWriteOn $act
     * @param array $doc
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
            }
        }
        $transaction->commit();
    }

    // запись в журнал в случае ошибки

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