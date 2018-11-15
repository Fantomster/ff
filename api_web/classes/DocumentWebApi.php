<?php

namespace api_web\classes;

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use api_web\helpers\CurrencyHelper;
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
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/**
 * Class DocumentWebApi
 *
 * @package api_web\modules\integration\classes
 */
class DocumentWebApi extends \api_web\components\WebApi
{
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

    /**
     * @var array available models
     */
    private static $models = [
        self::TYPE_WAYBILL     => Waybill::class,
        self::TYPE_ORDER       => Order::class,
        self::TYPE_ORDER_EMAIL => OrderEmail::class,
        self::TYPE_ORDER_EDI   => EdiOrder::class,
    ];

    /**
     * @var array available modelContents
     */
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
            throw new BadRequestHttpException('document.not_support_type');
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
            throw new BadRequestHttpException('document.not_support_type');
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
        if (isset($post['has_order_content']) && is_bool($post['has_order_content'])) {
            $hasOrderContent = $post['has_order_content'];
        }

        switch (strtolower($post['type'])) {
            case self::TYPE_ORDER :
                return $this->getDocumentOrder($post['document_id'], $post['service_id']);
                break;
            case self::TYPE_WAYBILL:
                return $this->getDocumentWaybill($post['document_id'], $post['service_id'], $hasOrderContent);
                break;
            default:
                throw new BadRequestHttpException('document.not_support_type');
        }
    }

    /**
     * Возвращаем информацию по документу типа order
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
                $modelClass::$serviceId = $service_id;
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
     */
    private function getDocumentWaybill($document_id, $service_id, $hasOrderContent = null)
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
            if ($hasOrderContent === true) {
                $query->andWhere(['not', ['order_content_id' => null]]);
            } elseif ($hasOrderContent === false) {
                $query->andWhere(['order_content_id' => null]);
            }
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

        $sort = (isset($post['sort']) ? $post['sort'] : null);
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $documents = [];

        $params_sql = [];
        $where_all = "";
        $params_sql[':service_id'] = $post['service_id'];
        $params_sql[':business_id'] = $this->user->organization_id;
        if (isset($post['search']['business_id']) && !empty($post['search']['business_id'])) {
            if (RelationUserOrganization::findOne(['user_id' => $this->user->id, 'organization_id' => $post['search']['business_id']])) {
                $params_sql[':business_id'] = $post['search']['business_id'];
            } else {
                throw new BadRequestHttpException("business unavailable to current user");
            }
        }

        if (isset($post['search']['waybill_status']) && !empty($post['search']['waybill_status'])) {
            $where_all .= " AND status_id = :status";
            $params_sql[':status'] = $post['search']['waybill_status'];
        }

        if (isset($post['search']['number']) && !empty($post['search']['number'])) {
            $where_all .= " AND doc_number = :doc_number";
            $params_sql[':doc_number'] = $post['search']['number'];
        }

        if (isset($post['search']['waybill_date']) && !empty($post['search']['waybill_date'])) {
            if (isset($post['search']['waybill_date']['from']) && !empty($post['search']['waybill_date']['from'])) {
                $from = self::convertDate($post['search']['waybill_date']['from'], 'from');
            }

            if (isset($post['search']['waybill_date']['to']) && !empty($post['search']['waybill_date']['to'])) {
                $to = self::convertDate($post['search']['waybill_date']['to'], 'to');
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
                $from = self::convertDate($post['search']['order_date']['from'], 'from');
            }

            if (isset($post['search']['order_date']['to']) && !empty($post['search']['order_date']['to'])) {
                $to = self::convertDate($post['search']['order_date']['to'], 'to');
            }

            if (isset($from) && isset($to)) {
                $where_all .= " AND order_date BETWEEN :order_date_from AND :order_date_to";
                $params_sql[':order_date_from'] = $from;
                $params_sql[':order_date_to'] = $to;
            }
        }

        if (isset($post['search']['vendor']) && !empty($post['search']['vendor'])) {
            $vendors = implode(",", $post['search']['vendor']);
            $where_all .= " AND vendor_id in ($vendors)";
        }

        if (isset($post['search']['store']) && !empty($post['search']['store'])) {
            $stories = implode(",", $post['search']['store']);
            $where_all .= " AND store_id in ($stories)";
        }

        $apiShema = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);

        /*$sql = "
        SELECT DISTINCT * FROM (
            SELECT 
                o.id as id, 
                edi_number as doc_number, 
                '" . self::TYPE_ORDER . "' as type, 
                null as waybill_status_id, 
                created_at as doc_date, 
                null as agent,
                vendor_id as vendor, 
                null as store
            FROM `order` as o
            LEFT JOIN (
             SELECT id, order_id, edi_number
				FROM order_content 
				order by char_length(edi_number) desc limit 1
            ) as oc on oc.order_id = o.id
            WHERE client_id = {$client->id}
        UNION ALL
            SELECT
                w.id as id, 
                outer_number_code as doc_number,  
                '" . self::TYPE_WAYBILL . "' as type, 
                status_id as waybill_status_id, 
                doc_date, 
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
       ";*/

        $sql = "SELECT DISTINCT documents.*, org.name as vendor_name FROM (
                SELECT 
                   o.id as id, 
                   edi_number as doc_number, 
                   (select group_concat(edi_number) from order_content sq1 where sq1.order_id = o.id order by edi_number) documents,
                   '" . self::TYPE_ORDER . "' as type, 
                   if(is_not_compared > 0," . Registry::DOC_GROUP_STATUS_WAIT_FORMING . ",
                   if(st.formed > 0, " . Registry::DOC_GROUP_STATUS_WAIT_FORMING . ", 
                   if(st.compared > 0, " . Registry::DOC_GROUP_STATUS_WAIT_SENDING . ", if(st.unloaded > 0,  " . Registry::DOC_GROUP_STATUS_SENT . ", " . Registry::DOC_GROUP_STATUS_WAIT_FORMING . ")))) as status_id, 
                   o.service_id,
                   certs as is_mercury_cert,
                   `count`,
                   total_price as total_price,
                   null as total_price_with_out_vat,
                   o.created_at as doc_date, 
                   o.vendor_id, 
                   oa.id as agent_id,
                   oa.name as agent_name,
                   ifnull(oa.agent_count,0) as agent_count,
                   null as store_id,
                   null as store_name,
                   null as waybill_status,
                   null as waybill_date,
                   o.created_at as order_date,
                   o.replaced_order_id as replaced_order_id
                   FROM `order` as o
                   LEFT JOIN (
                      SELECT id, order_id, edi_number
                      FROM order_content order by char_length(edi_number) desc, edi_number desc limit 1
                   ) as oc on oc.order_id = o.id
                   LEFT JOIN (
                       select order_id, count(oc.id) as `count`, count(merc_uuid) as certs, sum(if(wc.id is null, 1, 0)) as is_not_compared 
                       from order_content as oc 
                       left join `$apiShema`.waybill_content as wc on wc.order_content_id = oc.id
                       group by (order_id)
                   ) as counts on counts.order_id = o.id
                   LEFT JOIN (
                       SELECT *, count(id) as agent_count 
                       FROM `$apiShema`.outer_agent where org_id = :business_id and service_id = :service_id group by vendor_id
                   ) as oa on oa.vendor_id = o.vendor_id
                   LEFT JOIN (
                       SELECT 
                          order_id, 
                          sum(if(w.status_id = " . Registry::WAYBILL_COMPARED . ", 1 , 0)) as compared,
                          sum(if(w.status_id  = " . Registry::WAYBILL_UNLOADED . ", 1 , 0)) as unloaded,
                          sum(if(w.status_id = " . Registry::WAYBILL_FORMED . ", 1 , 0)) as formed
                       FROM `$apiShema`.waybill as w
                       left join `$apiShema`.waybill_content as wc on wc.waybill_id = w.id
                       left join order_content as oc on oc.id = wc.order_content_id
                       left join `order` as o on o.id = oc.order_id
                       where order_id is not null
                  ) as st on st.order_id = o.id
                WHERE o.client_id = :business_id	
                UNION ALL
                SELECT DISTINCT
                    w.id, 
                    outer_number_code as doc_number, 
                    outer_number_code as documents,
                    '" . self::TYPE_WAYBILL . "' as type, 
                    status_id, 
                    w.service_id,
                    0 as is_mercury_cert,
                    `count`,
                    counts.total_price,
                    counts.total_price_with_out_vat,
                    doc_date, 
                    oa.vendor_id as vendor_id,
                    oa.id as agent_id,
                    oa.name as agent_name,
                    os.id as store_id,
                    os.name as store_name,
                    status_id as waybill_status,
                    doc_date as waybill_date,
                    1 as agent_count,
                    null as order_date,
                    null as replaced_order_id
                    FROM `$apiShema`.waybill w
                    LEFT JOIN `$apiShema`.waybill_content as wc on  w.id = wc.waybill_id
                    LEFT JOIN (SELECT waybill_id, sum(ifnull(order_content_id,0)) as orders FROM `$apiShema`.waybill_content group by waybill_id) as o on o.waybill_id = w.id
                    LEFT JOIN (
                                select waybill_id, count(id) as `count`, sum(sum_with_vat) as total_price, sum(sum_without_vat) as total_price_with_out_vat from `$apiShema`.waybill_content group by (waybill_id)
                                ) as counts on counts.waybill_id = w.id
                    LEFT JOIN `$apiShema`.outer_agent as oa on oa.id = w.outer_agent_id      
                    LEFT JOIN `$apiShema`.outer_store as os on os.id = w.outer_store_id         
                    WHERE (o.orders = 0 OR wc.id is null) AND w.service_id = :service_id AND w.acquirer_id = :business_id
                ) as documents
                LEFT JOIN organization as org on org.id = vendor_id
                WHERE documents.id is not null $where_all";

        if ($sort) {
            $order = (preg_match('#^-(.+?)$#', $sort) ? 'DESC' : 'ASC');
            $sort_field = str_replace('-', '', $sort);
            //$where_all .= " AND $sort_field is not null ";
            if ($sort_field == 'number') {
                $sql .= ' ORDER BY char_length(doc_number) ' . $order;
            } elseif ($sort_field == 'doc_date') {
                $sql .= ' ORDER BY doc_date ' . $order;
            }
        } else {
            $sql .= ' ORDER BY doc_date DESC';
        }

        //var_dump($sql); die();

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
                    'doc_date',
                ],
            ],
        ]);

        /*if (isset($order)) {
            $dataProvider->sort->defaultOrder = [$sort_field => $order];
        }*/

        $result = $dataProvider->getModels();
        if (!empty($result)) {
            foreach ($this->iterator($result) as $model) {

                if ($model['type'] == self::TYPE_WAYBILL) {
                    $statusText = \Yii::t('api_web', 'waybill.' . Registry::$waybill_statuses[$model['status_id']]);
                } else {
                    $statusText = \Yii::t('api_web', 'doc_group.' . Registry::$doc_group_status[$model['status_id']]);
                }

                $documents[] = $return = [
                    "id"                       => (int)$model['id'],
                    "number"                   => isset($model['documents']) ? explode(",", $model['documents']) : [],
                    "type"                     => $model['type'],
                    "status_id"                => (int)$model['status_id'],
                    "status_text"              => $statusText,
                    "service_id"               => (int)$model['service_id'],
                    "is_mercury_cert"          => (int)($model['is_mercury_cert'] > 0),
                    "count"                    => (int)$model['count'],
                    "total_price"              => CurrencyHelper::asDecimal($model['total_price']),
                    "total_price_with_out_vat" => CurrencyHelper::asDecimal($model['total_price_with_out_vat'], 2, null),
                    "doc_date"                 => date("Y-m-d H:i:s T", strtotime($model['doc_date'])),
                    "vendor"                   => (!(isset($model['vendor_id']) && isset($model['vendor_name']))) ? null :
                        [
                            "id"    => $model['vendor_id'],
                            "name"  => $model['vendor_name'],
                            "difer" => false,
                        ],
                    "agent"                    => (!(isset($model['agent_id']) && isset($model['agent_name']))) ? null :
                        [
                            "id"   => $model['agent_id'],
                            "name" => $model['agent_name'],
                            "count" => $model['agent_count'],
                        ],
                    "store"                    => (!(isset($model['store_id']) && isset($model['store_name']))) ? null :
                        [
                            "id"   => $model['store_id'],
                            "name" => $model['store_name'],
                        ],
                    "replaced_order_id"        => isset($model['replaced_order_id']) ? (int)$model['replaced_order_id'] : null
                ];
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

        $waybill = Waybill::findOne(['id' => $post['waybill_id'], 'acquirer_id' => $this->user->organization_id]);

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
     * @param $direction
     * @return string
     */
    private static function convertDate($date, $direction)
    {
        $strTime = " 00:00:00";
        if ($direction == 'to') {
            $strTime = " 23:59:59";
        }
        $result = \DateTime::createFromFormat('d.m.Y H:i:s', $date . $strTime);
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

        if (empty($replacedOrder)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'document.replaced_order_not_found', ['ru' => 'Заменяемый документ не найден или не является заказом']));
        }

        $document = OrderEmail::findOne([
            'id'         => (int)$post['document_id'],
            'service_id' => Registry::VENDOR_DOC_MAIL_SERVICE_ID,
        ]);

        if (empty($document)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'document.document_not_found', ['ru' => 'Документ не найден или не является документом от поставщика']));
        }

        if ($document->status == Order::STATUS_CANCELLED) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'document.document_cancelled', ['ru' => 'Документ в состоянии "Отменен"']));
        }

        if (!is_null($document->replaced_order_id)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'document.document_replaced_order_id_is_not_null', ['ru' => 'Документ уже заменен']));
        }

        $replacedOrder->status = Order::STATUS_CANCELLED;
        $document->replaced_order_id = (int)$post['replaced_order_id'];

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$replacedOrder->save()) {
                throw new ValidationException($replacedOrder->getFirstErrors());
            }

            if (!$document->save()) {
                throw new ValidationException($document->getFirstErrors());
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $document->prepare();
    }

    /**
     * @return array
     */
    public function getDocumentStatus()
    {
        return array_map(function ($el) {
            return \Yii::t('api_web', 'doc_group.' . $el);
        }, Registry::$doc_group_status);
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
     * Список сортировок списка документов
     *
     * @return array
     */
    public function getSortList()
    {
        return [
            'number'    => \Yii::t('api_web', 'doc_order.doc_number'),
            '-number'   => \Yii::t('api_web', 'doc_order.-doc_number'),
            'doc_date'  => \Yii::t('api_web', 'doc_order.doc_date'),
            '-doc_date' => \Yii::t('api_web', 'doc_order.-doc_date'),
        ];
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

    /**
     * @param array $request
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function getDocument(array $request)
    {
        $this->validateRequest($request, ['service_id', 'type', 'document_id']);

        if (array_key_exists($request['type'], self::$models)) {
            $modelClass = self::$models[$request['type']];
            /**@var ActiveQuery $query */
            $query = $modelClass::find()->where(['id' => $request['document_id']]);
            if ($request['type'] == self::TYPE_WAYBILL) {
                $field = 'acquirer_id';
            } elseif ($request['type'] == self::TYPE_ORDER) {
                $field = 'client_id';
            }
            if (!$query->andWhere([$field => $this->user->organization_id])->exists()) {
                throw new BadRequestHttpException($request['type'] . '_not_found');
            }

            $document = $modelClass::prepareModel($request['document_id'], $request['service_id']);
        }

        return array_merge(['document' => $document], $this->getDocumentContents($request));
    }
}