<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2018-12-10
 * Time: 12:21
 */

namespace common\models\egais;

/**
 * @property int $id [int(11)]
 * @property int $org_id [int(11)]
 * @property string $reply_id [varchar(255)]
 * @property string $status [varchar(255)]
 * @property int $created_at [timestamp]
 * @property int $updated_at [timestamp]
 */
class EgaisQueryRests extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'egais_query_rests';
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
            [['org_id', 'reply_id', 'status'], 'required'],
            [['org_id', 'status'], 'integer'],
            [['reply_id'], 'string'],
            [['created_at', 'updated_at'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'org_id' => 'Organization id',
            'reply_id' => 'Reply id',
            'status' => 'Status',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at'
        ];
    }
}