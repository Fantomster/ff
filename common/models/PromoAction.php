<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "{{%promo_action}}".
 *
 * @property int    $id         ID промо-акции
 * @property string $name       Название промо-акции
 * @property string $code       Код промо-акции
 * @property string $title      Заголовок в сообщении
 * @property string $message    Содержание сообщения
 * @property string $created_at Дата создания акции
 * @property string $updated_at Дата изменения акции
 */
class PromoAction extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%promo_action}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'title', 'message'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 40],
            [['code'], 'string', 'max' => 20],
            [['title'], 'string', 'max' => 100],
            [['message'], 'string', 'max' => 1000],
            [['message'], 'checkCode'],
        ];
    }


    public function checkCode()
    {
        if(!empty($this->code)) {
            if(self::find()->where('id <> :id and code = :code', [':id' => $this->id, ':code' => $this->code])->one() != null) {
                $this->addError('code','Акция с таким кодом уже есть в системе');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID промо-акции',
            'name'       => 'Название промо-акции',
            'code'       => 'Код промо-акции',
            'title'      => 'Заголовок в сообщении',
            'message'    => 'Содержание сообщения',
            'created_at' => 'Дата создания акции',
            'updated_at' => 'Дата изменения акции',
        ];
    }


}
