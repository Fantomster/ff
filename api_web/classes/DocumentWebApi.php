<?php

namespace api_web\classes;

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
    //todo_refactoring to Registry class
    const DOC_WAYBILL_STATUS_COLLATED = 'Сопоставлена';
    const DOC_WAYBILL_STATUS_READY = 'Сформирована';
    const DOC_WAYBILL_STATUS_ERROR = 'Ошибка';
    const DOC_WAYBILL_STATUS_RESET = 'Сброшена';
    const DOC_WAYBILL_STATUS_SENT = 'Выгружена';

    //todo_refactoring to Registry class
    private static $doc_waybill_status = [
        1 => self::DOC_WAYBILL_STATUS_COLLATED,
        2 => self::DOC_WAYBILL_STATUS_READY,
        3 => self::DOC_WAYBILL_STATUS_ERROR,
        4 => self::DOC_WAYBILL_STATUS_RESET,
        5 => self::DOC_WAYBILL_STATUS_SENT,
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
     * @param $document_id
     * @param $type
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function getHeader(array $post)
    {
        if (!isset($post['type'])) {
            throw new BadRequestHttpException("empty_param|type");
        }
        if (empty($post['document_id'])) {
            throw new BadRequestHttpException("empty_param|document_id");
        }

        if (!in_array(strtolower($post['type']), self::$TYPE_LIST)) {
            throw new BadRequestHttpException('dont support this type');
        }

        $className = self::$models[$post['type']];
        return $className::prepareModel($post['document_id']);
    }

    /**
     * Метод получения детальной части документа
     *
     * @param $document_id
     * @param $type
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function getContent(array $post)
    {
        if (!isset($post['type'])) {
            throw new BadRequestHttpException("empty_param|type");
        }
        if (empty($post['document_id'])) {
            throw new BadRequestHttpException("empty_param|document_id");
        }

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
        if (!isset($post['type'])) {
            throw new BadRequestHttpException("empty_param|type");
        }

        if (empty($post['document_id'])) {
            throw new BadRequestHttpException("empty_param|document_id");
        }

        if (empty($post['service_id']) && $post['type'] == self::TYPE_ORDER) {
            throw new BadRequestHttpException("empty_param|service_id");
        }

        if (!in_array(strtolower($post['type']), self::$TYPE_LIST)) {
            throw new BadRequestHttpException('dont support this type');
        }

        switch (strtolower($post['type'])) {
            case self::TYPE_ORDER :
                return $this->getDocumentOrder($post['document_id'], $post['service_id']);
                break;
            case self::TYPE_WAYBILL:
                return $this->getDocumentWaybill($post['document_id']);
                break;
            default:
                throw new BadRequestHttpException('dont support this type');
        }
    }

    /**
     * Возвращаем информацию по докумнту типа order
     *
     * @param      $document_id
     * @param null $service_id
     * @return array
     */
    private function getDocumentOrder($document_id, $service_id = null)
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
     * @param $document_id
     * @return array
     */
    private function getDocumentWaybill($document_id)
    {
        $result = [
            'documents' => [],
            'positions' => []
        ];

        $positions = (new Query())
            ->select('id')
            ->from(\common\models\WaybillContent::tableName())
            ->where('waybill_id = :doc_id', [':doc_id' => (int)$document_id])
            ->all(\Yii::$app->db_api);

        if (!empty($positions)) {
            $modelClass = self::$modelsContent[self::TYPE_WAYBILL];
            foreach ($this->iterator($positions) as $key => $position) {
                $result['positions'][$key] = $modelClass::prepareModel($position);
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
            $where_all .= " AND waybill_status = :waybill_status";
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
            $vendors = implode("', '", $post['search']['vendor']);
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
        select * from (
        SELECT * from (
            SELECT id, '" . self::TYPE_ORDER . "' as type, client_id, null as waybill_status, created_at as order_date, null as waybill_date, 
            null as waybill_number, id as doc_number, vendor_id as vendor, null as store, service_id
            FROM `order`
            UNION ALL
            SELECT id, '" . self::TYPE_ORDER_EMAIL . "' as type, organization_id as client_id, null as waybill_status, date as order_date, null as waybill_date,
            null as waybill_number, number as doc_number, vendor_id as vendor, null as store, null as service_id   
            FROM integration_invoice WHERE order_id is null
        ) as c
        UNION ALL
        SELECT id, '" . self::TYPE_WAYBILL . "' as type, acquirer_id as client_id, status_id as waybill_status, null as order_date, doc_date as waybill_date, 
        outer_number_code as waybill_number, null as doc_number,  outer_contractor_uuid as vendor, outer_store_uuid as store, service_id 
        FROM `$apiShema`.waybill WHERE order_id is null AND service_id = :service_id) as documents
        WHERE id is not null $where_all
       ";

        if (is_null($sort)) {
            $sql .= 'ORDER BY coalesce(documents.order_date,documents.waybill_date) DESC';
        }

        //$count = \Yii::$app->db->createCommand("select COUNT(*) from ($sql) as cc",$params_sql);
        //var_dump($count->rawSql); die();
        $dataProvider = new SqlDataProvider([
            'sql'        => $sql,
            'params'     => $params_sql,
            //'totalCount' => $count,
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
        foreach ($result as $model) {
            $modelClass = self::$models[$model['type']];
            $documents[] = $modelClass::prepareModel($model['id']);
        }

        $return = [
            'documents'  => $documents,
            'pagination' => [
                'page'       => $dataProvider->pagination->page + 1,
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ],
            'sort'       => $sort_field
        ];

        return $return;
    }

    /**
     * Накладная - Сброс позиций
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function waybillResetPositions(array $post)
    {
        if (!isset($post['waybill_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_id");
        }

        $waybill = Waybill::findOne(['id' => $post['waybill_id']]);

        if (!isset($waybill)) {
            throw new BadRequestHttpException("Waybill not found");
        }

        if ($waybill->status_id == 3) {
            throw new BadRequestHttpException("Waybill in the state of \"reset\" or \"unloaded\"");
        }

        $waybill->resetPositions();
        return ['result' => true];
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
        if (empty($post['waybill_id'])) {
            throw new BadRequestHttpException("empty_param|document_id");
        }

        return Waybill::prepareDetail($post['waybill_id']);
    }

    /**
     * Накладная - Обновление детальной информации
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function editWaybillDetail(array $post)
    {
        if (empty($post['id'])) {
            throw new BadRequestHttpException("EDIT CANCELED product id empty");
        }

        $waybill = Waybill::findOne(['id' => $post['id']]);

        if (!isset($waybill)) {
            throw new BadRequestHttpException("EDIT CANCELED the waybill - waybill not found");
        }

        if (!empty($post['agent_uid'])) {
            $waybill->outer_contractor_uuid = $post['agent_uid'];
        }

        if (!empty($post['store_uid'])) {
            $waybill->outer_store_uuid = $post['store_uid'];
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
            return $this->getWaybillDetail(['waybill_id' => $waybill->id]);
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
     */
    public function mapWaybillOrder(array $post)
    {
        if (empty($post['order_id'])) {
            throw new BadRequestHttpException("empty_param|order_id");
        }

        if (empty($post['document_id'])) {
            throw new BadRequestHttpException("empty_param|document_id");
        }

        $waybill = Waybill::findOne(['id' => $post['document_id']]);

        if (!isset($waybill)) {
            throw new BadRequestHttpException("waybill not found");
        }

        $waybill->mapWaybill($post['order_id']);
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
        return self::$doc_waybill_status;
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