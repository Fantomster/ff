<?php

namespace common\models\edi;

use common\models\Organization;
use Yii;

/**
 * This is the model class for table "edi_organization".
 *
 * @property int $id
 * @property int $organization_id
 * @property int $gln_code
 * @property string $login
 * @property string $pass
 * @property string $int_user_id intUserID - ID юзера в системе Leradata
 * @property string $token Токен юзера в системе Leradata
 * @property int $provider_id ID EDI провайдера
 * @property int $provider_priority Приоритет провайдера
 */
class EdiOrganization extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'edi_organization';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'gln_code', 'provider_id', 'provider_priority'], 'integer'],
            [['gln_code'], 'unique'],
            [['login', 'pass'], 'string', 'max' => 255],
            [['int_user_id'], 'string', 'max' => 50],
            [['token'], 'string', 'max' => 150],
            [['organization_id', 'provider_id'], 'unique', 'targetAttribute' => ['organization_id', 'provider_id'], 'when' => function($model) {
        return $model->isNewRecord;
    }]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_id' => 'Organization ID',
            'gln_code' => 'Gln Code',
            'login' => 'Login',
            'pass' => 'Pass',
            'int_user_id' => 'intUserID - ID юзера в системе Leradata',
            'token' => 'Токен юзера в системе Leradata',
            'provider_id' => 'ID EDI провайдера',
            'provider_priority' => 'Приоритет провайдера',
        ];
    }

    public function getEdiProvider(){
        return $this->hasOne(EdiProvider::className(), ['id' => 'provider_id']);
    }

    public function getOrganization(){
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }
}
