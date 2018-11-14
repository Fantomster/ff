<?php

namespace api\common\models\egais;

use Yii;

/**
 * This is the model class for table "egais_settings".
 *
 * @property int $id
 * @property int $org_id id ресторана
 * @property string $egais_url url по которому нужно стучаться в егаис
 * @property string $fsrar_id идентификатор  организации  в  ФС  РАР
 * @property string $created_at Дата создания записи
 * @property string $updated_at Дата последнего изменения записи
 */
class EgaisSettings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'egais_settings';
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
            [['org_id', 'egais_url', 'fsrar_id'], 'required'],
            [['org_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['egais_url', 'fsrar_id'], 'string', 'max' => 255],
            ['fsrar_id', 'match', 'pattern' => '/\d+/'],
            ['egais_url', 'match', 'pattern' => '/^http[s]{0,1}:\/\/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d{2,6}$/'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'org_id' => Yii::t('app', 'Org ID'),
            'egais_url' => Yii::t('app', 'Egais Url'),
            'fsrar_id' => Yii::t('app', 'Fsrar ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function setSettings($request, $orgId)
    {
        if (isset($request['egaisUrl']) && isset($request['fsrarId']) && $orgId) {
            $ifExist = $this::find()->where(['org_id' => $orgId])->one();
            if ($ifExist) {
                $this->egais_url = $request['egaisUrl'];
                $this->fsrar_id = $request['fsrarId'];
                $this->updated_at = date('Y-m-d h:i:s');
                if ($this->validate(['egais_url', 'fsrar_id', 'updated_at'])) {
                    if (!$ifExist::updateAll(
                        ['egais_url' => $request['egaisUrl'],
                            'fsrar_id' => $request['fsrarId'],
                            'updated_at' => date('Y-m-d h:i:s')],
                        ['=', 'org_id', $orgId])
                    ) {
                        $result = 'Не получилось сохранить в базу!!!';
                    } else {
                        $result = true;
                    }
                } else {
                    $result = 'url ЕГАИСа или FSRAR_ID заданы неверно!!!';
                }
            } else {
                $this->egais_url = $request['egaisUrl'];
                $this->fsrar_id = $request['fsrarId'];
                $this->org_id = $orgId;
                $this->created_at = date('Y-m-d h:i:s');
                $this->updated_at = date('Y-m-d h:i:s');
                if ($r = $this->validate()) {
                    if ($this->save()) {
                        $result = true;
                    } else {
                        $result = 'Не получилось сохранить в БД!!!';
                    }
                } else {
                    $result = 'url ЕГАИСа или FSRAR_ID заданы неверно!!!';
                }
            }
        } else {
            $result = 'Отсутствуют нужные параметры!!!';
        }
        return ['result' => $result];
    }
}
