<?php

namespace api\common\models;

use Aws\Ec2\Iterator\DescribeInstancesIterator;
use common\models\Order;
use common\models\User;

use Yii;
use common\models\OrderContent;
// use common\models\User;

/**
 * This is the model class for table "rk_access".
 *
 * @property integer $id
 * @property integer $fid
 * @property integer $org
 * @property string $login
 * @property string $password
 * @property string $token
 * @property string $lic
 * @property datetime $fd
 * @property datetime $td
 * @property integer $ver
 * @property integer $locked
 * @property string $usereq 
 * @property string $comment
 * @property string $salespoint
 * 
 * 
 */
class RkWaybill extends \yii\db\ActiveRecord {

    const STATUS_UNLOCKED = 0;
    const STATUS_LOCKED = 1;
    


    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'rk_waybill';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['order_id', 'doc_date', 'corr_rid'], 'required'],
            [['corr_rid', 'store_rid', 'status_id','num_code'], 'integer'],
            [['store_rid'], 'number', 'min' => 0],
                //     [['comment'], 'string', 'max' => 255],
            [['store_rid', 'org','vat_included','text_code','num_code','note'],'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'order_id' => 'Заказ',
            'corr_rid' => 'Контрагент',
            'store_rid' => 'Склад',
            'doc_date' => 'Дата документа',
            'note' => 'Примечание',
            'text_code' => 'Код текст',
            'num_code' => 'Код цифр.'
        ];
    }

    public static function getStatusArray() {
        return [
            RkAccess::STATUS_UNLOCKED => 'Активен',
            RkAccess::STATUS_LOCKED => 'Отключен',
        ];
    }

    public function getCorr() {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        return RkAgent::find()->andWhere('rid = :corr_rid and acc = :acc', [':corr_rid' => $this->corr_rid, ':acc' => Yii::$app->user->identity->organization_id])->one();

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }

    public function getStore() {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        return RkStoretree::find()->andWhere('id = :store_rid and acc = :acc', [':store_rid' => $this->store_rid, ':acc' => Yii::$app->user->identity->organization_id])->one();

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }
    
    public function getStatus() {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        return RkWaybillstatus::find()->andWhere('id = :id', [':id' => $this->status_id])->one();

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }

    public function getOrder() {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        return Order::find()->andWhere('id = :id', [':id' => $this->order_id])->one();

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);
    }

    public function getFinalDate() {

        $fdate = $this->order->actual_delivery ? $this->order->actual_delivery :
            ( $this->order->requested_delivery ? $this->order->requested_delivery :
                $this->order->updated_at);

        // return Yii::$app->formatter->asDatetime($fdate, "php:j M Y");
        return $fdate;
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
    
            if ($this->doc_date){
            $this->doc_date = Yii::$app->formatter->asDate($this->doc_date, 'yyyy-MM-dd');
            } else {
            $this->doc_date = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd');    
            }

            if (empty($this->text_code))
                    $this->text_code = 'mixcart';

            if (empty($this->num_code))
                    $this->num_code = $this->order_id;

            return true;
        }
    }

    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        
         if ($insert) {

            $records = OrderContent::findAll(['order_id' => $this->order_id]);
            
        //    var_dump($records);
        //    var_dump($this->order_id);

            $transaction = Yii::$app->db_api->beginTransaction();

            try {

                foreach ($records as $record) {

                    $wdmodel = new RkWaybilldata();

                    $wdmodel->waybill_id = $this->id;
                    $wdmodel->product_id = $record->product_id;
                    $wdmodel->quant = $record->quantity;                    
                    $wdmodel->sum = round($record->price*$record->quantity,2);
                    $wdmodel->defquant = $record->quantity;                    
                    $wdmodel->defsum = round($record->price*$record->quantity,2);
                    $wdmodel->vat = 1800;
                    $wdmodel->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                    $wdmodel->org = $this->org;
                    $wdmodel->koef = 1;
                    
                    
                    // Check previous
                    
                    $ch = RkWaybilldata::find()
                            ->andWhere('product_id = :prod',['prod' => $wdmodel->product_id ]) 
                            ->andWhere('org = :org',['org' => $wdmodel->org]) 
                            ->andWhere('product_rid is not null')
                            ->orderBy(['linked_at' => SORT_DESC])
                            ->limit(1)
                            ->one();
                    
                    if ($ch) {
                        $wdmodel->product_rid = $ch->product_rid;
                        $wdmodel->munit_rid = $ch->munit_rid;
                        $wdmodel->koef = $ch->koef;

                    }
                    
               
                    if (!$wdmodel->save()) {
                        
                        var_dump($wdmodel->getErrors());
                        
                        throw new Exception();
                    }
                }

                $transaction->commit();
            } catch (Exception $ex) {
                
                 var_dump($ex);

                $transaction->rollback();
            }
        } 
        
    }
    
    
    


    public static function getDb() {
        return \Yii::$app->db_api;
    }

}
