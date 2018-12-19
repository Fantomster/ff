<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
use common\behaviors\UploadBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "relation_supp_rest".
 *
 * @property integer      $id
 * @property integer      $rest_org_id
 * @property integer      $supp_org_id
 * @property integer      $cat_id
 * @property integer      $invite
 * @property integer      $status
 * @property string       $created_at
 * @property string       $updated_at
 * @property string       $uploaded_catalog
 * @property booolean     $uploaded_processed
 * @property booolean     $is_from_market
 * @property booolean     $deleted
 * @property Catalog      $catalog
 * @property Organization $client
 * @property Organization $vendor
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
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'relation_supp_rest';
    }

    /**
     * @inheritdoc
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
            [
                'class'     => UploadBehavior::className(),
                'attribute' => 'uploaded_catalog',
                'scenarios' => ['default'],
                'path'      => '@app/web/upload/temp/',
                'url'       => '/upload/temp/',
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rest_org_id', 'supp_org_id'], 'required'],
            [['rest_org_id', 'supp_org_id', 'cat_id', 'status'], 'integer'],
            [['uploaded_catalog'], 'file'],
            [['uploaded_processed', 'vendor_manager_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'rest_org_id' => 'Rest Org ID',
            'supp_org_id' => 'Supp Org ID',
            'cat_id'      => Yii::t('app', 'common.models.catalogue', ['ru' => 'Каталог']),
        ];
    }

//    public function delete() {
//        $this->deleted = true;
//        return $this->save();
//    }
//    
//    public static function deleteAll($condition = '', $params = array()) {
//        $command = static::getDb()->createCommand();
//        $command->update(static::tableName(), ['deleted' => true], $condition, $params);
//
//        return $command->execute();
//    }

    public static function GetRelationCatalogs()
    {
        $catalog = RelationSuppRest::find()
            ->select(['id', 'cat_id', 'rest_org_id', 'invite'])
            ->where(['supp_org_id' => User::getOrganizationUser(Yii::$app->user->id)])
            ->andWhere(['not', ['cat_id' => null]])
            ->all();
        return $catalog;
    }

    /* public static function getStatusRelation($sup_org_id,$rest_org_id){
      $catalogName = RelationSuppRest::find()
      ->where(['sup_org_id' => $sup_org_id,'rest_org_id'=>$rest_org_id])->one();
      return $catalogName->status;
      } */

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
        return $this->hasOne(Catalog::className(), ['id' => 'cat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Organization::className(), ['id' => 'rest_org_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLastOrder()
    {
        return $this->hasOne(Order::className(), ['vendor_id' => 'supp_org_id', 'client_id' => 'rest_org_id'])->orderBy(["`order`.updated_at" => SORT_DESC]);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

    }

}
