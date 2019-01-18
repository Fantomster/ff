<?php
namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\AllMaps;
use api\common\models\RabbitJournal;
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

    const LIMIT = 100;
    
    public function init() {

        if (Yii::$app->user->isGuest)
            return;

        if(isset(User::findOne(Yii::$app->user->id)->organization_id))
        $this->org = User::findOne(Yii::$app->user->id)->organization_id;
        
        if (isset($this->org))
        $this->restr = RkServicedata::find()->andwhere('org = :org',[':org' => $this->org])->one();
                
       
    }

    public function getcats() {

        $sql = "SELECT count(*) FROM catalog_goods a left join catalog_base_goods b on b.id = a.base_goods_id
                left join relation_supp_rest c on a.cat_id = c.cat_id
                where a.cat_id IN
                                  (SELECT cat_id FROM relation_supp_rest
                                  where rest_org_id = ".$this->org." and deleted = 0)
                                  LIMIT 5";

        $newProdscount = Yii::$app->db->createCommand($sql)->queryScalar();

        if ( $newProdscount == 0)
            return false;

        $jmodel = new RabbitJournal();

        $jmodel->org_id = $this->org;
        $jmodel->action = 'fullmap';
        $jmodel->total_count = $newProdscount;
        $jmodel->success_count = 0;
        $jmodel->fail_count = 0;

        if (!$jmodel->save()) {
            echo "Cant save rabbit journal model";
            var_dump($jmodel->getErrors());
            die();
        }

        $offset = 0;
        $counter = 0;

        do {

            $sql = "SELECT a.*, b.product as product_name, c.supp_org_id as supp_id FROM catalog_goods a left join catalog_base_goods b on b.id = a.base_goods_id
                left join relation_supp_rest c on a.cat_id = c.cat_id
                where a.cat_id IN
                                  (SELECT cat_id FROM relation_supp_rest
                                  where rest_org_id = ".$this->org." and deleted = 0)
                                  LIMIT ".self::LIMIT." OFFSET ".$offset;

        $newProds = Yii::$app->db->createCommand($sql)->queryAll();

 //       $counter = 0;
            $counter = $counter + count($newProds);


        foreach ($newProds as $prod) {

            $mess['action'] = 'fullmap';
            $mess['id'] = $jmodel->id;

            $mess['body'] = [
                'service_id' => 1,
                'supp_id' => $prod['supp_id'],
                'cat_id' => $prod['cat_id'],
                'product_id' => $prod['base_goods_id'],
                'org_id' => $this->org,
                'koef' => 1,
                'is_active' => 1,
            ];

            //\Yii::$app->rkwsmq->addRabbitQueue(serialize($mess));

            try {
                \Yii::$app->get('rabbit')
                    ->setQueue('rkws')
                    ->setExchange('router')
                    ->addRabbitQueue(serialize($mess));
            } catch(\Exception $e) {
                Yii::error($e->getMessage());
            }

        }

            $offset += self::LIMIT;

        } while (count($newProds) == self::LIMIT || $counter == 2000);


        }

    
}