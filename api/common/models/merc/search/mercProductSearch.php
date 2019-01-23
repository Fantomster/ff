<?php

namespace api\common\models\merc\search;

use api\common\models\merc\mercDicconst;
use common\models\vetis\VetisPackingType;
use common\models\vetis\VetisProductItem;
use common\models\vetis\VetisUnit;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class mercProductSearch extends VetisProductItem
{
    public $from_create_date;
    public $to_create_date;
    public $unit;
    public $packagingType;

    public function rules()
    {
         return [
            [['last', 'active', 'status', 'productType', 'correspondsToGost', 'packagingQuantity'], 'integer'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'code', 'globalID', 'product_uuid', 'product_guid', 'subproduct_uuid',
                'subproduct_guid', 'gost', 'producer_uuid', 'producer_guid', 'tmOwner_uuid', 'tmOwner_guid',
                'packagingType_guid', 'packagingType_uuid', 'unit_uuid', 'unit_guid'], 'string', 'max' => 255],
            [['createDate', 'updateDate', 'packagingType_guid', 'packagingType_uuid', 'unit_uuid', 'unit_guid', 'packagingQuantity', 'packagingVolume'], 'safe'],
            [['unit','packagingType', 'from_create_date', 'to_create_date'], 'safe'],
        ];
    }

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
        $guid = mercDicconst::getSetting('issuer_id');
        $productTable = VetisProductItem::tableName();
        $unitTable = VetisUnit::tableName();
        $packingTypeTable = VetisPackingType::tableName();

        $query = VetisProductItem::find()->where(['producer_guid' => $guid, $productTable.'.last' => true, $productTable.'.active' => true]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC,
                ]
            ],
        ]);

        $query->select(
            "$productTable.uuid, $productTable.guid, $productTable.name, $productTable.globalID, $productTable.code, $productTable.createDate, $productTable.status,
            $unitTable.name as unit, $packingTypeTable.name as packagingType")
            ->from($productTable)
            ->leftJoin($unitTable, "$unitTable.uuid = $productTable.unit_uuid")
            ->leftJoin($packingTypeTable, "$packingTypeTable.uuid = $productTable.packagingType_uuid");

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        if ( !empty($this->from_create_date) && !empty($this->to_create_date)) {
        $start_date = date('Y-m-d 00:00:00',strtotime($this->from_create_date));
        $end_date = date('Y-m-d 23:59:59',strtotime($this->to_create_date));
            $query->andFilterWhere(['between', "STR_TO_DATE( $productTable.createDate, '%Y-%c-%e %H:%i:%s')", $start_date, $end_date]);
        }

        $query->andFilterWhere(['like', $productTable.'.name', $this->name]);
        $query->andFilterWhere(['like', $productTable.'.globalID', $this->globalID]);
        $query->andFilterWhere(['like', 'code', $this->code]);
        $query->andFilterWhere(['packagingType_uuid' => $this->packagingType_uuid]);
        $query->andFilterWhere(['unit_uuid' => $this->unit_uuid]);
        $query->andFilterWhere([$productTable.'.status' => $this->status]);

        return $dataProvider;
    }
}
