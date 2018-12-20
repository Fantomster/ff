<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2018-12-04
 * Time: 11:41
 */

namespace common\models\egais;

/**
 * @property int $id [int(11)]
 * @property string $type [varchar(255)]
 */
class EgaisTypeChargeOn extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'egais_type_charge_on';
    }

    /**
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return \Yii::$app->get('db_api');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'required'],
            [['type'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type' => 'Type'
        ];
    }
}