<?php

namespace common\models\edi;

use common\models\User;
use Yii;

/**
 * This is the model class for table "edi_roaming_map".
 *
 * @property int $id Идентификатор записи в таблице
 * @property int $sender_edi_organization_id Идентификатор связи ресторана в таблице edi_organization
 * @property int $recipient_edi_organization_id Идентификатор связи поставщика в таблице edi_organization
 * @property int $created_by_id Идентификатор создателя записи
 *
 * @property User $createdBy
 * @property EdiOrganization $recipientEdiOrganization
 * @property EdiOrganization $senderEdiOrganization
 */
class EdiRoamingMap extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'edi_roaming_map';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sender_edi_organization_id', 'recipient_edi_organization_id', 'created_by_id'], 'integer'],
            [['created_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by_id' => 'id']],
            [['recipient_edi_organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => EdiOrganization::className(), 'targetAttribute' => ['recipient_edi_organization_id' => 'id']],
            [['sender_edi_organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => EdiOrganization::className(), 'targetAttribute' => ['sender_edi_organization_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор записи в таблице',
            'sender_edi_organization_id' => 'Идентификатор связи ресторана в таблице edi_organization',
            'recipient_edi_organization_id' => 'Идентификатор связи поставщика в таблице edi_organization',
            'created_by_id' => 'Идентификатор создателя записи',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecipientEdiOrganization()
    {
        return $this->hasOne(EdiOrganization::className(), ['id' => 'recipient_edi_organization_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSenderEdiOrganization()
    {
        return $this->hasOne(EdiOrganization::className(), ['id' => 'sender_edi_organization_id']);
    }
}
