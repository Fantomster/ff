<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "rk_actions".
 *
 * @property int $id Идентификатор записи в таблице
 * @property string $action Описание действия
 * @property int $session Сессия, во время которой делался последний запрос актуальных данных о лицензиях UCS
 * @property string $created Дата и время, когда делался последний запрос актуальных данных о лицензиях UCS
 * @property int $result Результат (не используется)
 * @property string $ip ip-адрес, с которого делался последний запрос актуальных данных о лицензиях UCS
 * @property string $comment Комментарий (не используется)
 */
class RkActions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%rk_actions}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['session', 'result'], 'integer'],
            [['created'], 'safe'],
            [['action'], 'string', 'max' => 120],
            [['ip'], 'string', 'max' => 45],
            [['comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор записи в таблице',
            'action' => 'Описание действия',
            'session' => 'Сессия, во время которой делался последний запрос актуальных данных о лицензиях UCS',
            'created' => 'Дата и время, когда делался последний запрос актуальных данных о лицензиях UCS',
            'result' => 'Результат (не используется)',
            'ip' => 'ip-адрес, с которого делался последний запрос актуальных данных о лицензиях UCS',
            'comment' => 'Комментарий (не используется)',
        ];
    }
}
