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
use common\models\OuterAgent;
use common\models\OuterStore;
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
        $existsQuery = (new Query())
            ->select(['w.id'])
            ->from($apiDb . '.' . \common\models\Waybill::tableName() . ' as w')
            ->innerJoin($apiDb . '.' . \common\models\WaybillContent::tableName() . ' as wc', 'wc.waybill_id = w.id')
            ->where('wc.order_content_id = oc.id')
            ->andWhere('w.service_id = :servie_id', [':service_id' => (int)$service_id])
            ->prepare();

        $result['positions'] = (new Query())
            ->select(['oc.id'])
            ->from(\common\models\OrderContent::tableName() . ' as oc')
            ->where('oc.order_id = :order_id', [':order_id' => (int)$document_id])
            ->andWhere(['not exists', $existsQuery])
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
            $where_all .= " AND waybill_status_id = :status";
            $params_sql[':status'] = $post['search']['waybill_status'];
        }

        if (isset($post['search']['number']) && !empty($post['search']['number'])) {
            $where_all .= " AND dat.supply = :doc_number";
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
            $where_all .= " AND dat.order_vendor_id in ($vendors)";
        }

        if (isset($post['search']['store']) && !empty($post['search']['store'])) {
            $stories = implode(",", $post['search']['store']);
            $where_all .= " AND if(order_id IS NULL , osw.id in ($stories), ov.id in ($stories))";
        }

        $apiShema = DBNameHelper::getApiName();
        /** Статусы накладной */
        $params_sql[':WAYBILL_FORMED'] = Registry::WAYBILL_FORMED;
        $params_sql[':WAYBILL_COMPARED'] = Registry::WAYBILL_COMPARED;
        $params_sql[':WAYBILL_ERROR'] = Registry::WAYBILL_ERROR;
        $params_sql[':WAYBILL_UNLOADED'] = Registry::WAYBILL_UNLOADED;
        $params_sql[':WAYBILL_UNLOADING'] = Registry::WAYBILL_UNLOADING;

        /** Групповые статусы */
        $params_sql[':DOC_GROUP_STATUS_SENT'] = Registry::DOC_GROUP_STATUS_SENT;
        $params_sql[':DOC_GROUP_STATUS_WAIT_FORMING'] = Registry::DOC_GROUP_STATUS_WAIT_FORMING;
        $params_sql[':DOC_GROUP_STATUS_WAIT_SENDING'] = Registry::DOC_GROUP_STATUS_WAIT_SENDING;

        $sql = "SELECT
                  if(order_id IS NULL, waybill_id, order_id)     id,
                  if(order_id IS NULL, 'waybill', 'order')       `type`,
                  dat.sort_doc                                   doc_date,
                  group_concat(DISTINCT dat.supply)              documents,
                  dat.order_acquirer_id                          order_acquirer_id,
                  dat.order_vendor_id                            order_vendor_id,
                  if(merc_uuid IS NULL, 0, 1)                    is_mercury_cert,
                  dat.replaced_order_id                          replaced_order_id,
                  oav.id                                         object_agent_id,
                  oav.name                                       object_agent_name,
                  COUNT(DISTINCT oav.id)                         object_agent_count,
                  if(order_id IS NULL, osw.id, ov.id)            object_store_id,
                  if(order_id IS NULL, osw.name, ov.name)        object_store_name,
                  dat.order_service_id                           order_service_id,
                  if(order_id IS NULL, waybill_status_id, NULL)  wb_status_id,
                  if(order_id IS NULL, waybill_service_id, NULL) wb_service_id,
                  group_concat(DISTINCT dat.waybill_id)          wbs,
                  count(
                      if(order_id IS NULL,
                         dat.waybill_content_id,
                         coalesce(dat.order_content_id, dat.waybill_content_id)
                      )
                  )                                              object_position_count,
                  sum(tatal_price)                               object_total_price,
                  dat.sort_doc                                   sort_doc,
                  dat.outer_number_code                          outer_number_code,
                  dat.outer_number_additional                    outer_number_additional,
                  IF(order_id IS NOT NULL,
                     CASE
                     WHEN group_concat(DISTINCT dat.waybill_status_id) = :WAYBILL_UNLOADED
                       THEN :DOC_GROUP_STATUS_SENT
                     WHEN instr(group_concat(DISTINCT dat.waybill_status_id), :WAYBILL_FORMED) > 0
                       THEN :DOC_GROUP_STATUS_WAIT_FORMING
                     WHEN group_concat(DISTINCT dat.waybill_status_id) IN (:WAYBILL_ERROR, :WAYBILL_UNLOADED, :WAYBILL_UNLOADING)
                       THEN :DOC_GROUP_STATUS_WAIT_SENDING
                     ELSE :DOC_GROUP_STATUS_WAIT_SENDING END,
                     waybill_status_id
                  )                                              object_group_status_id,
                  waybill_date                                   waybill_date,
                  order_date                                     order_date
                FROM (
                       SELECT
                         d.id                                             order_id,
                         d.client_id                                      order_acquirer_id,
                         d.vendor_id                                      order_vendor_id,
                         d.service_id                                     order_service_id,
                         d.replaced_order_id,
                         a.id                                             waybill_id,
                         a.status_id                                      waybill_status_id,
                         a.service_id                                     waybill_service_id,
                         a.acquirer_id                                    waybill_acquirer_id,
                         a.outer_store_id                                 waybill_outer_store_id,
                         a.outer_agent_id                                 waybill_outer_agent_id,
                         a.outer_number_code                              outer_number_code,
                         a.outer_number_additional                        outer_number_additional,
                         b.id                                             waybill_content_id,
                         coalesce(b.sum_with_vat, 0)                      tatal_price,
                         c.id                                             order_content_id,
                         c.edi_number                                     supply,
                         c.merc_uuid,
                         coalesce(d.created_at, a.doc_date, a.created_at) sort_doc,
                         coalesce(a.doc_date, a.created_at)               sort_waybill,
                         coalesce(a.doc_date, a.created_at)               waybill_date,
                         null                                             order_date
                       FROM `$apiShema`.waybill a
                         LEFT JOIN `$apiShema`.waybill_content b ON b.waybill_id = a.id
                         LEFT JOIN order_content c ON c.id = b.order_content_id
                         LEFT JOIN `order` d ON d.id = c.order_id
                       WHERE a.acquirer_id = :business_id AND a.service_id = :service_id
                       AND NOT EXISTS(
                               SELECT sqwc.waybill_id
                               FROM `$apiShema`.waybill_content sqwc
                                 JOIN order_content sqoc ON sqoc.id = sqwc.order_content_id
                               WHERE sqwc.waybill_id = a.id
                           )
                       UNION
                       SELECT
                         a.id                                                   order_id,
                         a.client_id                                            order_acquirer_id,
                         a.vendor_id                                            order_vendor_id,
                         a.service_id                                           order_service_id,
                         a.replaced_order_id,
                         d.id                                                   waybill_id,
                         d.status_id                                            waybill_status_id,
                         d.service_id                                           waybill_service_id,
                         d.acquirer_id                                          waybill_acquirer_id,
                         d.outer_store_id                                       waybill_outer_store_id,
                         d.outer_agent_id                                       waybill_outer_agent_id,
                         d.outer_number_code                                    outer_number_code,
                         d.outer_number_additional                              outer_number_additional,
                         c.id                                                   waybill_content_id,
                         coalesce(if(c.id IS NULL, b.price, 0) * b.quantity, 0) total_price,
                         b.id                                                   order_content_id,
                         b.edi_number                                           supply,
                         b.merc_uuid,
                         coalesce(a.created_at, d.doc_date, d.created_at)       sort_doc,
                         coalesce(d.doc_date, d.created_at)                     sort_waybill,
                         null                                                   waybill_date,
                         a.created_at                                           order_date
                       FROM `order` a
                         JOIN order_content b ON b.order_id = a.id
                         LEFT JOIN `$apiShema`.waybill_content c ON c.order_content_id = b.id
                         LEFT JOIN `$apiShema`.waybill d ON d.id = c.waybill_id AND d.service_id = :service_id
                       WHERE a.client_id = :business_id
                     ) dat
                  LEFT JOIN `$apiShema`.outer_agent oav ON oav.org_id = :business_id AND oav.service_id = :service_id AND
                                               if(dat.order_id IS NULL, dat.waybill_outer_agent_id, dat.order_vendor_id) =
                                               if(dat.order_id IS NULL, oav.id, oav.vendor_id)
                  LEFT JOIN organization ov ON ov.id = dat.order_vendor_id
                  LEFT JOIN `$apiShema`.outer_store osw ON osw.org_id = :business_id AND osw.service_id = :service_id AND dat.waybill_outer_store_id = osw.id
                WHERE 1  $where_all  
                GROUP BY id, dat.order_acquirer_id, dat.order_service_id, wb_status_id, wb_service_id, sort_doc";

        if ($sort) {
            $order = (preg_match('#^-(.+?)$#', $sort) ? 'DESC' : 'ASC');
            $sort_field = str_replace('-', '', $sort);
            if ($sort_field == 'number') {
                $sql .= ' ORDER BY documents ' . $order;
            } elseif ($sort_field == 'doc_date') {
                $sql .= ' ORDER BY doc_date ' . $order;
            }
        } else {
            $sql .= ' ORDER BY sort_doc DESC, order_id, sort_waybill desc, waybill_id';
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
                    'doc_date',
                ],
            ],
        ]);

        $result = $dataProvider->getModels();
        if (!empty($result)) {
            foreach ($this->iterator($result) as $model) {

                if ($model['type'] == self::TYPE_WAYBILL) {
                    $statusText = \Yii::t('api_web', 'waybill.' . Registry::$waybill_statuses[$model['wb_status_id']]);
                    $status_id = $model['wb_status_id'];
                    $service_id = $model['wb_service_id'];
                } else {
                    $statusText = \Yii::t('api_web', 'doc_group.' . Registry::$doc_group_status[$model['object_group_status_id']]);
                    $status_id = $model['object_group_status_id'];
                    $service_id = $model['order_service_id'];
                }

                $documents[] = $return = [
                    "id"                      => (int)$model['id'],
                    "number"                  => isset($model['documents']) ? explode(",", $model['documents']) : [],
                    "type"                    => $model['type'],
                    "status_id"               => (int)$status_id,
                    "status_text"             => $statusText,
                    "service_id"              => (int)$service_id,
                    "is_mercury_cert"         => (int)($model['is_mercury_cert'] > 0),
                    "count"                   => (int)$model['object_position_count'],
                    "total_price"             => CurrencyHelper::asDecimal($model['object_total_price']),
                    //"total_price_with_out_vat" => CurrencyHelper::asDecimal($model['total_price_with_out_vat'], 2, null),
                    "doc_date"                => date("Y-m-d H:i:s T", strtotime($model['doc_date'])),
                    "outer_number_code"       => $model['outer_number_code'] ?? null,
                    "outer_number_additional" => $model['outer_number_additional'] ?? null,
                    "vendor"                  => (!(isset($model['order_vendor_id']) && isset($model['object_store_name']))) ? null :
                        [
                            "id"    => $model['order_vendor_id'],
                            "name"  => $model['object_store_name'],
                            "difer" => false,
                        ],
                    "agent"                   => (!(isset($model['object_agent_id']) && isset($model['object_agent_name']))) ? null :
                        [
                            "id"    => $model['object_agent_id'],
                            "name"  => $model['object_agent_name'],
                            "count" => $model['object_agent_count'],
                        ],
                    "store"                   => (!(isset($model['object_store_id']) && isset($model['object_store_name']))) ? null :
                        [
                            "id"   => $model['object_store_id'],
                            "name" => $model['object_store_name'],
                        ],
                    "replaced_order_id"       => isset($model['replaced_order_id']) ? (int)$model['replaced_order_id'] : null
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
            $agent = OuterAgent::findOne(['id' => $post['agent_uid'], 'org_id' => $this->user->organization_id]);
            if (!$agent) {
                throw new BadRequestHttpException('agent.not_found');
            }
            $waybill->outer_agent_id = $agent->id;
        }

        if (!empty($post['store_uid'])) {
            $store = OuterStore::findOne(['id' => $post['store_uid'], 'org_id' => $this->user->organization_id]);
            if (!$store) {
                throw new BadRequestHttpException('store.not_found');
            }
            //Если это категория а не склад
            if (!$store->isLeaf()) {
                throw new BadRequestHttpException('store.is_category');
            }
            $waybill->outer_store_id = $store->id;
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