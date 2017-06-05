<?php


namespace api\common\models;

use Yii;

/**
 * User model
 * Reroute to API Database
 * 
 */

class UserToken extends \amnah\yii2\user\models\UserToken {
    
    
    public static function model($className = __CLASS__) 
    {
        return parent::model($className);
    }    
    
    
    public static function getDb()
    {
       return \Yii::$app->db_api;
    }

}
