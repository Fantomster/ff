<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "relation_category".
 *
 * @property int          $id          Идентификатор записи в таблице
 * @property int          $category_id Идентификатор категории товаров
 * @property int          $rest_org_id Идентификатор организации-ресторана
 * @property int          $supp_org_id Идентификатор организации-поставщика
 * @property string       $created_at  Дата и время создания записи в таблице
 * @property string       $updated_at  Дата и время последнего изменения записи в таблице
 * @property Category     $category
 * @property Organization $vendor
 * @property Organization $client
 */
class RelationCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%relation_category}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['supp_rest_id'], 'required'],
            [['supp_rest_id', 'category_id'], 'integer'],
            [['rest_supp_id'], 'required'],
            [['rest_supp_id', 'category_id'], 'integer'],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'supp_org_id' => 'Relation Supp org ID',
            'rest_org_id' => 'Relation Rest org ID',
            'category_id' => Yii::t('app', 'common.models.category_three', ['ru' => 'Категория']),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Organization::className(), ['id' => 'rest_org_id']);
    }
}
