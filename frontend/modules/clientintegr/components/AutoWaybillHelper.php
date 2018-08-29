<?php
/**
 * Created by PhpStorm.
 * User: xsupervisor
 * Date: 28.08.2018
 * Time: 19:41
 */

use yii\helpers\ArrayHelper;
use common\models\AllService;
use frontend\modules\clientintegr\modules\rkws\components\AutoWaybillHelperRkws;

class AutoWaybillHelper extends \yii\base\Component
{

    public function processWaybill() {

    foreach ($this->getServiceList() as $key => $val) {


    }

    }


    public function processRkws() {

        $rkws = new AutoWaybillHelperRkws();

        $mode = $rkws->checkMode();

        var_dump($mode);
        die();
    }

    public function getServiceList() { // return list od service IDs with type client integration
    return ArrayHelper::map(AllService::find()->andWhere('type_id = 1')->all(),'id', 'denom' );
    }

}