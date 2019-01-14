<?php

namespace common\components;

use common\models\ManagerAssociate;
use common\models\Order;
use common\models\search\OrderSearch2;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Special service component for Customer-type user's `order searching` needs
 *
 * @createdBy Basil A Konakov
 * @createdAt 2018-08-14
 * @author    Mixcart
 * @module    Frontend
 * @version   1.0
 */
class SearchOrdersComponent extends Component
{

    public $affiliated = [];
    /** @var $searchParams array */
    public $searchParams = [];
    /** @var $counts array */
    public $counts = [
        'new'        => 0,
        'processing' => 0,
        'fulfilled'  => 0,
        // 'stopped' => 0, //
    ];
    /** @var $totalPrice int */
    public $totalPrice;

    /** @var $currentPage int */
    public $currentPage = 1;
    /** @var $selected array */
    public $selected = [];
    /** @var $dataProvider ActiveDataProvider */
    public $dataProvider;

    const BUSINESS_TYPE_RESTAURANT = 'restaurant';
    const BUSINESS_TYPE_VENDOR = 'vendor';

    /** @var $businessType string self::BUSINESS_TYPE_RESTAURANT || self::BUSINESS_TYPE_VENDOR */
    public $businessType;

    const INTEGRATION_TYPE_RKWS = 'rkws';
    const INTEGRATION_TYPE_IIKO = 'iiko';
    const INTEGRATION_TYPE_ONES = '1c';
    const INTEGRATION_TYPE_TILLYPAD = 'Tillypad';

    const IIKO_WB_DONT_SHOW_VARNAME_PREF = 'iiko_wb_hide_';
    const RKWS_WB_DONT_SHOW_VARNAME_PREF = 'rkws_wb_hide_';
    const ODIN_C_WB_DONT_SHOW_VARNAME_PREF = 'odin_c_wb_hide_';
    const TILLYPAD_WB_DONT_SHOW_VARNAME_PREF = 'tillypad_wb_hide_';

    /**
     * Search if $organization->type_id == Organization::TYPE_RESTAURANT
     *
     * @var $orgId         int
     * @var $curIUserOrgId int
     * @var $statuses      array
     */
    public function countForRestaurant(int $orgId, int $curIUserOrgId, array $statuses)
    {

        $temp = (array)json_decode(Yii::$app->getSession()->get('order'));

        if (isset($temp['OrderSearch2']) && is_object($temp['OrderSearch2'])) {
            $temp = (array)$temp['OrderSearch2'];
            foreach (['vendor_id', 'date_from', 'date_to', 'doc_status'] as $item) {
                if (isset($temp[$item])) {
                    $this->searchParams['OrderSearch2'][$item] = $temp[$item];
                }
            }
        }

        // 1. Initialize searchParams
        $i = Yii::$app->request->getQueryParams();
        if (isset($i['OrderSearch2'])) {
            $this->searchParams = $i;
        }

        $this->searchParams['OrderSearch2']['client_id'] = $curIUserOrgId;
        $sp = [];
        foreach ($this->searchParams as $k => $v) {
            if (is_array($v) && $v) {
                foreach ($v as $kk => $vv) {
                    $sp[str_replace('amp;', null, $k)][$kk] = $vv;
                }
            }
        }

        // костыль полный описан в задаче DEV-1425 Фильтры в iiko
        $sp_temp = [];
        foreach ($this->searchParams as $k => $v) {
            if (substr_count($k, 'OrderSearch2') === 1) {
                $sp_temp[substr_count($k, 'amp;')] = $v;
            }
        }
        krsort($sp_temp);
        foreach ($sp_temp as $k => $v) {
            foreach ($v as $kk => $vv) {
                $sp['OrderSearch2'][$kk] = $vv;
            }
        }
        $this->searchParams = $sp;

        Yii::$app->getSession()->set('order', json_encode($this->searchParams));

        // 2. Update counts
        foreach ($this->counts as $key => $val) {
            $this->counts[$key] = Order::find()->where(['client_id' => $orgId])->andWhere(['status' => $statuses[$key]])->count();
        }
        // 3. Detect vendors
        $query = Order::find()->select(['organization.id', 'organization.name'])->where(['client_id' => $orgId])
            ->leftJoin('organization', 'organization.id = order.vendor_id')->groupBy('vendor_id');
        $data = $query->asArray()->all();
        $data[''] = ['id' => '', 'name' => null];
        $this->affiliated = ArrayHelper::map($data, 'id', 'name');
        asort($this->affiliated);
        // 4. Update Totalprice
        $this->totalPrice = Order::find()->where(['status' => $statuses['fulfilled'], 'client_id' => $orgId])->sum("total_price");

    }

    /**
     * Search if $organization->type_id != Organization::TYPE_RESTAURANT
     *
     * @var $orgId         int
     * @var $curIUserOrgId int
     * @var $statuses      array
     * @var $userId        int
     */
    public function countForVendor(int $orgId, int $curIUserOrgId, array $statuses, int $userId)
    {

        // 1. Initialize searchParams

        $temp = (array)json_decode(Yii::$app->getSession()->get('order'));

        if (isset($temp['OrderSearch2']) && is_object($temp['OrderSearch2'])) {
            $temp = (array)$temp['OrderSearch2'];
            foreach (['client_id', 'date_from', 'date_to', 'doc_status'] as $item) {
                if (isset($temp[$item])) {
                    $this->searchParams['OrderSearch2'][$item] = $temp[$item];
                }
            }
        }

        // 1. Initialize searchParams
        $i = Yii::$app->request->getQueryParams();
        if (isset($i['OrderSearch2'])) {
            $this->searchParams = $i;
        }

        $this->searchParams['OrderSearch2']['vendor_id'] = $curIUserOrgId;
        $sp = [];
        foreach ($this->searchParams as $k => $v) {
            if (is_array($v) && $v) {
                foreach ($v as $kk => $vv) {
                    $sp[str_replace('amp;', null, $k)][$kk] = $vv;
                }
            }
        }

        // костыль полный описан в задачах по фильтрам, например, DEV-1425 "Фильтры в iiko"
        $sp_temp = [];
        foreach ($this->searchParams as $k => $v) {
            if (substr_count($k, 'OrderSearch2') === 1) {
                $sp_temp[substr_count($k, 'amp;')] = $v;
            }
        }
        krsort($sp_temp);
        foreach ($sp_temp as $k => $v) {
            foreach ($v as $kk => $vv) {
                $sp['OrderSearch2'][$kk] = $vv;
            }
        }
        $this->searchParams = $sp;

        Yii::$app->getSession()->set('order', json_encode($this->searchParams));

        // 2. Update counts and totalprice - can manage
        if (Yii::$app->user->can('manage')) {
            // 2.1. Update counts - can manage
            foreach ($this->counts as $key => $val) {
                $this->counts[$key] = Order::find()->where(['vendor_id' => $orgId])->andWhere(['status' => $statuses[$key]])->count();
            }
            // 2.2. Update Totalprice - can manage
            $this->totalPrice = Order::find()->where(['status' => $statuses['fulfilled'], 'vendor_id' => $orgId])->sum("total_price");
            // 2.3. Detect Restaurants
            $query = Order::find()->select(['organization.id', 'organization.name'])->where(['vendor_id' => $orgId])
                ->leftJoin('organization', 'organization.id = order.client_id')->groupBy('client_id');
            $data = $query->asArray()->all();
            $data[''] = ['id' => '', 'name' => null];
            $this->affiliated = ArrayHelper::map($data, 'id', 'name');
            asort($this->affiliated);

        } else {

            // 3.0. Setup tablenames - otherwise
            $tblOrder = Order::tableName();
            $tblMA = ManagerAssociate::tableName();
            // 3.1. Update searchParams - otherwise
            $this->searchParams['OrderSearch2']['manager_id'] = $userId;
            // 3.2. Update counts - otherwise
            foreach ($this->counts as $key => $val) {
                $where = ['vendor_id' => $orgId, "$tblMA.manager_id" => $userId, 'status' => $statuses[$key]];
                $this->counts[$key] = Order::find()->leftJoin("$tblMA", "$tblMA.organization_id = $tblOrder.client_id")
                    ->where($where)->count();
            }
            // 3.3. Update Totalprice - otherwise
            $this->totalPrice = Order::find()
                ->leftJoin("$tblMA", "$tblMA.organization_id = $tblOrder.vendor_id")
                ->where(['status' => $statuses['fulfilled'], "$tblMA.manager_id" => $userId, 'vendor_id' => $orgId])
                ->sum("total_price");
            // 3.4. Detect Restaurants
            $query = Order::find()->select(["$tblMA.organization_id", 'organization.name'])
                ->where(['vendor_id' => $orgId, "$tblMA.manager_id" => $userId])
                ->leftJoin("$tblMA", "$tblMA.organization_id = $tblOrder.client_id")
                ->leftJoin('organization', 'organization.id = order.client_id')
                ->groupBy("$tblMA.organization_id");
            $data = $query->asArray()->all();
            $data[''] = ['id' => '', 'name' => null];
            $this->affiliated = ArrayHelper::map($data, 'id', 'name');
            asort($this->affiliated);

        }

    }

    /**
     * Search finalize search after counters are culculated
     *
     * @var $searchModel   OrderSearch2
     * @var $orderStatuses array
     * @var $pagination    array
     * @var $sort          array
     */
    public function finalize(OrderSearch2 $searchModel, array $orderStatuses = [], array $pagination = [], array $sort = [])
    {

        $this->dataProvider = $searchModel->search($this->searchParams, $this->businessType, $orderStatuses, $pagination, $sort);
        if (isset($this->searchParams['page']) && (int)$this->searchParams['page'] > 0) {
            $this->currentPage = $this->searchParams['page'];
        }
        $selectedFromSession = Yii::$app->session->get('selected', []);
        if (isset($selectedFromSession)) {
            $this->selected = $selectedFromSession;
        }
        if (!$this->totalPrice) {
            $this->totalPrice = 0;
        }
    }

    /**
     * Search orders for intergartion views
     *
     * @var $type          string
     * @var $searchModel   OrderSearch2
     * @var $orgId         int
     * @var $curIUserOrgId int
     * @var $wbStatuses    array
     * @var $pagination    array
     * @var $sort          array
     */
    public function getRestaurantIntegration($type, OrderSearch2 $searchModel, int $orgId, int $curIUserOrgId, array $wbStatuses = [], array $pagination = [], array $sort = [])
    {

        // 1. Initialize searchParams
        $this->searchParams = Yii::$app->request->getQueryParams();

        $this->searchParams['OrderSearch2']['client_id'] = $curIUserOrgId;
        $sp = [];
        foreach ($this->searchParams as $k => $v) {
            if (is_array($v) && $v) {
                foreach ($v as $kk => $vv) {
                    $sp[str_replace('amp;', null, $k)][$kk] = $vv;
                }
            }
        }

        // костыль полный описан в задаче DEV-1425 Фильтры в iiko
        $sp_temp = [];
        foreach ($this->searchParams as $k => $v) {
            if (substr_count($k, 'OrderSearch2') === 1) {
                $sp_temp[substr_count($k, 'amp;')] = $v;
            }
        }
        krsort($sp_temp);
        foreach ($sp_temp as $k => $v) {
            foreach ($v as $kk => $vv) {
                $sp['OrderSearch2'][$kk] = $vv;
            }

        }

        $this->searchParams = $sp;
        // 2. Setup business type
        $this->businessType = SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT;

        // 3. Update widget parameters
        $this->dataProvider = $searchModel->searchForIntegration($type, $this->searchParams, $this->businessType, $wbStatuses, $pagination, $sort);

        // 4. Detect vendors
        $query = Order::find()->select(['organization.id', 'organization.name'])->where(['client_id' => $orgId])
            ->leftJoin('organization', 'organization.id = order.vendor_id')->groupBy('vendor_id');
        $data = $query->asArray()->all();
        $data[''] = ['id' => '', 'name' => null];
        $this->affiliated = ArrayHelper::map($data, 'id', 'name');
        asort($this->affiliated);

    }

}