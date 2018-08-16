<?php

namespace common\models\search;

use common\models\Order;
use common\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use Yii;
use common\components\SearchOrdersComponent;

/**
 * OrderSearch represents the model behind the search form about `common\models\Order`.
 */
class OrderSearch2 extends Order
{

    public $date_from;
    public $date_to;
    public $doc_status;

    public function rules(): array
    {
        return [
            [['id', 'client_id', 'vendor_id', 'doc_status',], 'integer'],
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

        if (isset($params['OrderSearch']['id']) && (int)$params['OrderSearch']['id'] > 0) {
            $query = Order::find()->where(['id' => (int)$params['OrderSearch']['id']])
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
            'query' => $query
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