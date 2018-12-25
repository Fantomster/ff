<?php

namespace common\models;

/**
 * This is the model class for table "catalog_temp".
 *
 * @property int                  $id           Идентификатор записи в таблице
 * @property int                  $cat_id       Идентификатор каталога
 * @property int                  $user_id      Идентификатор пользователя, соаздавшего временный каталог
 * @property string               $excel_file   Наименование файла Excel, содержащего временный каталог товаров
 * @property string               $mapping      Номера столбцов в файле и атрибуты товаров, которые в этих столбцах
 *           содержатся
 * @property string               $index_column Наименование столбца в файле, по которому индексируется временный
 *           каталог
 * @property string               $created_at   Дата и время создания записи в таблице
 * @property Catalog              $cat
 * @property CatalogTempContent[] $catalogTempContents
 */
class CatalogTemp extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%catalog_temp}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cat_id', 'user_id', 'excel_file'], 'required'],
            [['cat_id', 'user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['excel_file', 'mapping', 'index_column'], 'string', 'max' => 255],
            [['cat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalog::className(), 'targetAttribute' => ['cat_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'      => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                ],
                'value'      => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'cat_id'       => 'Cat ID',
            'user_id'      => 'User ID',
            'excel_file'   => 'Excel File',
            'mapping'      => 'Mapping',
            'index_column' => 'Index Column',
            'created_at'   => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCat()
    {
        return $this->hasOne(Catalog::className(), ['id' => 'cat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogTempContents()
    {
        return $this->hasMany(CatalogTempContent::className(), ['temp_id' => 'id']);
    }

}
