<?php

namespace common\models\ES;

use yii\elasticsearch\ActiveRecord;
use Yii;

class Supplier extends ActiveRecord
{  
    const MAX_RATING = \common\models\Organization::MAX_RATING;
    
    public static function index() 
    {  
        return 'supplier'; 
    } 
    public static function type() 
    {  
        return 'supplier';  
    }

    public function attributes()
    {
        return [            
            "supplier_id",
            "supplier_name",
            "supplier_image",
            "supplier_rating", 
            "supplier_partnership",
          ];
      }
    public function rules() 
    {   
        return [
            [$this->attributes(), 'safe']  
        ];
    }
}