<?php

/**
 * Class Migration
 *
 * @package   api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-10-02
 * @author    Mixcart
 * @module    WEB-API
 * @version   2.0
 */

namespace common\models;

use api_web\components\FireBase;
use api_web\components\Registry;
use api_web\modules\integration\classes\Integration;
use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "organization_dictionary".
 *
 * @property int             $id           Идентификатор записи
 * @property int             $outer_dic_id Код словаря
 * @property int             $org_id       Код организации
 * @property int             $status_id    ID статуса - выгружен, ошибка, не выгружался
 * @property int             $count        Количество записей в словаре
 * @property string          $created_at   Дата создания
 * @property string          $updated_at   Дата обновления
 * @property Organization    $org
 * @property OuterDictionary $outerDic
 */
class OrganizationDictionary extends ActiveRecord
{
    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_ERROR = 2;
    const STATUS_SEND_REQUEST = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%organization_dictionary}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['outer_dic_id', 'org_id'], 'required'],
            [['outer_dic_id', 'org_id', 'status_id', 'count'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::class, 'targetAttribute' => ['org_id' => 'id']],
            [['outer_dic_id'], 'exist', 'skipOnError' => true, 'targetClass' => OuterDictionary::class, 'targetAttribute' => ['outer_dic_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'Идентификатор записи',
            'outer_dic_id' => 'Код словаря',
            'org_id'       => 'Код организации',
            'status_id'    => 'ID статуса - выгружен, ошибка, не выгружался',
            'count'        => 'Количество записей в словаре',
            'created_at'   => 'Дата создания',
            'updated_at'   => 'Дата обновления',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrg()
    {
        return $this->hasOne(Organization::class, ['id' => 'org_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOuterDic()
    {
        return $this->hasOne(OuterDictionary::class, ['id' => 'outer_dic_id']);
    }

    /**
     * @param int $count
     * @return bool
     */
    public function successSync(int $count)
    {
        $this->status_id = self::STATUS_ACTIVE;
        $this->count = $count;
        $this->updated_at = \gmdate('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * @return bool
     */
    public function errorSync()
    {
        $this->status_id = self::STATUS_ERROR;
        $this->updated_at = \gmdate('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * @throws \Exception
     */
    public function noticeToFCM(): void
    {
        $consumerName = Integration::$service_map[$this->outerDic->service_id] . ucfirst($this->outerDic->name) . 'Sync';
        $consumerFullName = 'console\modules\daemons\classes\\' . $consumerName;
        $queueName = $consumerName . '_' . $this->org_id;
        $arFB = [
            'dictionaries',
            'queue' => $queueName,
        ];

        $lastExec = new \DateTime();
        $plainExec = null;
        if (in_array($this->outerDic->service_id, [Registry::IIKO_SERVICE_ID, Registry::TILLYPAD_SERVICE_ID])) {
            $plainExec = date('Y-m-d H:i:s', $lastExec->getTimestamp() + $consumerFullName::$timeout);
        }
        \Yii::$app->language = $this->org->lang ?? 'ru';

        FireBase::getInstance()->update($arFB, [
            'last_executed'  => $lastExec->format('Y-m-d H:i:s'),
            'plain_executed' => $plainExec,
            'status_text'    => $this->statusText,
            'status_id'      => $this->status_id,
            'count'          => $this->count ?? 0
        ]);
    }

    /**
     * @param $status
     * @param $org_id
     * @param $service_id
     */
    public static function updateUnitDictionary($status, $org_id, $service_id)
    {
        $unitId = self::getUnitIdByServiceId('unit', $service_id);

        $dictionaryUnit = self::findOne([
            'org_id'       => $org_id,
            'outer_dic_id' => $unitId
        ]);

        if (empty($dictionaryUnit)) {
            $dictionaryUnit = new self([
                'org_id'       => $org_id,
                'outer_dic_id' => $unitId,
                'status_id'    => $status,
                'count'        => 0
            ]);
        }

        if ($status == self::STATUS_ACTIVE) {
            $count = OuterUnit::find()->where([
                'org_id'     => $org_id,
                'service_id' => $service_id,
                'is_deleted' => 0
            ])->count();
            $dictionaryUnit->count = (int)$count;
        } else {
            $dictionaryUnit->updated_at = \gmdate('Y-m-d H:i:s');
        }

        $dictionaryUnit->status_id = $status;
        $dictionaryUnit->save();
    }

    /**
     * Статус справочника текстом
     *
     * @return mixed
     */
    public function getStatusText()
    {
        return self::getStatusTextList()[$this->status_id ?? 0];
    }

    /**
     * Список статусов справочников
     *
     * @return array
     */
    public static function getStatusTextList()
    {
        return [
            self::STATUS_DISABLED     => \Yii::t('app', 'organization_dictionary.status.disabled'),
            self::STATUS_ACTIVE       => \Yii::t('app', 'organization_dictionary.status.active'),
            self::STATUS_ERROR        => \Yii::t('app', 'organization_dictionary.status.error'),
            self::STATUS_SEND_REQUEST => \Yii::t('app', 'organization_dictionary.status.send_request')
        ];
    }

    private static function getUnitIdByServiceId($name, $service_id)
    {
        $unit = OuterDictionary::findOne([
            'name'       => $name,
            'service_id' => $service_id
        ]);

        return $unit->id;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        if ($this->outerDic->name == 'product_type') {
            $result = (new Query())
                ->select([
                    'selected' => 'coalesce(SUM(opts.selected), 0)',
                    'all'      => 'coalesce(COUNT(*), 0)'
                ])->distinct()
                ->from(OuterProductTypeSelected::tableName() . " opts")
                ->innerJoin(OuterProductType::tableName() . " opt", "opt.id = opts.outer_product_type_id")
                ->where([
                    'opt.service_id' => $this->outerDic->service_id,
                    'opts.org_id'    => $this->org_id
                ])->one(\Yii::$app->db_api);

            return $result['selected'] . "/" . $result['all'];
        } else {
            return $this->count ?? 0;
        }
    }
}
