<?php


namespace api\common\models;

use Yii;
use \common\models\Organization;

/**
 * User model
 * Reroute to API Database
 * 
 */

class Organization_api extends Organization {
    
    
    public static function model($className = __CLASS__) 
    {
        return parent::model($className);
    }    
    
    
    public static function getDb()
    {
       return \Yii::$app->db_api;
    }

}
