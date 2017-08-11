<?php

namespace common\models;

use Yii;
use common\behaviors\UploadBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "agent_attachment".
 *
 * @property integer $id
 * @property integer $agent_request_id
 * @property string $attachment
 *
 * @property AgentRequest $agentRequest
 */
class AgentAttachment extends \yii\db\ActiveRecord
{
    public $resourceCategory = 'agent_requests';
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_attachment';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return ArrayHelper::merge(parent::behaviors(), [
                    [
                        'class' => UploadBehavior::className(),
                        'attribute' => 'attachment',
                        'scenarios' => ['default'],
                        'path' => '@app/web/upload/temp/',
                        'url' => '/upload/temp/',
                    ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['attachment'], 'file'],
            [['agent_request_id'], 'exist', 'skipOnError' => true, 'targetClass' => AgentRequest::className(), 'targetAttribute' => ['agent_request_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'agent_request_id' => 'Agent Request ID',
            'attachment' => 'Attachment',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgentRequest()
    {
        return $this->hasOne(AgentRequest::className(), ['id' => 'agent_request_id']);
    }
}
