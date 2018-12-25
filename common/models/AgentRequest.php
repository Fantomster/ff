<?php

namespace common\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "agent_request".
 *
 * @property int               $id           Идентификатор записи в таблице
 * @property int               $agent_id     Идентификатор пользователя-франчайзи, сотрудничающего с организацией
 * @property string            $target_email Электронный ящик организации, от которой поступила заявка на присоединение
 *           к франшизе
 * @property string            $comment      Комментарий франчайзи о заявке
 * @property int               $is_processed Показатель статуса состояния заявки на присоединение организации к
 *           франшизе (0 - не в процессе, 1 - в процессе)
 * @property string            $created_at   Дата и время создания записи в таблице
 * @property string            $updated_at   Дата и время последнего изменения записи в таблице
 *
 * @property AgentAttachment[] $agentAttachments
 * @property User              $agent
 * @property Profile           $profile
 * @property Franchise         $franchise
 */
class AgentRequest extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%agent_request}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
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
     * {@inheritdoc}
     */
    public function rules()
    {
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
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'agent_id'     => 'Agent ID',
            'target_email' => 'Target Email',
            'comment'      => 'Comment',
            'is_processed' => 'Is Processed',
            'created_at'   => 'Created At',
            'updated_at'   => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgent()
    {
        return $this->hasOne(User::className(), ['id' => 'agent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'agent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachments()
    {
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

    /**
     * @return false|int|void
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete()
    {
        foreach ($this->attachments as $attachment) {
            $attachment->delete();
        }
        parent::delete();
    }

}
