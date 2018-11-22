<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "edi_unit".
 *
 * @property int    $id
 * @property string $name        Название в системе MixCart
 * @property string $unit_code   Код во внешней системе EDI
 * @property string $description Описание единицы измерения
 */
class EdiUnit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'edi_unit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'unit_code'], 'string', 'max' => 30],
            [['description'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'name'        => 'Название в системе MixCart',
            'unit_code'   => 'Код во внешней системе EDI',
            'description' => 'Описание единицы измерения',
        ];
    }

    public function getInnerName($outerName)
    {
        $unit = self::findOne(['unit_code' => $outerName]);
        if ($unit) {
            return $unit->name;
        }
        return $outerName;
    }

    public function getOuterName($innerName)
    {
        $unit = self::findOne(['name' => $innerName]);
        if ($unit) {
            return $unit->unit_code;
        }
        return $innerName;
    }
}
