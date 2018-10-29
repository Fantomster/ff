<?php

namespace api_web\classes;

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\classes\documents\EdiOrder;
use api_web\modules\integration\classes\documents\EdiOrderContent;
use api_web\modules\integration\classes\documents\Order;
use api_web\modules\integration\classes\documents\OrderContent;
use api_web\modules\integration\classes\documents\OrderContentEmail;
use api_web\modules\integration\classes\documents\OrderEmail;
use api_web\modules\integration\classes\documents\Waybill;
use api_web\modules\integration\classes\documents\WaybillContent;
use common\helpers\DBNameHelper;
use common\models\RelationUserOrganization;
use common\models\Order as OrderMC;
use yii\data\SqlDataProvider;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/**
 * Class DocumentWebApi
 *
 * @package api_web\modules\integration\classes
 */
class DocumentWebApi extends \api_web\components\WebApi
{

    const DOC_GROUP_STATUS_WAIT_SENDING = 'Ожидают выгрузки';
    const DOC_GROUP_STATUS_WAIT_FORMING = 'Ожидают формирования';
    const DOC_GROUP_STATUS_SENT = 'Выгружена';

    private static $doc_group_status = [
        1 => self::DOC_GROUP_STATUS_WAIT_SENDING,
        2 => self::DOC_GROUP_STATUS_WAIT_FORMING,
        3 => self::DOC_GROUP_STATUS_SENT,
    ];

    /**константа типа документа - заказ*/
    const TYPE_ORDER = 'order';
    /**константа типа документа - накладная*/
    const TYPE_WAYBILL = 'waybill';
    /** накладная поставщика **/
    const TYPE_ORDER_EMAIL = 'order_email';
    /** заказ из EDI */
    const TYPE_ORDER_EDI = 'order_edi';

    /**статический список типов документов*/
    public static $TYPE_LIST = [self::TYPE_ORDER, self::TYPE_WAYBILL, self::TYPE_ORDER_EMAIL, self::TYPE_ORDER_EDI];

    private static $models = [
        self::TYPE_WAYBILL     => Waybill::class,
        self::TYPE_ORDER       => Order::class,
        self::TYPE_ORDER_EMAIL => OrderEmail::class,
        self::TYPE_ORDER_EDI   => EdiOrder::class,
    ];

    private static $modelsContent = [
        self::TYPE_WAYBILL     => WaybillContent::class,
        self::TYPE_ORDER       => OrderContent::class,
        self::TYPE_ORDER_EMAIL => OrderContentEmail::class,
        self::TYPE_ORDER_EDI   => EdiOrderContent::class,
    ];

    /**
     * Метод получения шапки документа
     *
     * @param array $post
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function getHeader(array $post)
    {
        $this->validateRequest($post, ['type', 'document_id']);

        if (!in_array(strtolower($post['type']), self::$TYPE_LIST)) {
            throw new BadRequestHttpException('dont support this type');
        }

        $className = self::$models[$post['type']];
        return $className::prepareModel($post['document_id']);
    }

    /**
     * Метод получения детальной части документа
     *
     * @param array $post
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function getContent(array $post)
    {
        $this->validateRequest($post, ['type', 'document_id']);

        if (!in_array(strtolower($post['type']), self::$TYPE_LIST)) {
            throw new BadRequestHttpException('dont support this type');
        }

        $className = self::$modelsContent[$post['type']];
        return $className::prepareModel($post['document_id']);
    }

    /**
     * Получение состава документа
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getDocumentContents(array $post)
    {
        $this->validateRequest($post, ['type', 'document_id', 'service_id']);
        $hasOrderContent = null;
        if (!in_array(strtolower($post['type']), self::$TYPE_LIST)) {
            throw new BadRequestHttpException('document.not_support_type');
        }

        switch (strtolower($post['type'])) {
            case self::TYPE_ORDER :
                return $this->getDocumentOrder($post['document_id'], $post['service_id']);
                break;
            case self::TYPE_WAYBILL:
                return $this->getDocumentWaybill($post['document_id'], $post['service_id']);
                break;
            default:
                throw new BadRequestHttpException('document.not_support_type');
        }
    }

    /**
     * Возвращаем информацию по докумнту типа order
     *
     * @param      $document_id
     * @param null $service_id
     * @return array
     */
    private function getDocumentOrder($document_id, $service_id)
    {
        $apiDb = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);

        $result = [
            'documents' => [],
            'positions' => []
        ];

        $order = OrderMC::findOne((int)$document_id);
        //Документы в заказе
        if (!empty($order)) {
            $modelClass = self::$models[self::TYPE_WAYBILL];
            $waybills = $order->getWaybills($service_id);
            foreach ($this->iterator($waybills) as $waybill) {
                $result['documents'][] = $modelClass::prepareModel($waybill['id']);
            }
        }

        //Позиции заказа вне накладной
        $result['positions'] = (new Query())
            ->select([
                'order_content.id'
            ])
            ->from('order_content')
            ->leftJoin(
                $apiDb . '.' . \common\models\WaybillContent::tableName() . ' as wc',
                'wc.order_content_id = order_content.id'
            )
            ->where('wc.order_content_id is null')
            ->andWhere('order_id = :doc_id', [':doc_id' => (int)$document_id])
            ->all();

        if (!empty($result['positions'])) {
            $modelClass = self::$modelsContent[self::TYPE_ORDER];
            foreach ($this->iterator($result['positions']) as $key => $position) {
                $result['positions'][$key] = $modelClass::prepareModel($position);
            }
        }

        return $result;
    }

    /**
     * Возвращаем информацию по докумнту типа waybill
     *
     * @param      $document_id
     * @param null $service_id
     * @return array
     * @throws BadRequestHttpException
     */
    private function getDocumentWaybill($document_id, $service_id)
    {
        $result = [
            'documents' => [],
            'positions' => []
        ];

        if (\common\models\Waybill::find()->where(['id' => $document_id, 'service_id' => $service_id])->exists()) {
            $query = (new Query())
                ->select('id')
                ->from(\common\models\WaybillContent::tableName())
                ->where('waybill_id = :doc_id', [':doc_id' => (int)$document_id]);

            $positions = $query->all(\Yii::$app->db_api);

            if (!empty($positions)) {
                $modelClass = self::$modelsContent[self::TYPE_WAYBILL];
                foreach ($this->iterator($positions) as $key => $position) {
                    $result['positions'][$key] = $modelClass::prepareModel($position);
                }
            }
        }

        return $result;
    }

    /**
     * Получение списка документов
     *
     * @param array $post
     * @throws \Exception
     * @return array
     * @throws BadRequestHttpException
     */
    public function getDocumentsList(array $post)
    {
        $this->validateRequest($post, ['service_id']);
        $client = $this->user->organization;

        $sort = (isset($post['sort']) ? $post['sort'] : null);
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $documents = [];

        $params_sql = [];
        $where_all = " AND client_id  = :business_id";
        $params_sql[':service_id'] = $post['service_id'];
        if (isset($post['search']['business_id']) && !empty($post['search']['business_id'])) {
            if (RelationUserOrganization::findOne(['user_id' => $this->user->id, 'organization_id' => $post['search']['business_id']])) {
                $params_sql[':business_id'] = $post['search']['business_id'];
            } else {
                throw new BadRequestHttpException("business unavailable to current user");
            }
        } else {
            $params_sql[':business_id'] = $this->user->organization_id;
        }

        if (isset($post['search']['waybill_status']) && !empty($post['search']['waybill_status'])) {
            $where_all .= " AND waybill_status_id = :waybill_status";
            $params_sql[':waybill_status'] = $post['search']['waybill_status'];
        }

        if (isset($post['search']['doc_number']) && !empty($post['search']['doc_number'])) {
            $where_all .= " AND doc_number = :doc_number";
            $params_sql[':doc_number'] = $post['search']['doc_number'];
        }

        if (isset($post['search']['waybill_date']) && !empty($post['search']['waybill_date'])) {
            if (isset($post['search']['waybill_date']['from']) && !empty($post['search']['waybill_date']['from'])) {
                $from = self::convertDate($post['search']['waybill_date']['from']);
            }

            if (isset($post['search']['waybill_date']['to']) && !empty($post['search']['waybill_date']['to'])) {
                $to = self::convertDate($post['search']['waybill_date']['to']);
            }

            if (isset($from) && isset($to)) {
                $where_all .= " AND waybill_date BETWEEN :waybill_date_from AND :waybill_date_to";
                $params_sql[':waybill_date_from'] = $from;
                $params_sql[':waybill_date_to'] = $to;
            }

        }

        $from = null;
        $to = null;

        if (isset($post['search']['order_date']) && !empty($post['search']['order_date'])) {
            if (isset($post['search']['order_date']['from']) && !empty($post['search']['order_date']['from'])) {
                $from = self::convertDate($post['search']['order_date']['from']);
            }

            if (isset($post['search']['order_date']['to']) && !empty($post['search']['order_date']['to'])) {
                $to = self::convertDate($post['search']['order_date']['to']);
            }

            if (isset($from) && isset($to)) {
                $where_all .= " AND order_date BETWEEN :order_date_from AND :order_date_to";
                $params_sql[':order_date_from'] = $from;
                $params_sql[':order_date_to'] = $to;
            }
        }

        if (isset($post['search']['vendor']) && !empty($post['search']['vendor'])) {
            $vendors = implode(",", $post['search']['vendor']);
            $where_all .= " AND vendor in ($vendors)";
        }

        if (isset($post['search']['store']) && !empty($post['search']['store'])) {
            $stories = implode(",", $post['search']['store']);
            $where_all .= " AND store in ($stories)";
        }

        $sort_field = "";
        if ($sort) {
            $order = (preg_match('#^-(.+?)$#', $sort) ? SORT_DESC : SORT_ASC);
            $sort_field = str_replace('-', '', $sort);
            $where_all .= " AND $sort_field is not null ";
        }

        $params['client_id'] = $client->id;

        $apiShema = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);

        $sql = "
        SELECT DISTINCT * FROM (
            SELECT 
                id, 
                '" . self::TYPE_ORDER . "' as type, 
                client_id, 
                null as waybill_status_id, 
                created_at as order_date, 
                null as waybill_date, 
                null as waybill_number, 
                null as doc_number, 
                vendor_id as vendor, 
                null as store
            FROM `order` 
            WHERE client_id = {$client->id}
        UNION ALL
            SELECT
                w.id, 
                '" . self::TYPE_WAYBILL . "' as type, 
                acquirer_id as client_id, 
                status_id as waybill_status_id, 
                null as order_date, 
                doc_date as waybill_date, 
                edi_number as waybill_number, 
                outer_number_code as doc_number,  
                o.vendor_id as vendor, 
                outer_store_id as store
            FROM `$apiShema`.waybill w
            LEFT JOIN `$apiShema`.waybill_content wc ON wc.waybill_id = w.id
            LEFT JOIN order_content oc ON oc.id = wc.order_content_id
            LEFT JOIN `order` o ON o.id = oc.order_id
            WHERE 
                oc.order_id is null 
            AND 
                w.service_id = :service_id
        ) as documents
        WHERE 
        id is not null $where_all
       ";

        if (is_null($sort)) {
            $sql .= 'ORDER BY coalesce(documents.order_date,documents.waybill_date) DESC';
        }

        $dataProvider = new SqlDataProvider([
            'sql'        => $sql,
            'params'     => $params_sql,
            'pagination' => [
                'page'     => $page - 1,
                'pageSize' => $pageSize,
            ],
            'key'        => 'id',
            'sort'       => [
                'attributes' => [
                    'id',
                    'client_id',
                    'order_date',
                    'waybill_date',
                    'waybill_number',
                    'doc_number',
                ],
            ],
        ]);

        if (isset($order)) {
            $dataProvider->sort->defaultOrder = [$sort_field => $order];
        }

        $result = $dataProvider->getModels();
        if (!empty($result)) {
            foreach ($this->iterator($result) as $model) {
                $modelClass = self::$models[$model['type']];
                $documents[] = $modelClass::prepareModel($model['id']);
            }
        }

        $return = [
            'documents'  => $documents,
            'pagination' => [
                'page'       => $dataProvider->pagination->page + 1,
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        if (!empty($sort_field)) {
            $return['sort'] = $sort_field;
        }
        return $return;
    }

    /**
     * Накладная - Сброс позиций
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function waybillResetPositions(array $post)
    {
        $this->validateRequest($post, ['waybill_id']);

        $waybill = Waybill::findOne(['id' => $post['waybill_id']]);

        if (!isset($waybill)) {
            throw new BadRequestHttpException("waybill_not_found");
        }

        if (in_array($waybill->status_id, [Registry::WAYBILL_UNLOADED, Registry::WAYBILL_UNLOADING])) {
            throw new BadRequestHttpException("document.waybill_in_the_state_of_reset_or_unloaded");
        }

        try {
            if ($waybill->resetPositions()) {
                return $waybill->prepare();
            } else {
                throw new BadRequestHttpException('waybill.error_reset_positions');
            }

        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Накладная - Детальная информация
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getWaybillDetail(array $post)
    {
        $this->validateRequest($post, ['waybill_id']);

        return Waybill::prepareDetail($post['waybill_id']);

    }

    /**
     * Накладная - Обновление детальной информации
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidArgumentException
     * @throws ValidationException
     */
    public function editWaybillDetail(array $post)
    {
        $this->validateRequest($post, ['id']);

        $waybill = Waybill::findOne(['id' => $post['id'], 'acquirer_id' => $this->user->organization_id]);
        if (!isset($waybill)) {
            throw new BadRequestHttpException("waybill_not_found");
        }

        if (!empty($post['agent_uid'])) {
            $waybill->outer_agent_id = $post['agent_uid'];
        }

        if (!empty($post['store_uid'])) {
            $waybill->outer_store_id = $post['store_uid'];
        }

        if (!empty($post['doc_date'])) {
            $waybill->doc_date = date("Y-m-d H:i:s", strtotime($post['doc_date']));
        }

        if (!empty($post['outer_number_additional'])) {
            $waybill->outer_number_additional = $post['outer_number_additional'];
        }

        if (!empty($post['outer_number_code'])) {
            $waybill->outer_number_code = $post['outer_number_code'];
        }

        if (!empty($post['outer_note'])) {
            $waybill->outer_note = $post['outer_note'];
        }

        if ($waybill->validate() && $waybill->save()) {
            return $waybill->prepare();
        } else {
            throw new ValidationException($waybill->getFirstErrors());
        }
    }

    /**
     * @param $date
     * @return string
     */
    private static function convertDate($date)
    {
        $result = \DateTime::createFromFormat('d.m.Y H:i:s', $date . " 00:00:00");
        if ($result) {
            return $result->format('Y-m-d H:i:s');
        }

        return "";
    }

    /**
     * Накладная - Сопоставление с заказом
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function mapWaybillOrder(array $post)
    {
        $this->validateRequest($post, ['replaced_order_id', 'document_id']);

        $replacedOrder = \common\models\Order::findOne([
            'id'         => (int)$post['replaced_order_id'],
            'service_id' => Registry::MC_BACKEND
        ]);

        if (!isset($replacedOrder)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'document.replaced_order_not_found', ['ru' => 'Заменяемый документ не найден или не является заказом']));
        }

        $order = \common\models\Order::findOne([
            'id'         => (int)$post['document_id'],
            'service_id' => Registry::VENDOR_DOC_MAIL_SERVICE_ID,
        ]);

        if (!isset($order)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'document.document_not_found', ['ru' => 'Документ не найден или не является документом от поставщика']));
        }

        if ($order->status == Order::STATUS_CANCELLED) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'document.document_cancelled', ['ru' => 'Документ в состоянии "Отменен"']));
        }

        if (!is_null($order->replaced_order_id)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'document.document_replaced_order_id_is_not_null', ['ru' => 'Документ уже заменен']));
        }

        $replacedOrder->status = Order::STATUS_CANCELLED;
        $order->replaced_order_id = (int)$post['replaced_order_id'];

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$replacedOrder->save()) {
                throw new ValidationException($replacedOrder->getFirstErrors());
            }

            if (!$order->save()) {
                throw new ValidationException($replacedOrder->getFirstErrors());
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return ['result' => true];
    }

    /**
     * @return array
     */
    public function getDocumentStatus()
    {
        return self::$doc_group_status;
    }

    /**
     * @return array
     */
    public function getWaybillStatus()
    {
        return array_map(function ($el) {
            return \Yii::t('api_web', 'waybill.' . $el);
        }, Registry::$waybill_statuses);
    }

    /**
     * @param $items
     * @return \Generator
     */
    private function iterator($items)
    {
        if (!empty($items)) {
            foreach ($items as $item) {
                yield $item;
            }
        }
    }
}