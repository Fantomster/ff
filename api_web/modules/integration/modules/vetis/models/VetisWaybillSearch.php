<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 8/31/2018
 * Time: 1:51 PM
 */

namespace api_web\modules\integration\modules\vetis\models;


use api\common\models\merc\mercDicconst;
use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
use api\common\models\merc\MercVsd;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class VetisWaybillSearch extends MercVsd
{
    public $from;
    public $to;
    public $acquirer_id;

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = (new VetisHelper())->getListQuery();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (empty($params)) {
            return $dataProvider;
        }

        foreach ($params as $key => $param) {
            if ($key == 'from') {
                $start_date = date('Y-m-d 00:00:00', strtotime($this->from));
            } elseif ($key == 'to') {
                $end_date = date('Y-m-d 23:59:59', strtotime($this->to));
            } elseif ($key == 'product_name') {
                $query->andFilterWhere(['like', 'm.product_name', $this->product_name]);
            } elseif ($key == 'acquirer_id') {
                $enterprise_guid = mercDicconst::getSetting('enterprise_guid', $this->{$key});
                $query->andFilterWhere([
                    'OR',
                    ['m.recipient_guid' => $enterprise_guid],
                    ['m.sender_guid' =>  $enterprise_guid]
                ]);
            } else {
                $query->andFilterWhere(['m.' . $key => $this->{$key}]);
            }
        }

        if (isset($start_date) && isset($end_date)) {
            $query->andFilterWhere(['between', 'm.date_doc', $start_date, $end_date]);
        }

        return $dataProvider;
    }
}