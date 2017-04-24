<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "franchisee".
 *
 * @property integer $id
 * @property string $signed
 * @property string $legal_entity
 * @property string $legal_address
 * @property string $legal_email
 * @property string $inn
 * @property string $kpp
 * @property string $ogrn
 * @property string $bank_name
 * @property string $bik
 * @property string $phone
 * @property string $correspondent_account
 * @property string $checking_account
 * @property string $info
 * @property string $created_at
 * @property string $updated_at
 * @property integer $type_id
 * @property boolean $deleted
 * 
 * @property FranchiseeAssociate[] $franchiseeAssociates
 * @property FranchiseeUser[] $franchiseeUsers
 * @property FracnchiseeType type
 */
class Franchisee extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'franchisee';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
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
            [['type_id'], 'integer'],
            [['type_id'], 'required'],
            [['info'], 'string'],
            [['created_at', 'updated_at', 'deleted'], 'safe'],
            [['signed', 'legal_entity', 'legal_address', 'legal_email', 'inn', 'kpp', 'ogrn', 'bank_name', 'bik', 'phone', 'correspondent_account', 'checking_account'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'info' => 'Поле для заметок',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
            'signed' => 'Подписант',
            'legal_entity' => 'Юридическое название',
            'legal_address' => 'Юридический адрес',
            'legal_email' => 'Официальный email',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН',
            'bank_name' => 'Банк',
            'bik' => 'БИК',
            'correspondent_account' => 'р/с',
            'checking_account' => 'к/с',
            'phone' => 'Телефон',
        ];
    }

    public function delete() {
        $this->deleted = true;
        return $this->save();
    }
    
    public static function deleteAll($condition = '', $params = array()) {
        $command = static::getDb()->createCommand();
        $command->update(static::tableName(), ['deleted' => true], $condition, $params);

        return $command->execute();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFranchiseeAssociates() {
        return $this->hasMany(FranchiseeAssociate::className(), ['franchisee_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFranchiseeUsers() {
        return $this->hasMany(FranchiseeUser::className(), ['franchisee_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers() {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->via('franchiseeUsers');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType() {
        return $this->hasOne(FranchiseType::className(), ['id' => 'type_id']);
    }

    public function getFirstOrganizationDate() {
        $today = new \DateTime();
        $result = $today->format('d.m.Y');

        $firstOrg = Organization::find()
                ->joinWith('franchiseeAssociate')
                ->where(['franchisee_associate.franchisee_id' => $this->id])
                ->orderBy(['organization.created_at' => SORT_ASC])
                ->limit(1)
                ->one();

        if ($firstOrg) {
            $result = $firstOrg->created_at;
        }
        return $result;
    }

    /*
     * @return array
     * 
     * ['orderCount' => 1, 'turnover' => 1, 'turnoverCut' => 1]
     */
    public function getMyVendorsStats($dateFrom = null, $dateTo = null) {
        $ordTable = Order::tableName();
        $faTable = FranchiseeAssociate::tableName();
        $rsrTable = RelationSuppRest::tableName();
        $biTable = BuisinessInfo::tableName();
        $orders = Order::find()
                ->leftJoin($faTable, "$ordTable.vendor_id = $faTable.organization_id")
                ->leftJoin($rsrTable, "$ordTable.vendor_id = $rsrTable.supp_org_id")
                ->leftJoin($biTable, "$ordTable.vendor_id = $biTable.organization_id")
                ->where(["$faTable.franchisee_id" => $this->id, "$ordTable.status" => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_PROCESSING, Order::STATUS_DONE]])
                ->andFilterWhere([">", "$ordTable.updated_at", $dateFrom])
                ->andFilterWhere(["<", "$ordTable.updated_at", $dateTo]);
        $orderCount = $orders->count();
        $turnover = $orders->sum("$ordTable.total_price");
        $turnoverCut = $orders->andWhere(["$rsrTable.is_from_market" => true])->sum("$ordTable.total_price * $biTable.reward / 100");
        return ['orderCount' => $orderCount, 'turnover' => $turnover, 'turnoverCut' => $turnoverCut];
    }
}
