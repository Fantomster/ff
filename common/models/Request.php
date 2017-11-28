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
 * @property integer $rest_user_id
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
            [['category', 'rush_order', 'payment_method', 'responsible_supp_org_id', 'count_views', 'rest_org_id', 'active_status', 'rest_user_id'], 'integer'],
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
            'rest_user_id' => 'User Id',
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
            $iArray = array(Yii::t('app', 'common.models.minute', ['ru'=>"минуту"]), Yii::t('app', 'common.models.minutes', ['ru'=>"минуты"]), Yii::t('app', 'common.models.minute_two', ['ru'=>"минут"]));
            $hArray = array(Yii::t('app', 'common.models.hour', ['ru'=>"час"]), Yii::t('app', 'common.models.hour_two', ['ru'=>"часа"]), Yii::t('app', 'common.models.hours', ['ru'=>"часов"]));

            if ($dif < 60 and $dif >= 0) {
                $ns = floor($dif);
                $text = self::getTimeFormatWord($ns, $sArray);
                return "$ns $text " . Yii::t('message', 'market.controllers.site.ago', ['ru'=>'назад']);
            } elseif ($dif / 60 > 0 and $dif / 60 < 60) {
                $ni = floor($dif / 60);
                $text = self::getTimeFormatWord($ni, $iArray);
                return "$ni $text " . Yii::t('message', 'market.controllers.site.ago_two', ['ru'=>'назад']);
            } elseif ($dif / 3600 > 0 and $dif / 3600 < 6) {
                $nh = floor($dif / 3600);
                $text = self::getTimeFormatWord($nh, $hArray);
                return "$nh $text " . Yii::t('message', 'market.controllers.site.ago_three', ['ru'=>'назад']);
            } else {
                return Yii::t('app', 'common.models.today_in', ['ru'=>'Сегодня, в ']) . $tpd;
            }
        }
        if (($mpd == $md) & ($dpd == $dd - 1)) {
            $today = false;
            $yesterday = true;
            return Yii::t('app', 'common.models.yesterday_in', ['ru'=>'Вчера, в ']) . $tpd;
        }
        $monthes = array(
            1 => Yii::t('app', 'common.models.jan', ['ru'=>'Января']),
            2 => Yii::t('app', 'common.models.feb', ['ru'=>'Февраля']),
            3 => Yii::t('app', 'common.models.mar', ['ru'=>'Марта']),
            4 => Yii::t('app', 'common.models.apr', ['ru'=>'Апреля']),
            5 => Yii::t('app', 'common.models.may', ['ru'=>'Мая']),
            6 => Yii::t('app', 'common.models.june', ['ru'=>'Июня']),
            7 => Yii::t('app', 'common.models.july', ['ru'=>'Июля']),
            8 => Yii::t('app', 'common.models.aug', ['ru'=>'Августа']),
            9 => Yii::t('app', 'common.models.sep', ['ru'=>'Сентября']),
            10 => Yii::t('app', 'common.models.okt', ['ru'=>'Октября']),
            11 => Yii::t('app', 'common.models.nov', ['ru'=>'Ноября']),
            12 => Yii::t('app', 'common.models.dec', ['ru'=>'Декабря'])
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
                return Yii::t('app', 'common.models.once', ['ru'=>'Разово']);
                break;
            case 2:
                return Yii::t('app', 'common.models.daily', ['ru'=>'Ежедневно']);
                break;
            case 3:
                return Yii::t('app', 'common.models.weekly', ['ru'=>'Каждую неделю']);
                break;
            case 4:
                return Yii::t('app', 'common.models.monthly', ['ru'=>'Каждый месяц']);
                break;
        }
    }

    public function getPaymentMethodName() {
        switch ($this->payment_method) {
            case 1:
                return Yii::t('app', 'common.models.cash', ['ru'=>'Наличный расчет']);
                break;
            case 2:
                return Yii::t('app', 'common.models.no_cash', ['ru'=>'Безналичный расчет']);
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
                'label' => Yii::t('app', 'common.models.number', ['ru'=>'Номер']),
                'value' => 'id',
            ],
            [
                'label' => Yii::t('app', 'common.models.product', ['ru'=>'Продукт']),
                'value' => 'product',
            ],
            [
                'label' => Yii::t('app', 'common.models.quantity', ['ru'=>'Количество']),
                'value' => 'amount',
            ],
            [
                'label' => Yii::t('app', 'common.models.comment_three', ['ru'=>'Комментарий']),
                'value' => 'comment',
            ],
            [
                'label' => Yii::t('app', 'common.models.category_four', ['ru'=>'Категория']),
                'value' => 'categoryName.name',
            ],
            [
                'attribute' => 'client.name',
                'label' => Yii::t('app', 'common.models.rest_name', ['ru'=>'Название ресторана']),
            ],
            [
                'attribute' => 'created_at',
                'label' => Yii::t('app', 'common.models.creation_date', ['ru'=>'Дата создания']),
            ],
            [
                'value' => function($data) {
                    return ($data['active_status']) ? Yii::t('app', 'common.models.open', ['ru'=>'Открыта']) : Yii::t('app', 'common.models.close', ['ru'=>'Закрыта']);
                },
                'label' => Yii::t('app', 'common.models.status_three', ['ru'=>'Статус']),
            ],
        ];
    }

}
