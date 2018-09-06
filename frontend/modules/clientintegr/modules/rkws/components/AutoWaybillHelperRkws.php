<?php
namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\AllMaps;
use api\common\models\RabbitJournal;
use api\common\models\RkPconst;
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


class AutoWaybillHelperRkws extends yii\base\BaseObject  {

    public $org;

    public function init() {

        if (Yii::$app->user->isGuest)
            return;

        if(isset(User::findOne(Yii::$app->user->id)->organization_id))
        $this->org = User::findOne(Yii::$app->user->id)->organization_id;

       
    }

    public function checkMode() {

        $mode = (RkDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue() != null) ?
            RkDicconst::findOne(['denom' => '\'auto_unload_invoice'])->getPconstValue() : 0;

    }

    
}