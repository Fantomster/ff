<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use common\behaviors\UploadBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "relation_supp_rest".
 *
 * @property int          $id                 Идентификатор записи в таблице
 * @property int          $rest_org_id        Идентификатор организации-ресторана
 * @property int          $supp_org_id        Идентификатор организации-поставщика
 * @property int          $cat_id             Идентификатор каталога товаров
 * @property int          $invite             Показатель наличия связи с поставщиком (0 - нет связи, 1 - есть связь)
 * @property int          $discount_product   "Скидка на товары" (в процентах) - применяется для всех товаров прайса
 *           поставщика
 * @property string       $created_at         Дата и время создания записи в таблице
 * @property string       $updated_at         Дата и время последнего изменения записи в таблице
 * @property int          $status             Показатель состояния активности каталога (0 - не активен, 1 - активен)
 * @property string       $uploaded_catalog   Название файла, содержащего каталог
 * @property int          $uploaded_processed Показатель состояния внедрения каталога в систему (0 - каталог не
 *           внедрён, 1 - каталог внедрён)
 * @property int          $is_from_market     Показатель состояния получения каталога из Маркета (0 - получен не из
 *           Маркета, 1 - получен из Маркета)
 * @property int          $deleted            Показатель состояния удаления каталога (0 - не удалён, 1 - удалён)
 * @property Catalog      $catalog
 * @property Organization $client
 * @property Organization $restOrg
 * @property Order        $lastOrder
 */
class RelationSuppRest extends \yii\db\ActiveRecord
{

    const PAGE_CLIENTS = 3;
    const PAGE_CATALOG = 2;
    const PAGE_SUPPLIERS = 1;
    const CATALOG_STATUS_OFF = 0;
    const CATALOG_STATUS_ON = 1;
    const INVITE_OFF = 0;
    const INVITE_ON = 1;
    const UPLOADED_NOT_PROCESSED = 0;
    const UPLOADED_PROCESSED = 1;

    public $resourceCategory = 'uploaded_catalogs';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%relation_supp_rest}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'timestamp'  => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
            [
                'class'     => UploadBehavior::class,
                'attribute' => 'uploaded_catalog',
                'scenarios' => ['default'],
                'path'      => '@app/web/upload/temp/',
                'url'       => '/upload/temp/',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rest_org_id', 'supp_org_id'], 'required'],
            [['rest_org_id', 'supp_org_id', 'cat_id', 'status', 'discount_product'], 'integer'],
            [['uploaded_catalog'], 'file'],
            [['uploaded_processed', 'vendor_manager_id'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'rest_org_id'      => 'Rest Org ID',
            'supp_org_id'      => 'Supp Org ID',
            'cat_id'           => \Yii::t('app', 'common.models.catalogue', ['ru' => 'Каталог']),
            'discount_product' => "Скидка на товары (в процентах) - применяется для всех товаров прайса поставщика"
        ];
    }

    /**
     * @param $params
     * @param $currentUser
     * @param $const
     * @return ActiveDataProvider
     */
    public function search($params, $currentUser, $const)
    {
        $vendor_id = Yii::$app->request->get('vendor_id');
        $org_id = !empty($vendor_id) ? $vendor_id : $currentUser->organization_id;
        if ($const == RelationSuppRest::PAGE_CLIENTS) {
            $query = RelationSuppRest::find()
                ->where(['supp_org_id' => $org_id]);
        }
        if ($const == RelationSuppRest::PAGE_SUPPLIERS) {
            $query = RelationSuppRest::find()
                ->where(['rest_org_id' => $org_id]);
        }
        if ($const == RelationSuppRest::PAGE_CATALOG) {
            $query = RelationSuppRest::find()
                ->where(['supp_org_id' => $org_id])
                ->andWhere(['invite' => RelationSuppRest::INVITE_ON]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'attributes' => [
                'id',
                'rest_org_id',
                'supp_org_id',
                'cat_id',
                'invite',
                'status',
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        return $dataProvider;
    }

    /**
     * @param $id
     * @return int|string
     */
    public static function row_count($id)
    {
        $count = RelationSuppRest::find()
            ->where(['cat_id' => $id])
            ->count();
        return $count;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalog()
    {
        return $this->hasOne(Catalog::class, ['id' => 'cat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Organization::class, ['id' => 'supp_org_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Organization::class, ['id' => 'rest_org_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLastOrder()
    {
        return $this->hasOne(Order::class, ['vendor_id' => 'supp_org_id', 'client_id' => 'rest_org_id'])
            ->orderBy([Order::tableName() . ".updated_at" => SORT_DESC]);
    }
}
