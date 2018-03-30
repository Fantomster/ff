<?php

namespace common\models;

use Yii;
use common\models\Role;
use yii\base\Model;
use yii\web\UploadedFile;

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
 * @property bool $deleted
 * @property string $picture_manager
 * @property string $fio_manager
 * @property string $phone_manager
 * @property integer $additional_number_manager
 * @property integer $receiving_organization
 *
 * @property FranchiseeAssociate[] $franchiseeAssociates
 * @property FranchiseeUser[] $franchiseeUsers
 * @property FracnchiseeType type
 * @property string $pictureUrl
 */
class Franchisee extends \yii\db\ActiveRecord {
    
    const DEFAULT_AVATAR = '/image/franchisse/no-avatar.jpg';
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
            [['type_id','additional_number_manager', 'receiving_organization'], 'integer'],
            [['type_id'], 'required'],
            [['info'], 'string'],
            [['created_at', 'updated_at', 'deleted'], 'safe'],
            [['signed', 'legal_entity', 'legal_address', 'legal_email', 'inn', 'kpp', 'ogrn', 'bank_name', 'bik', 'phone', 'correspondent_account', 'checking_account','picture_manager','fio_manager','phone_manager'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'info' => Yii::t('app', 'common.models.field_two', ['ru'=>'Поле для заметок']),
            'created_at' => Yii::t('app', 'common.models.created_thrdd', ['ru'=>'Создано']),
            'updated_at' => Yii::t('app', 'common.models.refreshed_three', ['ru'=>'Обновлено']),
            'signed' => Yii::t('app', 'common.models.signer', ['ru'=>'Подписант']),
            'legal_entity' => Yii::t('app', 'common.models.jur_name_two', ['ru'=>'Юридическое название']),
            'legal_address' => Yii::t('app', 'common.models.jur_address_two', ['ru'=>'Юридический адрес']),
            'legal_email' => Yii::t('app', 'common.models.official_email_two', ['ru'=>'Официальный email']),
            'inn' => Yii::t('app', 'common.models.inn_two', ['ru'=>'ИНН']),
            'kpp' => Yii::t('app', 'common.models.kpp_two', ['ru'=>'КПП']),
            'ogrn' => Yii::t('app', 'common.models.ogrn_two', ['ru'=>'ОГРН']),
            'bank_name' => Yii::t('app', 'common.models.bank_two', ['ru'=>'Банк']),
            'bik' => Yii::t('app', 'common.models.bik_two', ['ru'=>'БИК']),
            'correspondent_account' => Yii::t('app', 'common.models.rs_two', ['ru'=>'р/с']),
            'checking_account' => Yii::t('app', 'common.models.ks_two', ['ru'=>'к/с']),
            'phone' => Yii::t('app', 'common.models.phone_two', ['ru'=>'Телефон']),
            'picture_manager' => Yii::t('app', 'common.models.managers_avatar', ['ru'=>'Аватар менеджера']),
            'fio_manager' => Yii::t('app', 'common.models.managers_fio', ['ru'=>'фио менеджера']),
            'phone_manager' => Yii::t('app', 'common.models.managers_phone', ['ru'=>'Телефон менеджера']),
            'additional_number_manager' => Yii::t('app', 'common.models.additional_phone_two', ['ru'=>'Добавочный номер менеджера'])
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
    public function getFranchiseeGeo() {
        return $this->hasMany(FranchiseeGeo::className(), ['franchisee_id' => 'id']);
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
                ->andWhere('organization.created_at is not null')
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
    public function getMyVendorsStats($dateFrom = null, $dateTo = null, $currencyId = null) {
        $ordTable = Order::tableName();
        $faTable = FranchiseeAssociate::tableName();
        $rsrTable = RelationSuppRest::tableName();
        $biTable = BuisinessInfo::tableName();
        $orders = Order::find()
                ->leftJoin($faTable, "$ordTable.vendor_id = $faTable.organization_id")
                ->leftJoin($rsrTable, "$ordTable.vendor_id = $rsrTable.supp_org_id")
                ->leftJoin($biTable, "$ordTable.vendor_id = $biTable.organization_id")
                ->where(["$faTable.franchisee_id" => $this->id, "$ordTable.status" => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_PROCESSING, Order::STATUS_DONE]]);

        if($currencyId){
            $orders = $orders->andWhere(["currency_id"=>$currencyId]);
        }
        $orders = $orders->andFilterWhere([">", "$ordTable.updated_at", $dateFrom])
                ->andFilterWhere(["<", "$ordTable.updated_at", $dateTo]);
        $orderCount = $orders->count();
        $turnover = $orders->sum("$ordTable.total_price");
        $turnoverCut = $orders->andWhere(["$rsrTable.is_from_market" => true])->sum("$ordTable.total_price * $biTable.reward / 100");
        return ['orderCount' => $orderCount, 'turnover' => $turnover, 'turnoverCut' => $turnoverCut];
    }
    
    public function getPictureUrl() {
        return $this->picture_manager ? $this->getThumbUploadUrl('picture', 'picture') : self::DEFAULT_AVATAR;
    }
    
    public static function limitedDropdown()
    {
        // get all records from database and generate
        static $dropdown;
        if ($dropdown === null) {
                $models = Role::findAll(['organization_type' => Organization::TYPE_FRANCHISEE]);
            foreach ($models as $model) {
                if ($model->id !== Role::ROLE_FRANCHISEE_AGENT) {
                    $dropdown[$model->id] = Yii::t('app', $model->name);
                }
            }
        }
        return $dropdown;
    }

    public function getFranchiseeEmployees($is_managers=false){
        $dropdown = [];
        $role = ($is_managers) ? Role::ROLE_FRANCHISEE_MANAGER : Role::ROLE_FRANCHISEE_LEADER;
        $models = User::find()
            ->joinWith("franchiseeUser")
            ->joinWith("profile")->select(['user.id', 'profile.full_name'])
            ->where([
                'franchisee_user.franchisee_id' => $this->id,
                'role_id' => $role
            ])->all();
        foreach ($models as $model) {
            $dropdown[$model->id] = $model->profile->full_name;
        }

        return $dropdown;
    }

    public function afterSave($insert, $changedAttributes)
    {
        $data = $this->find()->with('franchiseeGeo')->all();
        $arr = [];
        $i = 0;
        foreach ($data as $one){
            if($one->legal_email=='' || $one->phone=='')continue;
            $arr[$i]['franchisee_id'] = $one->id;
            $arr[$i]['franchisee_name'] = $one->legal_entity;
            $arr[$i]['contact_person'] = $one->signed;
            $arr[$i]['franchisee_email'] = $one->legal_email;
            $arr[$i]['phone'] = $one->phone;
            $j = 0;
            foreach ($one->franchiseeGeo as $geo){
                if($geo->exception)continue;
                $arr[$i]['location'][$j]['country'] = $geo->country;
                $arr[$i]['location'][$j]['locality'] = $geo->locality;
                $arr[$i]['location'][$j]['region'] = $geo->administrative_area_level_1;
                $j++;
            }
            $i++;
        }
        $json = json_encode($arr);
        $tmpFile = '/tmp/php3zU3t5'.md5(time()).'.json';
        $fp = fopen($tmpFile, 'w');
        fwrite($fp, $json);
        fclose($fp);
        $_FILES['files1'] = [
            'name' => $tmpFile,
            'type' => 'json',
            'tmp_name' => $tmpFile,
            'error' => 0,
            'size' => '31059'
        ];
        // Defensive code checks not written for the example
        $resource = UploadedFile::getInstanceByName('files1');
        \Yii::$app->resourceManager->save($resource, '/files/franchisee.json');

        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }
}
