<?php

namespace common\models\ES;

use yii\elasticsearch\ActiveRecord;
use Yii;

class Supplier extends ActiveRecord
{  
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
            "supplier_image" 
          ];
      }
    public function rules() 
    {   
        return [
            [$this->attributes(), 'safe']  
        ];
    }
}