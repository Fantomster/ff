<?php


namespace common\components;

use common\models\ManagerAssociate;
use common\models\Order;
use common\models\search\OrderSearch2;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Special service component for Customer-type user's `order searching` needs
 * @createdBy Basil A Konakov
 * @createdAt 2018-08-14
 * @author Mixcart
 * @module Frontend
 * @version 1.0
 */

class SearchOrdersComponent extends Component
{

    public $affiliated = [];
  /** @var $searchParams array */
    public $searchParams = [];
    /** @var $counts array */
    public $counts = [
        'new' => 0,
        'processing' => 0,
        'fulfilled' => 0,
        // 'stopped' => 0, // @todo проверить потребность в счетчике
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

    /**
     * Search if $organization->type_id == Organization::TYPE_RESTAURANT
     *
     * @var $orgId int
     * @var $curIUserOrgId int
     * @var $statuses array
     */
    public function countForRestaurant(int $orgId, int $curIUserOrgId, array $statuses)
    {

        // 1. Initialize searchParams
        $this->searchParams = Yii::$app->request->getQueryParams();
        $this->searchParams['OrderSearch2']['client_id'] = $curIUserOrgId;
        // 2. Update counts
        foreach ($this->counts as $key => $val) {
            $this->counts[$key] = Order::find()->where(['client_id' => $orgId])->andWhere(['status' => $statuses[$key]])->count();
        }
        // 3. Detect vendors
        $query = Order::find()->select(['organization.id', 'organization.name'])->where(['client_id' => $orgId])
            ->leftJoin('organization', 'organization.id = order.vendor_id')->groupBy('vendor_id');
        $data = $query->asArray()->all();
        $data[''] = ['id' => '', 'name' => NULL];
        $this->affiliated = ArrayHelper::map($data, 'id', 'name');
        asort($this->affiliated);
        // 4. Update Totalprice
        $this->totalPrice = Order::find()->where(['status' => $statuses['fulfilled'], 'client_id' => $orgId])->sum("total_price");

    }

    /**
     * Search if $organization->type_id != Organization::TYPE_RESTAURANT
     *
     * @var $orgId int
     * @var $curIUserOrgId int
     * @var $statuses array
     * @var $userId int
     */
    public function countForOthers(int $orgId, int $curIUserOrgId, array $statuses, int $userId)
    {

        // 1. Initialize searchParams
        $this->searchParams = Yii::$app->request->getQueryParams();
        $this->searchParams['OrderSearch2']['vendor_id'] = $curIUserOrgId;

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
            $data[''] = ['id' => '', 'name' => NULL];
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
                $this->counts[$key] = Order::find()->leftJoin("$tblMA", "$tblMA.organization_id = `$tblOrder`.client_id")
                    ->where($where)->count();
            }
            // 3.3. Update Totalprice - otherwise
            $this->totalPrice = Order::find()
                ->where(['status' => $statuses['fulfilled'], "$tblMA.manager_id" => $userId, 'vendor_id' => $orgId])
                ->sum("total_price");
            // 3.4. Detect Restaurants
            $query = Order::find()->select(["$tblMA.organization_id", 'organization.name'])
                ->where(['vendor_id' => $orgId, "$tblMA.manager_id" => $userId])
                ->leftJoin("$tblMA", "$tblMA.organization_id = `$tblOrder`.client_id")
                ->leftJoin('organization', 'organization.id = order.client_id')
                ->groupBy("$tblMA.organization_id");
            $data = $query->asArray()->all();
            $data[''] = ['id' => '', 'name' => NULL];
            $this->affiliated = ArrayHelper::map($data, 'id', 'name');
            asort($this->affiliated);



        }

    }

    /**
     * Search if $organization->type_id != Organization::TYPE_RESTAURANT
     * @var $searchModel OrderSearch2
     * @var $orderStatuses array
     * @var $pagination array
     * @var $sort array
     */
    public function finalize(OrderSearch2 $searchModel, array $orderStatuses = [], array $pagination = [], array $sort = [])
    {

        $this->dataProvider = $searchModel->search($this->searchParams, $this->businessType, $orderStatuses, $pagination, $sort);
        if (isset($this->searchParams['page']) && (int)$this->searchParams['page'] > 0) {
            $this->currentPage = $this->searchParams['page'];
        }
        $selectedFromSession = Yii::$app->session->get('selected', []);
        if (isset($selectedFromSession[$this->currentPage])) {
            $this->selected = $selectedFromSession[$this->currentPage];
        }
        if(!$this->totalPrice) {$this->totalPrice = 0;}
    }

}