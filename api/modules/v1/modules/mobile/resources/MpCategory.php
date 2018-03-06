<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class MpCategory extends \common\models\MpCategory
{
    public function fields()
    {
        return ['id', 'parent', 'name'];
    }
    
    public function rules()
    {
        return [
            [['parent'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function getCountProducts($category_id = null)
    {
       $category_id = ($category_id == null ) ? $this->id : $category_id;
       $categories = self::findall(['parent' => $category_id]);
       $res = 0;
       $res += \common\models\CatalogBaseGoods::find()
           ->where(['category_id'=>$category_id])
           ->count();
       foreach ($categories as $category)
           $res += $this->getCountProducts($category->id);

       return $res;
    }
}
