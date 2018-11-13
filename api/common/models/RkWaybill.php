<?php

namespace api\common\models;

use Aws\Ec2\Iterator\DescribeInstancesIterator;
use common\models\Order;
use common\models\User;
use frontend\modules\clientintegr\components\CreateWaybillByOrderInterface;
use common\helpers\DBNameHelper;
use yii\helpers\ArrayHelper;
use Yii;
use common\models\OrderContent;
use api_web\components\Registry;

/**
 * This is the model class for table "rk_access".
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
            //     [['comment'], 'string', 'max' => 255],
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

    public static function getStatusArray()
    {
        return [
            RkAccess::STATUS_UNLOCKED => 'Активен',
            RkAccess::STATUS_LOCKED   => 'Отключен',
        ];
    }

    public function getCorr()
    {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        $acc = ($this->org === null) ? Yii::$app->user->identity->organization_id : $this->org;
        return RkAgent::find()->andWhere('rid = :corr_rid and acc = :acc', [':corr_rid' => $this->corr_rid, ':acc' => $acc])->one();

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }

    public function getStore()
    {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        $acc = ($this->org === null) ? Yii::$app->user->identity->organization_id : $this->org;
        return RkStoretree::find()
            ->andWhere('id = :store_rid and acc = :acc', [':store_rid' => $this->store_rid, ':acc' => $acc])
            // ->andWhere('type = 2')
            ->one();

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }

    public function getStatus()
    {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        return RkWaybillstatus::find()->andWhere('id = :id', [':id' => $this->status_id])->one();

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }

    /**
     * @return Order
     */
    public function getOrder()
    {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        return Order::find()->andWhere('id = :id', [':id' => $this->order_id])->one();

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);
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
        $org_id = User::findOne(Yii::$app->user->id)->organization_id;

        if ($waybillMode !== '0') {

            if ($this->store_rid === null) {
                $records = OrderContent::find()
                    ->where(['order_id' => $this->order_id])
                    ->leftJoin('`' . $dbName . '`.all_map', 'order_content.product_id = `' . $dbName . '`.`all_map`.`product_id` and `' . $dbName . '`.all_map.service_id = ' . Registry::RK_SERVICE_ID/* and `' . $dbName . '`.all_map.org_id =' . $org_id . ')'*/)
                    //  ->andWhere('product_id in ( select product_id from ' . $dbName . '.all_map where service_id = Registry::RK_SERVICE_ID and store_rid is null)')
                    ->andWhere('`' . $dbName . '`.all_map.store_rid is null')
                    ->all();
            } else {
                $records = OrderContent::find()
                    ->where(['order_id' => $this->order_id])
                    ->leftJoin('`' . $dbName . '`.`all_map`', 'order_content.product_id = `' . $dbName . '`.`all_map`.`product_id` and `' . $dbName . '`.all_map.service_id = ' . Registry::RK_SERVICE_ID/* and `' . $dbName . '`.all_map.org_id =' . $org_id . ')'*/)
                    ->andWhere('`' . $dbName . '`.all_map.store_rid =' . $this->store_rid)
                    ->all();
            }
        } else {
            $records = OrderContent::findAll(['order_id' => $this->order_id]);
        }

        $transaction = \Yii::$app->db_api->beginTransaction();
        try {
            $taxVat = RkDicconst::findOne(['denom' => 'taxVat'])->getPconstValue() ?? 1800;

            foreach ($records as $record) {
                $wdmodel = new RkWaybilldata();
                ///$wdmodel->setScenario('autoWaybill');
                $wdmodel->waybill_id = $this->id;
                $wdmodel->product_id = $record->product_id;
                if (isset($record->invoiceContent)) {
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
                    \yii::error($ex->getTraceAsString());
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

    public static function createWaybill($order_id)
    {

        $res = true;

        $order = \common\models\Order::findOne(['id' => $order_id]);

        if (!$order) {
            echo "Can't find order";
            return false;
        }

        $dbName = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db->dsn);

        /* $stories2 = AllMaps::find()->select('store_rid')->andWhere('org_id = :org and service_id = :serv and product_id in (
          SELECT product_id from '.$dbName.'.order_content where order_id = :order
          ) and is_active = 1 ',[':org' => $order->client_id, ':order' => $order_id, ':serv' => Registry::RK_SERVICE_ID])->groupBy('store_rid')->column(); */

        $db = Yii::$app->db_api;
        $sql = ' SELECT m.store_rid FROM `' . $dbName . '`.`order_content` o ' .
            ' LEFT JOIN all_map m ON o.product_id = m.product_id AND m.service_id = ' . Registry::RK_SERVICE_ID . ' AND m.org_id = ' . $order->client_id .
            ' WHERE o.order_id = ' . $order_id .
            ' GROUP BY store_rid';

        $stories = $db->createCommand($sql)->queryAll();
        $stories = ArrayHelper::getColumn($stories, 'store_rid');

        $contra = RkAgent::findOne(['vendor_id' => $order->vendor_id]);

        $num = (count($stories) > 1) ? 1 : '';

        foreach ($stories as $store) {

            $model = new RkWaybill();
            $model->order_id = $order_id;
            $model->status_id = 1;
            $model->org = $order->client_id;
            $model->text_code = $num . '-mixcart';
            $model->num_code = $order_id;
            $model->store_rid = $store;
            $model->corr_rid = isset($contra) ? $contra->id : null;

            $model->doc_date = Yii::$app->formatter->asDate($model->doc_date . ' 16:00:00', 'php:Y-m-d H:i:s'); //date('d.m.Y', strtotime($model->doc_date));
            //   $model->payment_delay_date = Yii::$app->formatter->asDate($model->payment_delay_date . ' 16:00:00', 'php:Y-m-d H:i:s');

            if (!$model->save()) {
                $num++;
                $res = false;
                continue;
            }

            $num++;
        }
        return $res;
    }

    public static function exportWaybill($waybill_id): bool
    {
        // TODO: Implement exportWaybill() method.
    }

}
