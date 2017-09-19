<?php

namespace common\models\ES;

use yii\elasticsearch\ActiveRecord;
use Yii;

class Category extends ActiveRecord
{  
    public static function index() 
    {  
        return 'category'; 
    } 
    public static function type() 
    {  
        return 'category';  
    }

    public function attributes()
    {
        return [            
            "category_id",
            "category_slug",
            "category_sub_id",
            "category_name"
          ];
      }
    public function rules() 
    {   
        return [
            [$this->attributes(), 'safe']  
        ];
    }
}