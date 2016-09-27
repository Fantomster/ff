<?php

namespace common\models\search;

use yii\data\ActiveDataProvider;
use common\models\CatalogGoods;
use common\models\CatalogBaseGoods;
use common\models\Organization;

/**
 *  Model for order catalog search form
 */
class OrderCatalogSearch extends CatalogGoods {
    public $searchString;
    public $vendors;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "{{%catalog_goods}}";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['baseProduct.product', 'vendor.name', 'price', 'baseProduct.units', 'note', 'searchString'], 'safe'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), ['baseProduct.product', 'vendor.name', 'baseProduct.units']);
    }
    
    /**
     * Search
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $productTable = CatalogGoods::tableName();
        $baseProductTable = CatalogBaseGoods::tableName();
        $organizationTable = Organization::tableName();
        
        $catalogs = [];
        foreach ($this->vendors as $vendor) {
            if ($vendor['selected']) {
                $catalogs[] = $vendor['cat_id'];
            }
        }

        $query = CatalogGoods::find();
        
        $query->joinWith(['baseProduct' => function ($query) use ($baseProductTable) {
            $query->from(['baseProduct' => $baseProductTable]);
        }]);
//исправить ебанутый запрос с лишним джойном и лишними полями
        $query->joinWith('organization');
        $query->where([
            $productTable.'.cat_id' => $catalogs,
            $baseProductTable.'.deleted' => 0,
        ]);

        // create data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // enable sorting for the related columns
        $addSortAttributes = ['baseProduct.product', 'organization.name', 'price', 'baseProduct.units'];
        foreach ($addSortAttributes as $addSortAttribute) {
            $dataProvider->sort->attributes[$addSortAttribute] = [
                'asc' => [$addSortAttribute => SORT_ASC],
                'desc' => [$addSortAttribute => SORT_DESC],
            ];
        }

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->orFilterWhere(['like', $baseProductTable.'.product', $this->searchString])
            ->orFilterWhere(['like', $organizationTable.'.name', $this->searchString]);
//        $query->andWhere([
//            'cat_id' => $catalogs,
//        ]);
        return $dataProvider;
    }
    
}
