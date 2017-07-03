<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\RkAccess;
use yii;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ApiHelper  {
    
public function processCmd() {
    
     $url = "http://ws-w01m.ucs.ru/WSClient/api/Client/Cmd";
     
     $org = Yii::$app->db->createCommand('select organiztion_id from user where id ='.Yii::$app->user->id)
      ->queryScalar();   
     
     
     
//     $restr = RkAccess::find()->andWhere($url)  "199990046";
    
    
   // return strlen($text);
   
}

    
}

