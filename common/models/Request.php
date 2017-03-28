<?php

namespace common\models;
use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "request".
 *
 * @property integer $id
 * @property integer $category
 * @property string $product
 * @property string $comment
 * @property string $regular
 * @property string $amount
 * @property integer $rush_order
 * @property integer $payment_method
 * @property string $deferment_payment
 * @property integer $responsible_supp_org_id
 * @property integer $count_views
 * @property string $created_at
 * @property string $end
 * @property integer $rest_org_id
 * @property integer $active_status
 *
 * 
 * @property CountCallback $countCallback
 * @property RequestCallback[] $requestCallbacks
 */
class Request extends \yii\db\ActiveRecord
{
    const NAL = 1;
    const BEZNAL = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'request';
    }
    public function behaviors() {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                 ],
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'product', 'amount', 'rest_org_id'], 'required'],
            [['category', 'rush_order', 'payment_method', 'responsible_supp_org_id', 'count_views', 'rest_org_id', 'active_status'], 'integer'],
            [['created_at', 'end'], 'safe'],
            [['product', 'comment', 'regular', 'amount', 'deferment_payment'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category' => 'Category',
            'product' => 'Product',
            'comment' => 'Comment',
            'regular' => 'Regular',
            'amount' => 'Amount',
            'rush_order' => 'Rush Order',
            'payment_method' => 'Payment Method',
            'deferment_payment' => 'Deferment Payment',
            'responsible_supp_org_id' => 'Responsible Supp Org ID',
            'count_views' => 'Count Views',
            'created_at' => 'Created At',
            'end' => 'End',
            'rest_org_id' => 'Rest Org ID',
            'active_status' => 'Active Status',
        ];
    }
    public function getModifyDate()
    {
        if(empty($this->created_at)){return '';}
        
          $date = Yii::$app->formatter->asDatetime(strtotime($this->created_at),'php:Y-m-d H:i:s');
          
          $ypd = Yii::$app->formatter->asDatetime($date,'php:yy');
          $mpd = Yii::$app->formatter->asDatetime($date,'php:m.y');
          $dpd = Yii::$app->formatter->asDatetime($date,'php:j');
          $tpd = Yii::$app->formatter->asDatetime($date,'php:H:i');
          $yy =  Yii::$app->formatter->asDatetime('now','php:yy');
          $md =  Yii::$app->formatter->asDatetime('now','php:m.y');
          $dd =  Yii::$app->formatter->asDatetime('now','php:j');
          
          //return Yii::$app->formatter->asTimestamp($date,'php:H:i:s');
          //return Yii::$app->formatter->asTimestamp('now','php:H:i:s');
          
          $today = false;
          $yesterday = false;
        
        if (($mpd == $md) & ($dpd == $dd))
        {
            $today = true;
            $yesterday = false;
            
            $dataTime =  Yii::$app->formatter->asTimestamp($date,'php:H:i:s');
            $curTime =  Yii::$app->formatter->asTimestamp('now','php:H:i:s');
            
            $dif = $curTime - $dataTime;
            
            $sArray = array("секунду", "секунды", "секунд");   
            $iArray = array("минуту", "минуты", "минут");
            $hArray = array("час", "часа", "часов");
            
            if($dif<60 && $dif>=0){
                $ns = floor($dif);
                $text = self::getTimeFormatWord($ns, $sArray);
                return "$ns $text назад";
            }
            elseif($dif/60>0 and $dif/60<59){  
                $ni = floor($dif/60);
                $text = self::getTimeFormatWord($ni, $iArray);
                return "$ni $text назад";
            }
            elseif($dif/3600>0 and $dif/3600<6){
                $nh = floor($dif/3600);
                $text = self::getTimeFormatWord($nh, $hArray);
                return "$nh $text назад";
            }else{
                return 'Сегодня, в '. $tpd;
            }
        }
        if (($mpd == $md) & ($dpd == $dd-1))
        {
            $today = false;
            $yesterday = true;
            return  'Вчера, в '. $tpd;
        }
        $monthes = array(
            1 => 'Января', 2 => 'Февраля', 3 => 'Марта', 4 => 'Апреля',
            5 => 'Мая', 6 => 'Июня', 7 => 'Июля', 8 => 'Августа',
            9 => 'Сентября', 10 => 'Октября', 11 => 'Ноября', 12 => 'Декабря'
        );
        if  (($today == false) & ($yesterday == false) & ($ypd == $yy))
        {
            return Yii::$app->formatter->asDatetime($date, 'd ' . $monthes[(date('n'))] . ', в HH:mm');
        }
        else
        {
            return Yii::$app->formatter->asDatetime($date, 'd ' . $monthes[(date('n'))] . ' Y, в HH:mm');
        }
    }
    static function  getTimeFormatWord($number, $suffix) {
        $keys = array(2, 0, 1, 1, 1, 2);
        $mod = $number % 100;
        $suffix_key = ($mod > 7 && $mod < 20) ? 2: $keys[min($mod % 10, 5)];
        return $suffix[$suffix_key];
    }
      
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryName()
    {
        return $this->hasOne(MpCategory::className(), ['id' => 'category']);
    }
    
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'responsible_supp_org_id']);
    }
    
    public function getCounter()
    {
        return RequestCounters::find()->where(['request_id' => $this->id])->count();
        
    }
    public function getCountCallback()
    {
        return RequestCallback::find()->where(['request_id' => $this->id])->count();
        
    }
    public function getRequestCallbacks()
    {
        return $this->hasMany(RequestCallback::className(), ['request_id' => 'id']);
    }
}
