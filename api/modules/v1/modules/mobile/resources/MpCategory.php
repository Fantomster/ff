<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class MpCategory extends \common\models\MpCategory
{
    public $language;
    public function fields()
    {
        return ['id', 'parent', 'name','language'];
    }
    
    public function rules()
    {
        return [
            [['parent'], 'integer'],
            [['name','language'], 'string', 'max' => 255],
        ];
    }

    public function getCountProducts($category_id = null)
    {
       $category_id = ($category_id == null ) ? $this->id : $category_id;
       $categories = $this->getCategories($category_id);
       $categories = implode(",", $categories);

       $res = 0;
        $user = \Yii::$app->user->getIdentity();
        $client = $user->organization;

        $query1 = "
            SELECT  cbg.id as id
            FROM catalog_base_goods as cbg
            WHERE (cbg.status = 1) 
                AND (cbg.deleted = 0) AND (cbg.category_id in ($categories))  
                AND (cbg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)))
                ";

        $query2 = "SELECT cbg.id as id
            FROM catalog_base_goods AS cbg 
                    LEFT JOIN catalog_goods AS cg ON cg.base_goods_id = cbg.id
            WHERE (cbg.status = 1) 
                AND (cbg.deleted = 0) AND (cbg.category_id in ($categories)) 
                AND (cg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)))
                ";

        $sql = "SELECT count(*) FROM($query1  UNION ALL ($query2)) as tbl";

        $connection = \Yii::$app->getDb();
        $command = $connection->createCommand($sql);
        $res = $command->queryScalar();
       return $res;
    }

    private function getCategories($cat_id) {
        $res = [];
        $cats = MpCategory::find()->where(["parent" => $cat_id])->all();
        foreach ($cats as $cat) {
            $res[] = $cat->id;
            $res = array_merge($res, $this->getCategories($cat->id));
        }

        return $res;
    }


}
