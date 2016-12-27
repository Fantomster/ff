<?php

use yii\elasticsearch\ActiveRecord;

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
            "first_name",
            "last_name",
            "age" ,
            "about" ,
            "interests" 
          ];
      }
    public function rules() 
    {   
        return [
            [$this->attributes(), 'safe']  
        ];
    }
}