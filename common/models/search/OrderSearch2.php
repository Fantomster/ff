<?php

namespace common\models\search;

use api\common\models\iiko\iikoWaybill;
use common\models\Order;
use common\models\User;
use frontend\modules\clientintegr\modules\rkws\controllers\WaybillController;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use Yii;
use common\components\SearchOrdersComponent;
use api\common\models\RkWaybill;
use yii\helpers\ArrayHelper;

/**
 * OrderSearch represents the model behind the search form about `common\models\Order`.
 */
class OrderSearch2 extends Order
{

    public $date_from;
    public $date_to;
    public $doc_status;
    public $wb_status;

    public function rules(): array
    {
        return [
            [['id', 'client_id', 'vendor_id', 'doc_status', 'wb_status'], 'integer'],
            [['date_from', 'date_to',], 'safe'],
        ];
    }


    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates dateFrom and dateTo parameters for filtering needs
     * @createdBy Basil A Konakov
     * @createdAt 2018-08-15
     * @var $dateFrom string
     * @var $dateTo string
     * */
    public function prepareDates(string $dateFrom = NULL, string $dateTo = NULL)
    {

        if (!$dateFrom) {
            $today = new \DateTime();
            $this->date_from = $today->format('d.m.Y');
        } else {
            $this->date_from = $dateFrom;
        }

        if (!$dateTo) {
            $today = new \DateTime();
            $this->date_to = $today->format('d.m.Y');
        } else {
            $this->date_to = $dateTo;
        }

    }


    /**
     * searchForIntegration R-Keeper
     * @var $params array
     * @var $businessType string
     * @var $wbStatuses array
     * @var $pagination array
     * @var $sort array
     * @return ActiveDataProvider
     */
    public function searchForIntegrationRKWS(array $params, string $businessType, array $wbStatuses = [], array $pagination = [], array $sort = []): ActiveDataProvider
    {

        $selfTypeColumnId = 'vendor_id';
        if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
            $selfTypeColumnId = 'client_id';
        }

        $orgId = User::findOne(Yii::$app->user->id)->organization_id;

        if (isset($params['OrderSearch2']['id']) && (int)$params['OrderSearch2']['id'] > 0) {
            $query = Order::find()->where(['id' => (int)$params['OrderSearch2']['id']])
                ->andWhere([$selfTypeColumnId => $orgId])
                ->andFilterWhere(['status' => Order::STATUS_DONE]);
        } else {

            $query = Order::find()->andWhere([$selfTypeColumnId => $orgId]);
            $this->load($params);


            $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_from . " 00:00:00");
            if ($from) {
                $query->andFilterWhere(['>=', Order::tableName() . '.created_at', $from->format('Y-m-d H:i:s')]);
            }
            $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_to . " 00:00:00");
            if ($to) {
                $to->add(new \DateInterval('P1D'));
                $query->andFilterWhere(['<=', Order::tableName() . '.created_at', $to->format('Y-m-d H:i:s')]);
            }

            if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
                if ($this->vendor_id) {
                    $query->andFilterWhere(['vendor_id' => $this->vendor_id]);
                }
            } elseif ($businessType == SearchOrdersComponent::BUSINESS_TYPE_VENDOR) {
                if ($this->client_id) {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }
            }

            if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
                if ($this->vendor_id) {
                    $query->andFilterWhere(['vendor_id' => $this->vendor_id]);
                }
            } elseif ($businessType == SearchOrdersComponent::BUSINESS_TYPE_VENDOR) {
                if ($this->client_id) {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }
            }

            $query->andFilterWhere(['status' => Order::STATUS_DONE]);



            if ($this->wb_status && isset($wbStatuses[$this->wb_status]) && $wbStatuses[$this->wb_status]) {
                /** @var string Все заказы, по которым есть накладные со статусом 5 и readytoexport > 0 */
                if ($wbStatuses[$this->wb_status] == WaybillController::ORDER_STATUS_READY_DEFINEDBY_WB_STATUS) {

                    # ищем все готовые к выгрузке
                    $qparams = ['org' => (int)$params['OrderSearch2']['client_id'], 'status_id' => 5, 'readytoexport' => 1];
                    $ordersWithReadyWbDoc = RkWaybill::find()->select('order_id') ->where($qparams)->asArray()->all();
                    $ordersWithReadyWbDoc = ArrayHelper::map($ordersWithReadyWbDoc, 'order_id', 'order_id');

                    $query->andWhere(['IN', 'order.id', $ordersWithReadyWbDoc]);

                } elseif ($wbStatuses[$this->wb_status] == WaybillController::ORDER_STATUS_COMPLETED_DEFINEDBY_WB_STATUS) {

                    # ищем все выгруженные
                    $qparams = ['org' => (int)$params['OrderSearch2']['client_id'], 'status_id' => 2];
                    $ordersWithFinalWbDoc = RkWaybill::find()->select('order_id') ->where($qparams)->asArray()->all();
                    $ordersWithFinalWbDoc = ArrayHelper::map($ordersWithFinalWbDoc, 'order_id', 'order_id');

                    $query->andWhere(['IN', 'order.id', $ordersWithFinalWbDoc]);

                } elseif ($wbStatuses[$this->wb_status] == WaybillController::ORDER_STATUS_FILLED_DEFINEDBY_WB_STATUS) {

                    # ищем все сформированные
                    $qparams = ['org' => (int)$params['OrderSearch2']['client_id']];
                    $all = RkWaybill::find()->select('order_id') ->where($qparams)->asArray()->all();
                    $all = ArrayHelper::map($all, 'order_id', 'order_id');

                    # ищем все готовые к выгрузке и выгруженные
                    $qparams = ['org' => (int)$params['OrderSearch2']['client_id'], 'status_id' => 5, 'readytoexport' => 1];
                    $ordersWithReadyWbDoc = RkWaybill::find()->select('order_id') ->where($qparams)->asArray()->all();
                    $ordersWithReadyWbDoc = ArrayHelper::map($ordersWithReadyWbDoc, 'order_id', 'order_id');

                    $qparams = ['org' => (int)$params['OrderSearch2']['client_id'], 'status_id' => 2];
                    $ordersWithFinalWbDoc = RkWaybill::find()->select('order_id') ->where($qparams)->asArray()->all();
                    $ordersWithFinalWbDoc = ArrayHelper::map($ordersWithFinalWbDoc, 'order_id', 'order_id');

                    $query->andWhere(['IN', 'order.id', array_diff($all, array_merge($ordersWithReadyWbDoc, $ordersWithFinalWbDoc))]);
                } elseif ($wbStatuses[$this->wb_status] == WaybillController::ORDER_STATUS_NODOC_DEFINEDBY_WB_STATUS) {

                    # ищем все сформированные
                    $qparams = ['org' => (int)$params['OrderSearch2']['client_id']];
                    $all = RkWaybill::find()->select('order_id') ->where($qparams)->asArray()->all();
                    $all = ArrayHelper::map($all, 'order_id', 'order_id');

                    $query->andWhere(['NOT IN', 'order.id', $all]);

                }
            }
        }

        $queryData = [
            'query' => $query,
        ];
        if ($pagination) {
            $queryData['pagination'] = $pagination;
            if ($sort) {
                $queryData['sort'] = $sort;
            }
        }
        return new ActiveDataProvider($queryData);
    }

    /**
     * searchForIntegration IIKO
     * @var $params array
     * @var $businessType string
     * @var $wbStatuses array
     * @var $pagination array
     * @var $sort array
     * @return ActiveDataProvider
     */
    public function searchForIntegrationIIKO(array $params, string $businessType, array $wbStatuses = [], array $pagination = [], array $sort = []): ActiveDataProvider
    {

        $selfTypeColumnId = 'vendor_id';
        if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
            $selfTypeColumnId = 'client_id';
        }

        $orgId = User::findOne(Yii::$app->user->id)->organization_id;

        if (isset($params['OrderSearch2']['id']) && (int)$params['OrderSearch2']['id'] > 0) {
            $query = Order::find()->where(['id' => (int)$params['OrderSearch2']['id']])
                ->andWhere([$selfTypeColumnId => $orgId])
                ->andFilterWhere(['status' => Order::STATUS_DONE]);
        } else {

            $query = Order::find()->andWhere([$selfTypeColumnId => $orgId]);
            $this->load($params);


            $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_from . " 00:00:00");
            if ($from) {
                $query->andFilterWhere(['>=', Order::tableName() . '.created_at', $from->format('Y-m-d H:i:s')]);
            }
            $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_to . " 00:00:00");
            if ($to) {
                $to->add(new \DateInterval('P1D'));
                $query->andFilterWhere(['<=', Order::tableName() . '.created_at', $to->format('Y-m-d H:i:s')]);
            }

            if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
                if ($this->vendor_id) {
                    $query->andFilterWhere(['vendor_id' => $this->vendor_id]);
                }
            } elseif ($businessType == SearchOrdersComponent::BUSINESS_TYPE_VENDOR) {
                if ($this->client_id) {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }
            }

            if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
                if ($this->vendor_id) {
                    $query->andFilterWhere(['vendor_id' => $this->vendor_id]);
                }
            } elseif ($businessType == SearchOrdersComponent::BUSINESS_TYPE_VENDOR) {
                if ($this->client_id) {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }
            }

            $query->andFilterWhere(['status' => Order::STATUS_DONE]);



            if ($this->wb_status && isset($wbStatuses[$this->wb_status]) && $wbStatuses[$this->wb_status]) {
                /** @var string Все заказы, по которым есть накладные со статусом 5 и readytoexport > 0 */
                if ($wbStatuses[$this->wb_status] == WaybillController::ORDER_STATUS_READY_DEFINEDBY_WB_STATUS) {

                    # ищем все готовые к выгрузке
                    $qparams = ['org' => (int)$params['OrderSearch2']['client_id'], 'status_id' => 4, 'readytoexport' => 1];
                    $ordersWithReadyWbDoc = iikoWaybill::find()->select('order_id') ->where($qparams)->asArray()->all();
                    $ordersWithReadyWbDoc = ArrayHelper::map($ordersWithReadyWbDoc, 'order_id', 'order_id');

                    $query->andWhere(['IN', 'order.id', $ordersWithReadyWbDoc]);

                } elseif ($wbStatuses[$this->wb_status] == WaybillController::ORDER_STATUS_COMPLETED_DEFINEDBY_WB_STATUS) {

                    # ищем все выгруженные
                    $qparams = ['org' => (int)$params['OrderSearch2']['client_id'], 'status_id' => 2];
                    $ordersWithFinalWbDoc = iikoWaybill::find()->select('order_id') ->where($qparams)->asArray()->all();
                    $ordersWithFinalWbDoc = ArrayHelper::map($ordersWithFinalWbDoc, 'order_id', 'order_id');

                    $query->andWhere(['IN', 'order.id', $ordersWithFinalWbDoc]);

                } elseif ($wbStatuses[$this->wb_status] == WaybillController::ORDER_STATUS_FILLED_DEFINEDBY_WB_STATUS) {

                    # ищем все сформированные
                    $qparams = ['org' => (int)$params['OrderSearch2']['client_id']];
                    $all = iikoWaybill::find()->select('order_id') ->where($qparams)->asArray()->all();
                    $all = ArrayHelper::map($all, 'order_id', 'order_id');

                    # ищем все готовые к выгрузке и выгруженные
                    $qparams = ['org' => (int)$params['OrderSearch2']['client_id'], 'status_id' => 4, 'readytoexport' => 1];
                    $ordersWithReadyWbDoc = iikoWaybill::find()->select('order_id') ->where($qparams)->asArray()->all();
                    $ordersWithReadyWbDoc = ArrayHelper::map($ordersWithReadyWbDoc, 'order_id', 'order_id');

                    $qparams = ['org' => (int)$params['OrderSearch2']['client_id'], 'status_id' => 2];
                    $ordersWithFinalWbDoc = iikoWaybill::find()->select('order_id') ->where($qparams)->asArray()->all();
                    $ordersWithFinalWbDoc = ArrayHelper::map($ordersWithFinalWbDoc, 'order_id', 'order_id');

                    $query->andWhere(['IN', 'order.id', array_diff($all, array_merge($ordersWithReadyWbDoc, $ordersWithFinalWbDoc))]);
                } elseif ($wbStatuses[$this->wb_status] == WaybillController::ORDER_STATUS_NODOC_DEFINEDBY_WB_STATUS) {

                    # ищем все сформированные
                    $qparams = ['org' => (int)$params['OrderSearch2']['client_id']];
                    $all = iikoWaybill::find()->select('order_id') ->where($qparams)->asArray()->all();
                    $all = ArrayHelper::map($all, 'order_id', 'order_id');

                    $query->andWhere(['NOT IN', 'order.id', $all]);

                }
            }
        }

        $queryData = [
            'query' => $query,
        ];
        if ($pagination) {
            $queryData['pagination'] = $pagination;
            if ($sort) {
                $queryData['sort'] = $sort;
            }
        }
        return new ActiveDataProvider($queryData);
    }

    /**
     * swdfsdfsfd
     * @var $params array
     * @var $businessType string
     * @var $orderStatuses array
     * @var $pagination array
     * @var $sort array
     * @return ActiveDataProvider
     */
    public function search(array $params, string $businessType, array $orderStatuses = [], array $pagination = [], array $sort = []): ActiveDataProvider
    {

        $selfTypeColumnId = 'vendor_id';
        if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
            $selfTypeColumnId = 'client_id';
        }

        if (isset($params['OrderSearch2']['id']) && (int)$params['OrderSearch2']['id'] > 0) {
            $query = Order::find()->where(['id' => (int)$params['OrderSearch2']['id']])
                ->andWhere([$selfTypeColumnId => User::findOne(Yii::$app->user->id)->organization_id]);
        } else {

            $query = Order::find()->andWhere([$selfTypeColumnId => User::findOne(Yii::$app->user->id)->organization_id]);

            $this->load($params);

            $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_from . " 00:00:00");
            if ($from) {
                $query->andFilterWhere(['>=', Order::tableName() . '.created_at', $from->format('Y-m-d H:i:s')]);
            }
            $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_to . " 00:00:00");
            if ($to) {
                $to->add(new \DateInterval('P1D'));
                $query->andFilterWhere(['<=', Order::tableName() . '.created_at', $to->format('Y-m-d H:i:s')]);
            }

            if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
                if ($this->vendor_id) {
                    $query->andFilterWhere(['vendor_id' => $this->vendor_id]);
                }
            } elseif ($businessType == SearchOrdersComponent::BUSINESS_TYPE_VENDOR) {
                if ($this->client_id) {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }
            }

            $keys = array_keys(array_merge([''], $orderStatuses));
            if (isset($keys[$this->doc_status]) && $keys[$this->doc_status]) {
                if (isset($orderStatuses[$keys[$this->doc_status]]) && $orderStatuses[$keys[$this->doc_status]]) {
                    $query->andFilterWhere(['status' => $orderStatuses[$keys[$this->doc_status]]]);
                }
            }
        }
        $queryData = [
            'query' => $query,
        ];
        if ($pagination) {
            $queryData['pagination'] = $pagination;
            if ($sort) {
                $queryData['sort'] = $sort;
            }
        }
        return new ActiveDataProvider($queryData);
    }


}