<?php

namespace api\common\models\iiko\search;

use api\common\models\iiko\iikoProduct;
use api\common\models\iiko\iikoService;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class iikoProductSearch extends iikoProduct
{

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
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = iikoProduct::find();

        if (isset($params['productSearch']) && $params['productSearch'] != 'all') {
            $query->andWhere([
                'product_type' => $params['productSearch']
            ]);
        }

        if (isset($params['cookingPlaceSearch']) && $params['cookingPlaceSearch'] != 'all') {
            if ($params['cookingPlaceSearch'] == '') {
                $params['cookingPlaceSearch'] = null;
            }
            $query->andWhere([
                'cooking_place_type' => $params['cookingPlaceSearch']
            ]);
        }

        if (isset($params['unitSearch']) && $params['unitSearch'] != 'all') {
            $query->andWhere([
                'unit' => $params['unitSearch']
            ]);
        }

        if (isset($params['org_id'])) {
            $query->andWhere([
                'org_id' => $params['org_id']
            ]);
        }

        if (isset($params['is_active'])) {
            $query->andWhere([
                'is_active' => $params['is_active']
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        return $dataProvider;
    }

    public function getProductType()
    {
        $rows = (new \yii\db\Query())
            ->select(['product_type'])
            ->distinct()
            ->from('iiko_product')
            ->all(\Yii::$app->get('db_api'));
        $arr = [];
        foreach ($rows as $row) {
            $arr[$row['product_type']] = $row['product_type'];
        }
        return $arr;
    }

    public function getCoockingPlaceType()
    {
        $rows = (new \yii\db\Query())
            ->select(['cooking_place_type'])
            ->distinct()
            ->from('iiko_product')
            ->all(\Yii::$app->get('db_api'));
        $arr = [];
        foreach ($rows as $row) {
            $arr[$row['cooking_place_type']] = $row['cooking_place_type'];
        }
        return $arr;
    }

    public function getUnit()
    {
        $rows = (new \yii\db\Query())
            ->select(['unit'])
            ->distinct()
            ->from('iiko_product')
            ->all(\Yii::$app->get('db_api'));
        $arr = [];
        foreach ($rows as $row) {
            $arr[$row['unit']] = $row['unit'];
        }
        return $arr;
    }
}
