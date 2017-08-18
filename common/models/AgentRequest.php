<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "agent_request".
 *
 * @property integer $id
 * @property integer $agent_id
 * @property string $target_email
 * @property string $comment
 * @property integer $is_processed
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $agent
 * @property AgentAttachment[] $attachments
 */
class AgentRequest extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'agent_request';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return ArrayHelper::merge(parent::behaviors(), [
                    'timestamp' => [
                        'class' => 'yii\behaviors\TimestampBehavior',
                        'value' => function ($event) {
                            return gmdate("Y-m-d H:i:s");
                        },
                    ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['agent_id', 'target_email'], 'required'],
            [['target_email'], 'email'],
            [['agent_id', 'is_processed'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['target_email', 'comment'], 'string', 'max' => 255],
            [['agent_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['agent_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'agent_id' => 'Agent ID',
            'target_email' => 'Target Email',
            'comment' => 'Comment',
            'is_processed' => 'Is Processed',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgent() {
        return $this->hasOne(User::className(), ['id' => 'agent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachments() {
        return $this->hasMany(AgentAttachment::className(), ['agent_request_id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFranchisee()
    {
        return $this->hasOne(Franchisee::className(), ['id' => 'franchisee_id'])
            ->viaTable('franchisee_user', ['user_id' => 'agent_id']);
    }


    public function delete() {
        foreach ($this->attachments as $attachment) {
            $attachment->delete();
        }
        parent::delete();
    }
}
