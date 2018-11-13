<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "edi_provider".
 *
 * @property int $id
 * @property string $name
 * @property string $legal_name
 * @property string $web_site
 *
 * @property RoamingMap $roamingMap
 */
class EdiProvider extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'edi_provider';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['legal_name'], 'string', 'max' => 255],
            [['web_site'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'legal_name' => 'Legal Name',
            'web_site' => 'Web Site',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoamingMap()
    {
        return $this->hasOne(RoamingMap::className(), ['id' => 'id']);
    }
}
