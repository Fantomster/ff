<?php

namespace api\common\models\one_s;

use common\models\Order;
use common\models\OrderContent;
use Yii;

/**
 * This is the model class for table "one_s_waybill".
 *
 * @property integer $id
 * @property string  $agent_uuid
 * @property integer $org
 * @property integer $order_id
 * @property integer $num_code
 * @property integer $readytoexport
 * @property integer $status_id
 * @property integer $store_id
 * @property string  $note
 * @property integer $is_duedate
 * @property integer $active
 * @property integer $vat_included
 * @property string  $doc_date
 * @property string  $created_at
 * @property string  $exported_at
 * @property string  $updated_at
 * @property Order   $order
 */
class OneSWaybill extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'one_s_waybill';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['org', 'order_id', 'num_code', 'readytoexport', 'status_id', 'store_id', 'is_duedate', 'active', 'vat_included'], 'integer'],
            [['doc_date', 'created_at', 'exported_at', 'updated_at'], 'safe'],
            [['org', 'store_id', 'agent_uuid'], 'required'],
            [['agent_uuid'], 'string', 'max' => 36],
            [['note'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => Yii::t('app', 'ID'),
            'agent_uuid'    => Yii::t('app', 'Контрагент'),
            'org'           => Yii::t('app', 'Организация'),
            'order_id'      => Yii::t('app', 'Заказ'),
            'num_code'      => Yii::t('app', 'Номер документа'),
            'readytoexport' => Yii::t('app', 'Readytoexport'),
            'status_id'     => Yii::t('app', 'Статус'),
            'store_id'      => Yii::t('app', 'Склад'),
            'note'          => Yii::t('app', 'Примечание'),
            'is_duedate'    => Yii::t('app', 'Is Duedate'),
            'active'        => Yii::t('app', 'Active'),
            'vat_included'  => Yii::t('app', 'Vat Included'),
            'doc_date'      => Yii::t('app', 'Дата документа'),
            'created_at'    => Yii::t('app', 'Created At'),
            'exported_at'   => Yii::t('app', 'Exported At'),
            'updated_at'    => Yii::t('app', 'Updated At'),
            'is_invoice'    => Yii::t('app', 'Флаг, указывающий надо ли проводить документ при загрузке '),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($this->doc_date) {
                $datetime = new \DateTime($this->doc_date);
                $this->doc_date = $datetime->format('Y-m-d H:i:s');
            } else {
                $datetime = new \DateTime();
                $this->doc_date = $datetime->format('Y-m-d H:i:s');
            }

            if (empty($this->num_code)) {
                $this->num_code = $this->order_id;
            }

            return true;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $records = OrderContent::findAll(['order_id' => $this->order_id]);
            $transaction = \Yii::$app->db_api->beginTransaction();
            try {
                $taxVat = (OneSDicconst::findOne(['denom' => 'taxVat'])->getPconstValue() != null) ? OneSDicconst::findOne(['denom' => 'taxVat'])->getPconstValue() : 2000;

                foreach ($records as $record) {
                    $wdmodel = new OneSWaybillData();
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
                    // Check previous
                    $ch = OneSWaybillData::find()
                        ->andWhere('product_id = :prod', ['prod' => $wdmodel->product_id])
                        ->andWhere('org = :org', ['org' => $wdmodel->org])
                        ->andWhere('product_rid is not null')
                        //->orderBy(['linked_at' => SORT_DESC])
                        ->one();
                    if ($ch) {
                        $wdmodel->product_rid = $ch->product_rid;
                        $wdmodel->munit = $ch->munit;
                        $wdmodel->koef = $ch->koef;
                        $wdmodel->quant = $wdmodel->quant * $ch->koef;
                    } else {
                        $wdmodel->product_rid = null;
                    }
                    if ($ch && !isset($record->invoiceContent)) {
                        $wdmodel->vat = $ch->vat;
                    }
                    if (!$wdmodel->save()) {
                        var_dump($wdmodel->getErrors());
                        throw new \Exception();
                    }
                }
                $transaction->commit();
            } catch (\Exception $ex) {
                var_dump($ex);
                $transaction->rollback();
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgent()
    {
        return $this->hasOne(OneSContragent::className(), ['id' => 'agent_uuid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStore()
    {
        return $this->hasOne(OneSStore::className(), ['id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(OneSWaybillStatus::className(), ['id' => 'status_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWaybillData()
    {
        return $this->hasMany(OneSWaybillData::className(), ['waybill_id' => 'id']);
    }

    /**
     * @return mixed
     */
    public function getXmlDocument()
    {
        $model = $this;
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><document></document>');

        $xml->addChild('comment', $model->note);
        $xml->addChild('documentNumber', $model->num_code);
        $datetime = new \DateTime($model->doc_date);
        $xml->addChild('dateIncoming', $datetime->format('d.m.Y'));
        $xml->addChild('incomingDate', $datetime->format('d.m.Y'));
        $xml->addChild('defaultStore', $model->store->uuid);
        $xml->addChild('supplier', $model->agent->uuid);
        $xml->addChild('incomingDocumentNumber', $model->order_id);
        $xml->addChild('status', 'NEW');

        $items = $xml->addChild('items');
        /**
         * @var $row OneSWaybillData
         */
        $records = OneSWaybillData::findAll(['waybill_id' => $model->id, 'unload_status' => 1]);
        $vatPercent = 0;
        $discount = 0;
        //  $vatModel = \api\common\models\iiko\iikoDicconst::findOne(['denom' => 'taxVat']);
        //  if($vatModel) {
        //      $vatPercent = $vatModel->getPconstValue() / 100;
        //  }

        foreach ($records as $i => $row) {
            $item = $items->addChild('item');

            $item->addChild('amount', $row->quant);
            $item->addChild('product', $row->product->uuid);
            $item->addChild('num', (++$i));
            $item->addChild('containerId');
            $item->addChild('amountUnit', $row->munit);
            $item->addChild('discountSum', $discount);
            $item->addChild('sumWithoutNds', $row->sum);
            $item->addChild('vatPercent', $row->vat / 100);

            $item->addChild('sum', round($row->sum + ($row->sum * $row->vat / 10000), 2));
            $item->addChild('price', round($row->sum / $row->quant, 2));
            $item->addChild('isAdditionalExpense', false);
            $item->addChild('store', $model->store->uuid);
        }

        return $xml->asXML();
    }

}
