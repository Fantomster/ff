<?php

namespace api\common\models;

use Aws\Ec2\Iterator\DescribeInstancesIterator;
use common\models\Order;
use common\models\User;
use frontend\modules\clientintegr\components\CreateWaybillByOrderInterface;
use common\helpers\DBNameHelper;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use Yii;
use common\models\OrderContent;
use api_web\components\Registry;
use yii\behaviors\TimestampBehavior;
use yii\web\NotFoundHttpException;
use api\common\models\AllMaps;

/**
 * This is the model class for table "rk_waybill".
 *
 * @property integer  $id
 * @property integer  $fid
 * @property integer  $org
 * @property string   $login
 * @property string   $password
 * @property string   $token
 * @property string   $lic
 * @property datetime $fd
 * @property datetime $td
 * @property integer  $ver
 * @property integer  $locked
 * @property string   $usereq
 * @property string   $comment
 * @property string   $salespoint
 */
class RkWaybill extends \yii\db\ActiveRecord implements CreateWaybillByOrderInterface
{

    const STATUS_UNLOCKED = 0;
    const STATUS_LOCKED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_waybill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'doc_date'], 'required'],
            [['corr_rid', 'store_rid', 'status_id', 'num_code'], 'integer'],
            [['store_rid'], 'number', 'min' => 0],
            [['store_rid', 'org', 'vat_included', 'text_code', 'num_code', 'note'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'order_id'  => 'Заказ',
            'corr_rid'  => 'Контрагент',
            'store_rid' => 'Склад',
            'doc_date'  => 'Дата документа',
            'note'      => 'Примечание',
            'text_code' => 'Код текст',
            'num_code'  => 'Код цифр.'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    public static function getStatusArray()
    {
        return [
            RkAccess::STATUS_UNLOCKED => 'Активен',
            RkAccess::STATUS_LOCKED   => 'Отключён',
        ];
    }

    public function getCorr()
    {
        $acc = ($this->org === null) ? Yii::$app->user->identity->organization_id : $this->org;
        return RkAgent::find()->andWhere('rid = :corr_rid and acc = :acc', [':corr_rid' => $this->corr_rid, ':acc' => $acc])->one();
    }

    public function getStore()
    {
        $acc = ($this->org === null) ? Yii::$app->user->identity->organization_id : $this->org;
        return RkStoretree::find()
            ->andWhere('id = :store_rid and acc = :acc', [':store_rid' => $this->store_rid, ':acc' => $acc])
            // ->andWhere('type = 2')
            ->one();
    }

    public function getStatus()
    {
        return RkWaybillstatus::find()->andWhere('id = :id', [':id' => $this->status_id])->one();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return Order::find()->andWhere('id = :id', [':id' => $this->order_id])->one();
    }

    public function getFinalDate()
    {

        $fdate = $this->order->actual_delivery ? $this->order->actual_delivery :
            ($this->order->requested_delivery ? $this->order->requested_delivery :
                $this->order->updated_at);

        // return Yii::$app->formatter->asDatetime($fdate, "php:j M Y");
        return $fdate;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWaybillData()
    {
        return $this->hasMany(RkWaybilldata::className(), ['waybill_id' => 'id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($this->doc_date) {
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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            $this->createWaybillData();
        }
    }

    protected function createWaybillData()
    {
        $dbName = DBNameHelper::getApiName();
        $waybillMode = RkDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();
        $allmapTableName = $dbName . '.' . AllMaps::tableName();
        $client_id = $this->org;

        if ($waybillMode !== '0') {

            if ($this->store_rid === null) {
                $records = OrderContent::find()
                    ->where(['order_id' => $this->order_id])
                    ->leftJoin($dbName . '.all_map', 'order_content.product_id = ' . $dbName . '.all_map.product_id and ' . $dbName . '.all_map.service_id = ' . Registry::RK_SERVICE_ID/* and `' . $dbName . '`.all_map.org_id =' . $org_id . ')' */)
                    ->andWhere($dbName . '.all_map.store_rid is null')
                    ->all();
            } else {
                $records = OrderContent::find()
                    ->where(['order_id' => $this->order_id])
                    ->leftJoin($dbName . '.all_map', 'order_content.product_id = ' . $dbName . '.all_map.product_id and ' . $dbName . '.all_map.service_id = ' . Registry::RK_SERVICE_ID/* and `' . $dbName . '`.all_map.org_id =' . $org_id . ')' */)
                    ->andWhere($dbName . '.all_map.store_rid =' . $this->store_rid)
                    ->all();
            }
        } else {
            $records = OrderContent::findAll(['order_id' => $this->order_id]);
        }

        $transaction = \Yii::$app->db_api->beginTransaction();
        try {
            $taxVat = RkDicconst::findOne(['denom' => 'taxVat'])->getPconstValue() ?? 2000;

            foreach ($records as $record) {
                $wdmodel = new RkWaybilldata();
                $wdmodel->waybill_id = $this->id;
                $wdmodel->product_id = $record->product_id;
                if (($record->into_quantity != null) and ($record->into_price != null) and ($record->into_price_vat != null) and ($record->into_price_sum != null) and ($record->into_price_sum_vat != null) and ($record->vat_product != null) and ($record->quantity == $record->into_quantity)) {
                    $wdmodel->quant = $record->into_quantity;
                    $wdmodel->sum = $record->into_price_sum;
                    $wdmodel->defquant = $record->into_quantity;
                    $wdmodel->defsum = $record->into_price_sum;
                    $wdmodel->vat = $record->vat_product * 100;
                } elseif (isset($record->invoiceContent)) {
                    $wdmodel->quant = $record->invoiceContent->quantity;
                    $wdmodel->sum = $record->invoiceContent->sum_without_nds;
                    $wdmodel->defquant = $record->invoiceContent->quantity;
                    $wdmodel->defsum = $record->invoiceContent->sum_without_nds;
                    $wdmodel->vat = $record->invoiceContent->percent_nds * 100;
                } else {
                    $wdmodel->quant = $record->quantity;
                    $wdmodel->sum = round($record->price * $record->quantity, 2);
                    $wdmodel->defquant = $record->quantity;
                    $wdmodel->defsum = round($record->price * $record->quantity, 2);
                    $wdmodel->vat = $taxVat;
                }
                $wdmodel->org = $this->org;
                $wdmodel->koef = 1;
                $wdmodel->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:i:s');
                $wdmodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:i:s');
                // New check mapping
                $ch = AllMaps::find()
                    ->andWhere('product_id = :prod', ['prod' => $record->product_id])
                    ->andWhere('org_id = :org', ['org' => $this->org])
                    ->andWhere('service_id = :serv', ['serv' => Registry::RK_SERVICE_ID])
                    ->one();

                if ($ch) {
                    if (isset($ch->serviceproduct_id)) {
                        $wdmodel->product_rid = $ch->serviceproduct_id;
                    } else {
                        $wdmodel->product_rid = null;
                    }

                    if (isset($ch->koef)) {
                        $wdmodel->koef = $ch->koef;
                        $wdmodel->quant = $wdmodel->quant * $ch->koef;
                    }

                    if (isset($ch->unit_rid)) {
                        $wdmodel->munit_rid = $ch->unit_rid;
                    }

                    if (isset($ch->vat) && !isset($record->invoiceContent)) {
                        $wdmodel->vat = $ch->vat;
                    }
                } else {
                    $wdmodel->product_rid = null;
                }

                if (!$wdmodel->save()) {
                    \yii::error(print_r($wdmodel->getErrors()), true);
                    throw new \Exception();
                }
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            \yii::error($ex->getTraceAsString());
            $transaction->rollback();
        }
    }

    public static function getDb()
    {
        return \Yii::$app->db_api;
    }

    public static function createWaybill($order_id, $auto = false)
    {
        $order_id = (int)$order_id; //переписать без raw запросов
        $res = true;

        $order = \common\models\Order::findOne(['id' => $order_id]);

        if (!$order) {
            throw new NotFoundHttpException(Yii::t('error', 'api.controllers.order.not.find', ['ru' => 'Заказа с таким номером не существует.']));
        }

        $allMapTableName = DBNameHelper::getApiName() . '.' . AllMaps::tableName();
        $orderContentTableName = OrderContent::tableName();
        $client_id = $order->client_id;
        $stories = OrderContent::find()
            ->select("$allMapTableName.store_rid")
            ->leftJoin($allMapTableName, "$orderContentTableName.product_id = $allMapTableName.product_id and $allMapTableName.service_id = :service_id AND 
            $allMapTableName.org_id = :client_id", [':service_id' => Registry::RK_SERVICE_ID,
                                                    ':client_id'  => $order->client_id])
            ->where("$orderContentTableName.order_id = :order_id", [':order_id' => $order_id])
            ->groupBy('store_rid')
            ->asArray()->all();

        $contra = RkAgent::findOne(['vendor_id' => $order->vendor_id]);

        $num = (count($stories) > 1) ? 1 : '';

        foreach ($stories as $store) {
            $store = $store['store_rid'];
            $model = new RkWaybill();
            $model->order_id = $order_id;
            $model->status_id = 1;
            $model->org = $order->client_id;
            $model->text_code = $num . '-mixcart';
            $model->num_code = $order_id;
            $model->store_rid = $store;
            $model->corr_rid = isset($contra) ? $contra->id : null;

            $model->doc_date = Yii::$app->formatter->asDate($model->doc_date . ' 16:00:00', 'php:Y-m-d H:i:s'); //date('d.m.Y', strtotime($model->doc_date));

            if (!$model->save()) {
                $num++;
                $res = false;
                throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную R-Keeper не удалось.']));
                continue;
            } else {
                $model->createWaybillData();
                $kolvo_nesopost = RkWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $model->id])->andWhere(['product_rid' => null])->count();
                if (($model->corr_rid === null) or ($model->num_code === null) or ($model->text_code === null) or ($model->store_rid === null)) {
                    $shapka = 0;
                } else {
                    $shapka = 1;
                }
                if ($kolvo_nesopost == 0) {
                    if ($shapka == 1) {
                        $model->readytoexport = 1;
                        $model->status_id = 5;
                    } else {
                        $model->readytoexport = 0;
                        $model->status_id = 1;
                    }
                } else {
                    if ($shapka == 1) {
                        $model->readytoexport = 0;
                        $model->status_id = 1;
                    } else {
                        $model->readytoexport = 0;
                    }
                }
                if (!$model->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную R-Keeper не удалось.']));
                }
            }
            $num++;
        }
        return $res;
    }

    public static function exportWaybill($waybill_id, $auto = false): bool
    {
        // TODO: Implement exportWaybill() method.
    }

    /**
     * @param RkWaybill $contributorWaybill
     * @param RkWaybill $recipientWaybill
     * @return RkWaybill
     */
    public static function moveContentToExistingWaybill($contributorWaybill, $recipientWaybill)
    {
        foreach ($contributorWaybill->waybillData as $position) {
            $position->waybill_id = $recipientWaybill->id;
            $position->save();
        }
        $contributorWaybill->delete();
        return $recipientWaybill;
    }

}
