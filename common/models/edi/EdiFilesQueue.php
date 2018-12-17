<?php

namespace common\models\edi;

use Yii;

/**
 * This is the model class for table "edi_files_queue".
 *
 * @property int    $id              Идентификатор записи в таблице
 * @property string $name            Название файла xml либо идентификатор файла
 * @property int    $organization_id Идентификатор организации, от которой получен документ
 * @property int    $status          Статус обработки документа (1 - новый, 2 - обрабатывается, 3 - ошибка, 4 -
 *           обработан)
 * @property string $error_text      Текст ошибки при получении файла или обработке документа
 * @property string $created_at      Дата и время создания записи в таблице
 * @property string $updated_at      Дата и время последнего изменения записи в таблице
 * @property string $json_data       Данные в формате JSON от Leradata
 */
class EdiFilesQueue extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'edi_files_queue';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'organization_id'], 'required'],
            [['organization_id', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['json_data'], 'string'],
            [['name', 'error_text'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'Идентификатор записи в таблице',
            'name'            => 'Название файла xml либо идентификатор файла',
            'organization_id' => 'Идентификатор организации, от которой получен документ',
            'status'          => 'Статус обработки документа (1 - новый, 2 - обрабатывается, 3 - ошибка, 4 - обработан)',
            'error_text'      => 'Текст ошибки при получении файла или обработке документа',
            'created_at'      => 'Дата и время создания записи в таблице',
            'updated_at'      => 'Дата и время последнего изменения записи в таблице',
            'json_data'       => 'Данные в формате JSON от Leradata',
        ];
    }

    //auto created_at && updated_at
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }
}
