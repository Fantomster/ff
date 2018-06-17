<?php
namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\AllMaps;
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

        $sql = "SELECT a.*, b.product as product_name, c.supp_org_id as supp_id FROM `catalog_goods` a left join `catalog_base_goods` b on b.id = a.base_goods_id
                left join `relation_supp_rest` c on a.cat_id = c.cat_id
                where a.cat_id IN
                                  (SELECT cat_id FROM `relation_supp_rest`
                                  where rest_org_id = ".$this->org." and deleted = 0)";

        $newProds = Yii::$app->db->createCommand($sql)->queryAll();

        $counter = 0;

        foreach ($newProds as $prod) {

          $ch = AllMaps::find()->andWhere(["product_id" => $prod['base_goods_id']])->one();

          if(!empty($ch))
              continue;

          $model = new AllMaps();

          $model->service_id = 1;
          $model->supp_id = $prod["supp_id"];
          $model->cat_id = $prod["cat_id"];
          $model->product_id = $prod["base_goods_id"];
          $model->org_id = $this->org;
          $model->koef = 1;
          $model->is_active = 1;

          if (!$model->save()) {
              echo "Can't save catalog model";
              die();
          }

           $counter++;

        }

        return $counter;
    }

    
    
}