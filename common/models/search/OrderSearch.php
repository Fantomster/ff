<?php

namespace common\models\search;

use api\common\models\RkWaybill;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Order;
use common\models\User;
use common\models\Organization;
use common\models\Profile;
use yii\helpers\ArrayHelper;

/**
 * OrderSearch represents the model behind the search form about `common\models\Order`.
 */
class OrderSearch extends Order {

    public $vendor_search_id = null;
    public $client_search_id = null;
    public $manager_id = null;
    public $status_array;
    public $date_from;
    public $completion_date_from;
    public $date_to;
    public $docStatus;
    public $completion_date_to;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['client_id', 'vendor_id', 'created_by_id', 'accepted_by_id', 'status', 'total_price', 'client_search_id', 'vendor_search_id', 'manager_id'], 'integer'],
            [['created_at', 'updated_at', 'date_from', 'date_to', 'docStatus'], 'safe'],
        ];
    }

//     * @property User $acceptedBy
// * @property Organization $client
// * @property User $createdBy
// * @property Organization $vendor
// * @property OrderContent[] $orderContent
// * @property OrderChat[] $orderChat

    /**
     * @inheritdoc
     */
    public function attributes() {
        return array_merge(parent::attributes(), ['acceptedByProfile.full_name', 'vendor.name', 'client.name', 'createdByProfile.full_name']);
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params) {
        $query = Order::find();
        $this->load($params);


        $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_from . " 00:00:00");
        if ($from) {
            $t1_f = $from->format('Y-m-d H:i:s');
        }
        $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_to . " 00:00:00");
        if ($to) {
            $to->add(new \DateInterval('P1D'));
            $t2_f = $to->format('Y-m-d H:i:s');
        }

        /**
         * Дата завершения заказа
         */
        if(!empty($this->completion_date_from)) {
            $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->completion_date_from . " 00:00:00");
            if ($from) {
                $completion_date_from = $from->format('Y-m-d H:i:s');
            }
        }

        if(!empty($this->completion_date_to)) {
            $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->completion_date_to . " 00:00:00");
            if ($to) {
                $to->add(new \DateInterval('P1D'));
                $completion_date_to = $to->format('Y-m-d H:i:s');
            }
        }
        /**
         * END
         */

        switch ($this->status) {
            case 1: //new
                $this->status_array = [Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT];
                break;
            case 2: //canceled
                $this->status_array = [Order::STATUS_REJECTED, Order::STATUS_CANCELLED];
                break;
            case 3: //processing
                $this->status_array = [Order::STATUS_PROCESSING];
                break;
            case 4: //done
                $this->status_array = [Order::STATUS_DONE];
                break;
        }

        if (!$this->vendor_search_id) {
            $query->joinWith([
                'vendor' => function ($query) {
                    $query->from(Organization::tableName() . ' vendor');
                },
            ]);
        } else {
            $query->joinWith([
                'client' => function ($query) {
                    $query->from(Organization::tableName() . ' client');
                },
            ]);
        }
        $query->joinWith([
            'createdByProfile' => function($query) {
                $query->from(Profile::tableName(). ' createdByProfile');
            },
                ], true);

        $query->joinWith([
            'acceptedByProfile' => function($query) {
                $query->from(Profile::tableName(). ' acceptedByProfile');
            },
                ], true);
        if ($this->manager_id) {
            $maTable = \common\models\ManagerAssociate::tableName();
            $orderTable = Order::tableName();
            $query->rightJoin($maTable, "$maTable.organization_id = `$orderTable`.client_id AND $maTable.manager_id = " . $this->manager_id);
        }
        $query->where(Order::tableName() . '.status != :status', ['status' => Order::STATUS_FORMING]);

        $addSortAttributes = $this->vendor_search_id ? ['client.name'] : ['vendor.name'];
        $addSortAttributes[] = 'createdByProfile.full_name';
        $addSortAttributes[] = 'acceptedByProfile.full_name';
        foreach ($addSortAttributes as $addSortAttribute) {
            $dataProvider->sort->attributes[$addSortAttribute] = [
                'asc' => [$addSortAttribute => SORT_ASC],
                'desc' => [$addSortAttribute => SORT_DESC],
            ];
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            Order::tableName() . '.status' => $this->status_array,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        if (isset($t1_f)) {
            $query->andFilterWhere(['>=', Order::tableName() . '.created_at', $t1_f]);
        }
        if (isset($t2_f)) {
            $query->andFilterWhere(['<=', Order::tableName() . '.created_at', $t2_f]);
        }

        if (isset($completion_date_from)) {
            $query->andFilterWhere(['>=', Order::tableName() . '.completion_date', $completion_date_from]);
        }
        if (isset($completion_date_to)) {
            $query->andFilterWhere(['<=', Order::tableName() . '.completion_date', $completion_date_to]);
        }

        $query->andFilterWhere(['vendor_id' => $this->vendor_id]);
        $query->andFilterWhere(['client_id' => $this->client_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => ['pageSize' => 20],
        ]);
        return $dataProvider;
    }


    /**
     * Creates data provider instance with search query applied for waybill controller (Integration)
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchWaybill($params) {
        //$query = Order::find();

        $query = Order::find()->andWhere(['status' => Order::STATUS_DONE])
            ->andWhere(['client_id' => User::findOne(Yii::$app->user->id)->organization_id]);

        $this->load($params);

        $filter_date_from = strtotime($this->date_from);
        $filter_date_to = strtotime($this->date_to);

        $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_from . " 00:00:00");
        if ($from) {
            $t1_f = $from->format('Y-m-d H:i:s');
        }
        $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_to . " 00:00:00");
        if ($to) {
            $to->add(new \DateInterval('P1D'));
            $t2_f = $to->format('Y-m-d H:i:s');
        }

        switch ($this->status) {
            case 1: //new
                $this->status_array = [Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT];
                break;
            case 2: //canceled
                $this->status_array = [Order::STATUS_REJECTED, Order::STATUS_CANCELLED];
                break;
            case 3: //processing
                $this->status_array = [Order::STATUS_PROCESSING];
                break;
            case 4: //done
                $this->status_array = [Order::STATUS_DONE];
                break;
        }

        if (!$this->vendor_search_id) {
            $query->joinWith([
                'vendor' => function ($query) {
                    $query->from(Organization::tableName() . ' vendor');
                },
            ]);
        } else {
            $query->joinWith([
                'client' => function ($query) {
                    $query->from(Organization::tableName() . ' client');
                },
            ]);
        }
/*
        $nacl = RkWaybill::findOne(['order_id' => $data->id]);

        //    var_dump($nacl->id);
        if (isset($nacl->status)) {
            return $nacl->status->denom;
        }  else {
            return 'Не сформирована';
        }

*/
        // $query ->innerJoin('rk_waybill', 'rk_waybill.order_id = order.id');

    $nacl = null;
    $naclInternal = array();

        switch ($this->docStatus) {
            case 1: //new
                $nacl = \api\common\models\RkWaybill::find()->select('order_id')->asArray()->all();
                foreach ($nacl as $value) {
                    foreach($value as $idd) {
                        $naclInternal[] = $idd;
                    }
                }
                $query->andWhere([  'NOT IN', 'order.id', $naclInternal ]);

                break;
            case 2: //ready
                $nacl = \api\common\models\RkWaybill::find()->select('order_id')->andWhere('status_id = 1')->asArray()->all();
                foreach ($nacl as $value) {
                    foreach($value as $idd) {
                        $naclInternal[] = $idd;
                    }
                }
                $query->andWhere([  'IN', 'order.id', $naclInternal ]);
                break;
            case 3: //done
                $nacl = \api\common\models\RkWaybill::find()->select('order_id')->andWhere('status_id = 2')->asArray()->all();
                foreach ($nacl as $value) {
                    foreach($value as $idd) {
                        $naclInternal[] = $idd;
                    }
                }
                $query->andWhere([  'IN', 'order.id', $naclInternal ]);
                break;

        }



      // var_dump($nacl);
      // die();

        /*
        $query->joinWith([
            'createdByProfile' => function($query) {
                $query->from(Profile::tableName(). ' createdByProfile');
            },
        ], true);

        $query->joinWith([
            'acceptedByProfile' => function($query) {
                $query->from(Profile::tableName(). ' acceptedByProfile');
            },
        ], true);

        if ($this->manager_id) {
            $maTable = \common\models\ManagerAssociate::tableName();
            $orderTable = Order::tableName();
            $query->rightJoin($maTable, "$maTable.organization_id = `$orderTable`.client_id AND $maTable.manager_id = " . $this->manager_id);
        }
        $query->where(Order::tableName() . '.status != :status', ['status' => Order::STATUS_FORMING]);
         */

        $addSortAttributes = $this->vendor_search_id ? ['client.name'] : ['vendor.name'];
        $addSortAttributes[] = 'createdByProfile.full_name';
        $addSortAttributes[] = 'acceptedByProfile.full_name';
        foreach ($addSortAttributes as $addSortAttribute) {
            $dataProvider->sort->attributes[$addSortAttribute] = [
                'asc' => [$addSortAttribute => SORT_ASC],
                'desc' => [$addSortAttribute => SORT_DESC],
            ];
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            Order::tableName() . '.status' => $this->status_array,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        if (isset($t1_f)) {
            $query->andFilterWhere(['>=', Order::tableName() . '.updated_at', $t1_f]);
        }
        if (isset($t2_f)) {
            $query->andFilterWhere(['<=', Order::tableName() . '.updated_at', $t2_f]);
        }

        $query->andFilterWhere(['vendor_id' => $this->vendor_id]);
        $query->andFilterWhere(['client_id' => $this->client_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => ['pageSize' => 20],
        ]);
        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied for waybill controller (Integration)
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
     /*
    public function searchWaybill($params) {
       
        $query = Order::find()->andWhere(['status' => Order::STATUS_DONE])
                ->andWhere(['client_id' => User::findOne(Yii::$app->user->id)->organization_id]);
        
        $this->load($params);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        var_dump('Bugaga', $params);
       // die();

        return $dataProvider;

    }*/

}
