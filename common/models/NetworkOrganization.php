<?php

namespace common\models;

/**
 * This is the model class for table "network_organization".
 *
 * @property int          $id              Идентификатор записи в таблице
 * @property int          $organization_id Идентификатор организации, являющейся подчинённой
 * @property int          $parent_id       Идентификатор "родительской" организации
 * @property string       $created_at      Дата и время создания записи в таблице
 * @property string       $updated_at      Дата и время последнего изменения записи в таблице
 * @property Organization $organization
 * @property Organization $parent
 */
class NetworkOrganization extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%network_organization}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'parent_id'], 'required'],
            [['organization_id', 'parent_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['parent_id' => 'id']],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'organization_id' => 'Organization ID',
            'parent_id'       => 'Parent ID',
            'created_at'      => 'Created At',
            'updated_at'      => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Organization::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }
}
