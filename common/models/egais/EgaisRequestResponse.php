<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2018-12-04
 * Time: 14:45
 */

namespace common\models\egais;

/**
 * @property int $id [int(11)]
 * @property int $org_id [int(11)]
 * @property int $act_id [int(11)]
 * @property int $created_at [timestamp]
 * @property int $doc_id [int(11)]
 * @property string $operation_name [varchar(250)]
 * @property string $result [varchar(250)]
 * @property string $conclusion [varchar(250)]
 * @property string $date [varchar(255)]
 * @property string $comment
 * @property string $doc_type [varchar(255)]
 */
class EgaisRequestResponse extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'egais_request_response';
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
            [['org_id', 'act_id', 'doc_id'], 'required'],
            [['org_id', 'act_id', 'doc_id'], 'integer'],
            [
                [
                    'operation_name',
                    'result',
                    'conclusion',
                    'date',
                    'comment',
                    'doc_type'
                ], 'string'
            ],
            [['created_at'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'org_id' => 'Organization id',
            'act_id' => 'Act id',
            'doc_id' => 'Document id',
            'doc_type' => 'Document type',
            'date' => 'Date',
            'operation_name' => 'Operation name',
            'result' => 'Result',
            'conclusion' => 'Conclusion',
            'comment' => 'Comment',
            'created_at' => 'Created at'
        ];
    }
}