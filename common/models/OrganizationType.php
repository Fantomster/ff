<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "organization_type".
 *
 * @property int            $id
 * @property string         $name
 * @property Organization[] $organizations
 */
class OrganizationType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%organization_type}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'   => 'ID',
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganizations()
    {
        return $this->hasMany(Organization::className(), ['type_id' => 'id']);
    }

    /**
     * array of all organization types
     *
     * @return array
     */
    public static function getList()
    {
        $models = OrganizationType::find()
            ->select(['id', 'name'])
            ->asArray()
            ->all();

        return ArrayHelper::map($models, 'id', 'name');
    }
}
