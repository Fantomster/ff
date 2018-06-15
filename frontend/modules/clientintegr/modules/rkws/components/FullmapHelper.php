<?php
namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\RkServicedata;
use yii;
use api\common\models\RkSession;
use api\common\models\RkAccess;
use api\common\models\RkService;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use common\models\User;
use api\common\models\RkTasks;
use yii\helpers\VarDumper;

use yii\base\Object;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class FullmapHelper extends yii\base\BaseObject  {

    public $org;
    public $restr;
    
    public function init() {

        if (Yii::$app->user->isGuest)
            return;

        if(isset(User::findOne(Yii::$app->user->id)->organization_id))
        $this->org = User::findOne(Yii::$app->user->id)->organization_id;
        
        if (isset($this->org))
        $this->restr = RkServicedata::find()->andwhere('org = :org',[':org' => $this->org])->one();
                
       
    }

    public function getcats() {

        echo "getcats here";

    }

    
    
}