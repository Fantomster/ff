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
 * @property string $regularName
 * @property Organization $vendor
 * @property Organization $client
 * @property string $paymentMethodName
 * @property string $categoryName
 * @property string $countCallback
 * @property array $requestCallbacks
 */
class Request extends \yii\db\ActiveRecord {

    const ACTIVE = 1;
    const INACTIVE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'request';
    }

    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            if ($this->rush_order) {
                $this->end = Yii::$app->formatter->asDatetime(strtotime($this->created_at) + 24 * 3600, 'php:Y-m-d H:i:s');
            } else {
                $this->end = Yii::$app->formatter->asDatetime(strtotime($this->created_at) + 30 * 24 * 3600, 'php:Y-m-d H:i:s');
            }
            return true;
        }
        return false;
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
    public function rules() {
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
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'category' => Yii::t('app', 'common.models.goods_category', ['ru'=>'Категория товара']),
            'product' => Yii::t('app', 'common.models.good_two', ['ru'=>'Товар']),
            'comment' => Yii::t('app', 'common.models.comment_two', ['ru'=>'Комментарий']),
            'regular' => Yii::t('app', 'common.models.orders_regularity', ['ru'=>'Регулярность заказа']),
            'amount' => Yii::t('app', 'common.models.value', ['ru'=>'Объем']),
            'rush_order' => Yii::t('app', 'common.models.urgency', ['ru'=>'Срочность']),
            'payment_method' => Yii::t('app', 'common.models.payment_variant', ['ru'=>'Способ оплаты']),
            'deferment_payment' => Yii::t('app', 'common.models.deferred_payment', ['ru'=>'Отложенный платеж']),
            'responsible_supp_org_id' => Yii::t('app', 'common.models.responsible', ['ru'=>'Ответственный']),
            'count_views' => Yii::t('app', 'Count Views'),
            'created_at' => Yii::t('app', 'Created At'),
            'end' => Yii::t('app', 'End'),
            'rest_org_id' => 'Rest Org ID',
            'active_status' => Yii::t('app', 'Active Status'),
        ];
    }

    public function getModifyDate() {
        $date = Yii::$app->formatter->asDatetime(strtotime($this->created_at), 'php:Y-m-d H:i:s');
        $m = Yii::$app->formatter->asDatetime($date, 'php:n');
        $ypd = Yii::$app->formatter->asDatetime($date, 'php:yy');
        $mpd = Yii::$app->formatter->asDatetime($date, 'php:m.y');
        $dpd = Yii::$app->formatter->asDatetime($date, 'php:j');
        $tpd = Yii::$app->formatter->asDatetime($date, 'php:H:i');
        $yy = Yii::$app->formatter->asDatetime('now', 'php:yy');
        $md = Yii::$app->formatter->asDatetime('now', 'php:m.y');
        $dd = Yii::$app->formatter->asDatetime('now', 'php:j');

        $today = false;
        $yesterday = false;

        if (($mpd == $md) & ($dpd == $dd)) {
            $today = true;
            $yesterday = false;

            $dataTime = Yii::$app->formatter->asTimestamp($date, 'php:H:i:s');
            $curTime = Yii::$app->formatter->asTimestamp('now', 'php:H:i:s');

            $dif = $curTime - $dataTime;

            $sArray = array(Yii::t('app', 'common.models.sec', ['ru'=>"секунду"]), Yii::t('app', 'common.models.secs', ['ru'=>"секунды"]), Yii::t('app', 'common.models.sec_two', ['ru'=>"секунд"]));
            $iArray = array("минуту", "минуты", "минут");
            $hArray = array("час", "часа", "часов");

            if ($dif < 60 and $dif >= 0) {
                $ns = floor($dif);
                $text = self::getTimeFormatWord($ns, $sArray);
                return "$ns $text назад";
            } elseif ($dif / 60 > 0 and $dif / 60 < 60) {
                $ni = floor($dif / 60);
                $text = self::getTimeFormatWord($ni, $iArray);
                return "$ni $text назад";
            } elseif ($dif / 3600 > 0 and $dif / 3600 < 6) {
                $nh = floor($dif / 3600);
                $text = self::getTimeFormatWord($nh, $hArray);
                return "$nh $text назад";
            } else {
                return 'Сегодня, в ' . $tpd;
            }
        }
        if (($mpd == $md) & ($dpd == $dd - 1)) {
            $today = false;
            $yesterday = true;
            return 'Вчера, в ' . $tpd;
        }
        $monthes = array(
            1 => 'Января', 2 => 'Февраля', 3 => 'Марта', 4 => 'Апреля',
            5 => 'Мая', 6 => 'Июня', 7 => 'Июля', 8 => 'Августа',
            9 => 'Сентября', 10 => 'Октября', 11 => 'Ноября', 12 => 'Декабря'
        );
        if (($today == false) & ($yesterday == false) & ($ypd == $yy)) {
            return Yii::$app->formatter->asDatetime($date, 'd ' . $monthes[($m)] . ', в HH:mm');
        } else {
            return Yii::$app->formatter->asDatetime($date, 'd ' . $monthes[($m)] . ' Y, в HH:mm');
        }
    }

    static function getTimeFormatWord($number, $suffix) {
        $keys = array(2, 0, 1, 1, 1, 2);
        $mod = $number % 100;
        $suffix_key = ($mod > 7 && $mod < 20) ? 2 : $keys[min($mod % 10, 5)];
        return $suffix[$suffix_key];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryName() {
        return $this->hasOne(MpCategory::className(), ['id' => 'category']);
    }

    public function getRegularName() {
        switch ($this->regular) {
            case 1:
                return 'Разово';
                break;
            case 2:
                return 'Ежедневно';
                break;
            case 3:
                return 'Каждую неделю';
                break;
            case 4:
                return 'Каждый месяц';
                break;
        }
    }

    public function getPaymentMethodName() {
        switch ($this->payment_method) {
            case 1:
                return 'Наличный расчет';
                break;
            case 2:
                return 'Безналичный расчет';
                break;
        }
    }

    public function getManagers($id) {
        if (User::find()->where(['organization_id' => $id])->exists()) {
            return User::find()->where(['organization_id' => $id])->all();
        } else {
            return;
        }
    }

    public function getClient() {
        return $this->hasOne(Organization::className(), ['id' => 'rest_org_id']);
    }

    public function getVendor() {
        return $this->hasOne(Organization::className(), ['id' => 'responsible_supp_org_id']);
    }

    public function getCounter() {
        return RequestCounters::find()->where(['request_id' => $this->id])->count();
    }

    public function getCountCallback() {
        return RequestCallback::find()->where(['request_id' => $this->id])->count();
    }

    public function getRequestCallbacks() {
        return $this->hasMany(RequestCallback::className(), ['request_id' => 'id']);
    }

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        if (!is_a(Yii::$app, 'yii\console\Application')) {
            if ($insert) {
                \api\modules\v1\modules\mobile\components\NotificationHelper::actionRequest($this->id, $insert);
            }
        }
    }

    public function getFranchiseeAssociate() {
        return $this->hasOne(FranchiseeAssociate::className(), ['rest_org_id' => 'organization_id']);
    }

    public function getRequestExportColumns() {
        return [
            [
                'label' => 'Номер',
                'value' => 'id',
            ],
            [
                'label' => 'Продукт',
                'value' => 'product',
            ],
            [
                'label' => 'Количество',
                'value' => 'amount',
            ],
            [
                'label' => 'Комментарий',
                'value' => 'comment',
            ],
            [
                'label' => 'Категория',
                'value' => 'categoryName.name',
            ],
            [
                'attribute' => 'client.name',
                'label' => 'Название ресторана',
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Дата создания',
            ],
            [
                'value' => function($data) {
                    return ($data['active_status']) ? 'Открыта' : 'Закрыта';
                },
                'label' => 'Статус',
            ],
        ];
    }

}
