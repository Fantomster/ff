<?php

namespace common\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "franchisee".
 *
 * @property int                   $id                        Идентификатор записи в таблице
 * @property string                $signed                    Подписант
 * @property string                $legal_entity              Юридическое название организации
 * @property string                $legal_address             Юридический адрес организации
 * @property string                $legal_email               Официальный электронный ящик
 * @property string                $inn                       ИНН организации
 * @property string                $kpp                       КПП организации
 * @property string                $ogrn                      ОГРН организации
 * @property string                $bank_name                 Наименование банка, в котором обслуживается организация
 * @property string                $bik                       БИК банка, в котором обслуживается организация
 * @property string                $phone                     Телефон организации
 * @property string                $correspondent_account     Корреспондентский счёт банка, в котором обслуживается
 *           организация
 * @property string                $checking_account          Расчётный счёт организации в банке
 * @property string                $info                      Поле для заметок об организации
 * @property string                $created_at                Дата и время создания записи в таблице
 * @property string                $updated_at                Дата и время последнего изменения записи в таблице
 * @property int                   $type_id                   Идентификатор типа франчайзи
 * @property int                   $deleted                   Показатель статуса удаления франчайзи (0 - не удалён, 1 -
 *           удалён)
 * @property string                $fio_manager               ФИО менеджера
 * @property string                $phone_manager             Телефон менеджера
 * @property string                $picture_manager           Аватар менеджера
 * @property int                   $additional_number_manager Дополнительный телефон менеджера
 * @property int                   $receiving_organization    Количество организаций, с которыми работает франчайзи
 * @property int                   $is_public_web             Показатель статуса разрешения публикации на сайте
 *           mixcart.ru (0 - не публиковать, 1 - публиковать)
 * @property FranchiseeAssociate[] $franchiseeAssociates
 * @property FranchiseeGeo[]       $franchiseeGeos
 * @property FranchiseeUser[]      $franchiseeUsers
 * @property User[]                $users
 * @property FranchiseType         $type
 */
class Franchisee extends \yii\db\ActiveRecord
{

    const DEFAULT_AVATAR = '/image/franchisse/no-avatar.jpg';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%franchisee}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_id', 'additional_number_manager', 'receiving_organization'], 'integer'],
            [['type_id'], 'required'],
            [['info'], 'string'],
            [['created_at', 'updated_at', 'deleted'], 'safe'],
            [['signed', 'legal_entity', 'legal_address', 'legal_email', 'inn', 'kpp', 'ogrn', 'bank_name', 'bik', 'phone', 'correspondent_account', 'checking_account', 'picture_manager', 'fio_manager', 'phone_manager'], 'string', 'max' => 255],
            [['is_public_web'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                        => 'ID',
            'info'                      => Yii::t('app', 'common.models.field_two', ['ru' => 'Поле для заметок']),
            'created_at'                => Yii::t('app', 'common.models.created_thrdd', ['ru' => 'Создано']),
            'updated_at'                => Yii::t('app', 'common.models.refreshed_three', ['ru' => 'Обновлено']),
            'signed'                    => Yii::t('app', 'common.models.signer', ['ru' => 'Подписант']),
            'legal_entity'              => Yii::t('app', 'common.models.jur_name_two', ['ru' => 'Юридическое название']),
            'legal_address'             => Yii::t('app', 'common.models.jur_address_two', ['ru' => 'Юридический адрес']),
            'legal_email'               => Yii::t('app', 'common.models.official_email_two', ['ru' => 'Официальный email']),
            'inn'                       => Yii::t('app', 'common.models.inn_two', ['ru' => 'ИНН']),
            'kpp'                       => Yii::t('app', 'common.models.kpp_two', ['ru' => 'КПП']),
            'ogrn'                      => Yii::t('app', 'common.models.ogrn_two', ['ru' => 'ОГРН']),
            'bank_name'                 => Yii::t('app', 'common.models.bank_two', ['ru' => 'Банк']),
            'bik'                       => Yii::t('app', 'common.models.bik_two', ['ru' => 'БИК']),
            'correspondent_account'     => Yii::t('app', 'common.models.rs_two', ['ru' => 'р/с']),
            'checking_account'          => Yii::t('app', 'common.models.ks_two', ['ru' => 'к/с']),
            'phone'                     => Yii::t('app', 'common.models.phone_two', ['ru' => 'Телефон']),
            'picture_manager'           => Yii::t('app', 'common.models.managers_avatar', ['ru' => 'Аватар менеджера']),
            'fio_manager'               => Yii::t('app', 'common.models.managers_fio', ['ru' => 'фио менеджера']),
            'phone_manager'             => Yii::t('app', 'common.models.managers_phone', ['ru' => 'Телефон менеджера']),
            'additional_number_manager' => Yii::t('app', 'common.models.additional_phone_two', ['ru' => 'Добавочный номер менеджера']),
            'is_public_web'             => Yii::t('app', 'Публиковать на mixcart.ru')
        ];
    }

    /**
     * @return bool|false|int
     */
    public function delete()
    {
        $this->deleted = true;
        return $this->save();
    }

    /**
     * @param string $condition
     * @param array  $params
     * @return int
     * @throws \yii\db\Exception
     */
    public static function deleteAll($condition = '', $params = [])
    {
        $command = static::getDb()->createCommand();
        $command->update(static::tableName(), ['deleted' => true], $condition, $params);
        return $command->execute();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFranchiseeAssociates()
    {
        return $this->hasMany(FranchiseeAssociate::className(), ['franchisee_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFranchiseeUsers()
    {
        return $this->hasMany(FranchiseeUser::className(), ['franchisee_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFranchiseeGeo()
    {
        return $this->hasMany(FranchiseeGeo::className(), ['franchisee_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->via('franchiseeUsers');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(FranchiseType::className(), ['id' => 'type_id']);
    }

    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function getFirstOrganizationDate()
    {
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

    /**
     * @param null $dateFrom
     * @param null $dateTo
     * @param null $currencyId
     * @return array ['orderCount' => 1, 'turnover' => 1, 'turnoverCut' => 1]
     */
    public function getMyVendorsStats($dateFrom = null, $dateTo = null, $currencyId = null)
    {
        $ordTable = Order::tableName();
        $faTable = FranchiseeAssociate::tableName();
        $rsrTable = RelationSuppRest::tableName();
        $biTable = BuisinessInfo::tableName();
        $orders = Order::find()
            ->leftJoin($faTable, "$ordTable.vendor_id = $faTable.organization_id")
            ->leftJoin($rsrTable, "$ordTable.vendor_id = $rsrTable.supp_org_id")
            ->leftJoin($biTable, "$ordTable.vendor_id = $biTable.organization_id")
            ->where(["$faTable.franchisee_id" => $this->id, "$ordTable.status" => [OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT, OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR, OrderStatus::STATUS_PROCESSING, OrderStatus::STATUS_DONE]]);

        if ($currencyId) {
            $orders = $orders->andWhere(["currency_id" => $currencyId]);
        }
        $orders = $orders->andFilterWhere([">", "$ordTable.updated_at", $dateFrom])
            ->andFilterWhere(["<", "$ordTable.updated_at", $dateTo]);
        $orderCount = $orders->count();
        $turnover = $orders->sum("$ordTable.total_price");
        $turnoverCut = $orders->andWhere(["$rsrTable.is_from_market" => true])->sum("$ordTable.total_price * $biTable.reward / 100");
        return ['orderCount' => $orderCount, 'turnover' => $turnover, 'turnoverCut' => $turnoverCut];
    }

    /**
     * @return string
     */
    public function getPictureUrl()
    {
        return $this->picture_manager ? $this->getThumbUploadUrl('picture', 'picture') : self::DEFAULT_AVATAR;
    }

    /**
     * @return |null
     */
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

    /**
     * @param bool $is_managers
     * @return array
     */
    public function getFranchiseeEmployees($is_managers = false)
    {
        $dropdown = [];
        $role = ($is_managers) ? Role::ROLE_FRANCHISEE_MANAGER : Role::ROLE_FRANCHISEE_LEADER;
        $models = User::find()
            ->joinWith("franchiseeUser")
            ->joinWith("profile")->select(['user.id', 'profile.full_name'])
            ->where([
                'franchisee_user.franchisee_id' => $this->id,
                'role_id'                       => $role
            ])->all();
        foreach ($models as $model) {
            $dropdown[$model->id] = $model->profile->full_name;
        }

        return $dropdown;
    }

    /**
     * @param bool  $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        $data = $this->find()->where('franchisee.is_public_web = 1')->with('franchiseeGeo')->all();
        $arr = [];
        $i = 0;
        foreach ($data as $one) {
            if ($one->legal_email == '' || $one->phone == '') continue;
            $arr[$i]['franchisee_id'] = $one->id;
            $arr[$i]['franchisee_name'] = $one->legal_entity;
            $arr[$i]['contact_person'] = $one->signed;
            $arr[$i]['franchisee_email'] = $one->legal_email;
            $arr[$i]['phone'] = $one->phone;
            $j = 0;
            $franchiseGeo = $one->franchiseeGeo;
            if (count($franchiseGeo)) {
                foreach ($franchiseGeo as $geo) {
                    if ($geo->exception) continue;
                    $arr[$i]['location'][$j]['country'] = $geo->country;
                    $arr[$i]['location'][$j]['locality'] = $geo->locality;
                    $arr[$i]['location'][$j]['region'] = $geo->administrative_area_level_1;
                    $j++;
                }
            }
            $i++;
        }
        $json = json_encode($arr, JSON_UNESCAPED_UNICODE);
        $tmpFile = '/tmp/php' . time() . '.json';
        $fp = fopen($tmpFile, 'w');
        fwrite($fp, "\xEF\xBB\xBF" . $json);
        fclose($fp);
        $_FILES['files1'] = [
            'name'     => $tmpFile,
            'type'     => 'json',
            'tmp_name' => $tmpFile,
            'error'    => 0,
            'size'     => filesize($tmpFile)
        ];
        // Defensive code checks not written for the example
        $resource = UploadedFile::getInstanceByName('files1');
        if (strpos(Yii::$app->request->hostName, 'test') || strpos(Yii::$app->request->hostName, 'ev') || strpos(Yii::$app->request->hostName, 'ackend')) {
            $file = '/files/franchisee-dev.json';
        } else {
            $file = '/files/franchisee.json';
        }

        \Yii::$app->resourceManagerStatic->save($resource, $file);

        parent::afterSave($insert, $changedAttributes);
    }
}
