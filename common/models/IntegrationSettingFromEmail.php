<?php

namespace common\models;

/**
 * This is the model class for table "integration_setting_from_email".
 *
 * @property int                $id              Идентификатор записи в таблице
 * @property int                $organization_id Идентификатор организации - получателя накладных
 * @property string             $server_type     Тип почтового сервера для получения накладных
 * @property string             $server_host     Почтовый сервер для получения накладных
 * @property int                $server_port     Порт почтового сервера для получения накладных
 * @property int                $server_ssl      Флажок использования SSL для получения накладных
 * @property string             $user            Логин для входа на почтовый сервер для получения накладных
 * @property string             $password        Пароль для входа на почтовый сервер для получения накладных
 * @property int                $is_active       Флажок показателя активности данного почтового ящика для получения
 *           накладных
 * @property string             $created_at      Дата и время создания записи в таблице
 * @property string             $updated_at      Дата и время последнего изменения записи в таблице
 * @property string             $language        Двухбуквенное обозначение языка, на котором ведётся переписка
 * @property int                $version         Версия приложения MixCart
 * @property Organization       $organization
 * @property IntegrationInvoice $invoice
 * @property string             $countCharsPassword
 */
class IntegrationSettingFromEmail extends \yii\db\ActiveRecord
{
    private $_old_password;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%integration_setting_from_email}}';
    }

    public function afterFind()
    {
        $this->_old_password = $this->password;
        parent::afterFind();
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function beforeSave($insert)
    {
        $password = \Yii::$app->get('encode')->encrypt($this->password, $this->user);
        $checkPassword = trim($this->password, '*');
        if (!$this->isNewRecord && empty($checkPassword)) {
            $password = $this->_old_password;
        }
        $this->password = $password;

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function () {
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
            [['organization_id', 'server_type', 'server_host', 'server_port', 'user', 'password'], 'required'],
            [['organization_id', 'server_port', 'server_ssl', 'is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['server_type', 'server_host', 'user', 'password'], 'string', 'max' => 255],
            [['language'], 'string', 'max' => 3],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::class, 'targetAttribute' => ['organization_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'organization_id' => 'Организация',
            'server_type'     => 'Тип сервера',
            'server_host'     => 'Сервер',
            'server_port'     => 'Порт',
            'server_ssl'      => 'SSL',
            'user'            => 'Логин',
            'password'        => 'Пароль',
            'is_active'       => 'Активен',
            'created_at'      => 'Дата создания',
            'updated_at'      => 'Дата изменения',
            'language'        => 'Язык',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasMany(IntegrationInvoice::class, ['integration_setting_from_email_id' => 'id']);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getCountCharsPassword(): string
    {
        $stars = '';
        if (!empty($this->password)) {
            $countChars = iconv_strlen(\Yii::$app->get('encode')->decrypt($this->password, $this->user));
            $stars = str_pad("", $countChars, "*");
        }

        return $stars;
    }
}
