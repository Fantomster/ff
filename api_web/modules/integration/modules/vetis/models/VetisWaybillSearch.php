<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 8/31/2018
 * Time: 1:51 PM
 */

namespace api_web\modules\integration\modules\vetis\models;


use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercVsd;
use yii\base\Model;
use yii\data\ArrayDataProvider;

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
     * @return ArrayDataProvider
     */
    public function search($params)
    {
        $query = (new VetisHelper)->getQueryByUuid();
        
        $dataProvider = new ArrayDataProvider([
            'query' => $query,
        ]);

        if(empty($params)){
            return $dataProvider;
        }

        foreach ($params as $key => $param) {
            if ($key == 'from') {
                $start_date = date('Y-m-d 00:00:00', strtotime($this->from));
            } elseif ($key == 'to'){
                $end_date = date('Y-m-d 23:59:59',strtotime($this->to));
            } elseif ($key == 'product_name'){
                $query->andFilterWhere(['like', 'product_name', $this->product_name]);
            } else {
                $query->andWhere([$key => $this->{$param}]);
            }
        }

        if (isset($start_date) && isset($end_date)) {
            $query->andFilterWhere(['between', 'date_doc', $start_date, $end_date]);
        }
        
        return $dataProvider;
    }

    /*public function getRecipientList()
    {
        $guid = mercDicconst::getSetting('enterprise_guid');
        if($this->type == 1)
            return array_merge(['' => 'Все'], ArrayHelper::map(MercVsd::find()->where("recipient_guid = '$guid'")->groupBy('sender_guid')->all(), 'sender_guid', 'sender_name'));
        else
            return array_merge(['' => 'Все'], ArrayHelper::map(MercVsd::find()->where("sender_guid = '$guid'")->groupBy('recipient_guid')->all(), 'recipient_guid', 'recipient_name'));
    }*/
}