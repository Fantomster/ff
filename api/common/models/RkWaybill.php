<?php

namespace api\common\models;

//use common\models\User;

use Yii;
use common\models\OrderContent;

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
            [['order_id', 'doc_date', 'corr_rid', 'store_rid', 'note', 'text_code', 'num_code'], 'required'],
            [['corr_rid', 'store_rid', 'status_id'], 'integer'],
                //     [['comment'], 'string', 'max' => 255],
                //     [['acc','rid','denom','agent_type','updated_at'],'safe']
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
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
    
            if ($this->doc_date){
            $this->doc_date = Yii::$app->formatter->asDate($this->doc_date, 'yyyy-MM-dd');
            } else {
            $this->doc_date = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd');    
            }
            
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
                    $wdmodel->sum = $record->price;
                    $wdmodel->vat = 1800;
                    $wdmodel->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

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
