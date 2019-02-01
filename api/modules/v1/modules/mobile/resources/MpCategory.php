<?php

namespace api\modules\v1\modules\mobile\resources;

use common\helpers\DBNameHelper;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class MpCategory extends \common\models\MpCategory
{
    public $language;
    public $empty;

    public function fields()
    {
        return ['id', 'parent', 'name', 'language', 'empty'];
    }

    public function rules()
    {
        return [
            [['parent'], 'integer'],
            [['name', 'language', 'empty'], 'string', 'max' => 255],
        ];
    }

    public function getCountProducts($category_id = null)
    {
        $category_id = ($category_id == null) ? $this->id : $category_id;
        $categories = self::getCategories($category_id);
        $categories = implode(",", $categories);

        $res = 0;
        $user = \Yii::$app->user->getIdentity();
        $client = $user->organization;

        $subQuery1 = (new \yii\db\Query())
            ->select('cat_id')
            ->from(\common\models\RelationSuppRest::tableName())
            ->where("(supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)");
        $query1 = (new \yii\db\Query())
            ->select('cbg.id as id')
            ->from(DBNameHelper::getMainName() . '.' . CatalogBaseGoods::tableName() . ' as cbg')
            ->where("(cbg.status = 1) 
                AND (cbg.deleted = 0) AND (cbg.category_id in ($categories))")
            ->andWhere(['cbg.cat_id' => $subQuery1]);

        $query2 = (new \yii\db\Query())
            ->select('cbg.id as id')
            ->from(DBNameHelper::getMainName() . '.' . CatalogBaseGoods::tableName() . ' as cbg')
            ->leftJoin(DBNameHelper::getMainName() . '.' . CatalogGoods::tableName() . ' as cg', 'cg.base_goods_id = cbg.id')
            ->where("(cbg.status = 1) 
                AND (cbg.deleted = 0) AND (cbg.category_id in ($categories))")
            ->andWhere(['cg.cat_id' => $subQuery1]);

        $res = (new \yii\db\Query())
            ->select('count(*)')
            ->from(['tb1' => $query1->union($query2)])
            ->count();
        return $res;
    }

    public static function getCategories($cat_id)
    {
        $res = [];
        $cats = MpCategory::find()->where(["parent" => $cat_id])->all();
        foreach ($cats as $cat) {
            $res[] = $cat->id;
            $res = array_merge($res, self::getCategories($cat->id));
        }

        return $res;
    }

}
