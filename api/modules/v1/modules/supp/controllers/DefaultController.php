<?php

namespace api\modules\v1\modules\supp\controllers;

use common\models\Organization;
use Yii;
use yii\web\Controller;
use api\common\models\ApiAccess;
use api\common\models\ApiSession;
use api\common\models\ApiActions;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\MpEd;
use common\models\Catalog;
use common\models\RelationSuppRest;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */
class DefaultController extends Controller
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;
    private $username;
    private $password;
    private $nonce;
    private $extimefrom;
    private $ip;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->ip = Yii::$app->request->getUserIP();
            return true;
        }
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'wsdl' => [
                'class' => 'mongosoft\soapserver\Action',
                'serviceOptions' => [
                    'disableWsdlMode' => false,
                ]
            ],
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * delete Item from Personal Catalog
     * @param $sessionId
     * @param $nonce
     * @param $cat_fid
     * @param $fid
     * @return mixed
     * @soap
     */
    public function deleteItemfromPersonalCatalog($sessionId, $nonce, $cat_fid, $fid)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $persCat = Catalog::find()
                ->where("supp_org_id = $org->org and type = 2 and id= $cat_fid")
                ->one();

            if (!isset($persCat)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal catalog not found', $this->ip);
                return 'Catalog error. Personal catalog not found.';
            }

            $priceModel = CatalogGoods::find()->andwhere('base_goods_id=' . $fid)->andwhere('cat_id=' . $persCat->id)->one();

            if (!$priceModel) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Item with FID is not found in catalog', $this->ip);
                return 'Product error. Item with FID not found in personal catalog';
            }

            if (!$priceModel->delete()) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Internal error. Model Item not deleted', $this->ip);
                return $priceModel->getErrors();
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK: ' . $priceModel->id . ' is deleted', $this->ip);
                return 'OK.DELETEDFID:' . $fid;
            }
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * set Item to Personal Catalog
     * @param $sessionId
     * @param $nonce
     * @param $cat_fid
     * @param $fid
     * @param $newprice
     * @return mixed
     * @soap
     */
    public function setItemtoPersonalCatalog($sessionId, $nonce, $cat_fid, $fid, $newprice)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $persCat = Catalog::find()
                ->where("supp_org_id = $org->org and type = 2 and id= $cat_fid")
                ->one();

            if (!isset($persCat)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal catalog not found', $this->ip);
                return 'Catalog error. Personal catalog not found.';
            }

            $priceModel = CatalogGoods::find()->andwhere('base_goods_id=' . $fid)->andwhere('cat_id=' . $persCat->id)->one();

            if ($priceModel) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Item with FID exists in catalog already', $this->ip);
                return 'Product error. Item with FID exists in catalog already.';
            }

            $priceModel = New CatalogGoods;
            $priceModel->price = $newprice;
            $priceModel->cat_id = $cat_fid;
            $priceModel->base_goods_id = $fid;
            $priceModel->updated_at = time();

            if (!$priceModel->save()) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Internal error. Model Item not saved', $this->ip);
                return $priceModel->getErrors();
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK: ' . $priceModel->id . ' is added', $this->ip);
                return 'OK.ADDEDFID:' . $fid;
            }
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * update Personal Price
     * @param $sessionId
     * @param $nonce
     * @param $cat_fid
     * @param $fid
     * @param $newprice
     * @return mixed
     * @soap
     */
    public function updatePersonalPrice($sessionId, $nonce, $cat_fid, $fid, $newprice)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $persCat = Catalog::find()
                ->where("supp_org_id = $org->org and type = 2 and id= $cat_fid")
                ->one();

            if (!isset($persCat)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal catalog not found', $this->ip);
                return 'Catalog error. Personal catalog not found.';
            }

            $priceModel = CatalogGoods::find()->andwhere('base_goods_id=' . $fid)->andwhere('cat_id=' . $persCat->id)->one();

            if (!$priceModel) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Item with FID is not found', $this->ip);
                return 'Product error. Item with FID is not found.';
            }

            $priceModel->price = $newprice;

            if (!$priceModel->save()) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Internal error. Model Item not saved', $this->ip);
                return $priceModel->getErrors();
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK: ' . $priceModel->id . ' is updated', $this->ip);
                return 'OK.UPDATEDFID:' . $fid;
            }
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Update Base Price
     * @param $sessionId
     * @param $nonce
     * @param $fid
     * @param $newprice
     * @return mixed
     * @soap
     */
    public function updateBasePrice($sessionId, $nonce, $fid, $newprice)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $baseCat = Catalog::find()
                ->where("supp_org_id =  $org->org and type = 1")
                ->scalar();

            if (!isset($baseCat)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Base catalog not found', $this->ip);
                return 'Catalog error. Base catalog not found.';
            }

            $priceModel = CatalogBaseGoods::find()->andwhere('id=' . $fid)->andwhere('cat_id=' . $baseCat->id)->one();

            if (!$priceModel) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Item with FID is not found', $this->ip);
                return 'Product error. Item with FID is not found.';
            }

            $priceModel->price = $newprice;


            if (!$priceModel->save()) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Internal error. Model Item not saved', $this->ip);
                return $priceModel->getErrors();
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK: ' . $priceModel->id . ' is updated', $this->ip);
                return 'OK.UPDATEDFID:' . $fid;
            }
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * get Personal Catalog IDs
     * @param $sessionId
     * @param $nonce
     * @param $cat_fid
     * @return mixed
     * @soap
     */
    public function getPersonalCatalogIDs($sessionId, $nonce, $cat_fid)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $persCat = Catalog::find()
                ->where("supp_org_id = $org->org and type = 2 and id= $cat_fid")
                ->one();


            if (!isset($persCat)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal catalog not found', $this->ip);
                return 'Catalog error. Personal catalog not found.';
            }

            $cats = CatalogGoods::find()
                ->select('base_goods_id as fid')
                ->where("cat_id= $cat_fid")
                ->scalar();

            if (!$cats) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal catalog is empty', $this->ip);
                return 'Catalog error. Personal catalog is empty.';
            }

            $jcats = json_encode($cats);
            return $jcats;
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * unset Personal Catalog from Agent
     * @param $sessionId
     * @param $nonce
     * @param $cat_fid
     * @param $agent_fid
     * @return mixed
     * @soap
     */
    public function unsetPersonalCatalogfromAgent($sessionId, $nonce, $cat_fid, $agent_fid)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $persCat = Catalog::find()
                ->where("supp_org_id = $org->org and type = 2 and id= $cat_fid")
                ->one();


            if (!isset($persCat)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal catalog not found', $this->ip);
                return 'Catalog error. Personal catalog with this FID not found.';
            }

            $agent = RelationSuppRest::find()
                ->where("supp_org_id = $org->org and rest_org_id = $agent_fid")
                ->one();

            if (!isset($agent)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Agent not found', $this->ip);
                return 'Catalog error. Agent for Personal catalog with this FID not found.';
            }

            $catModel = RelationSuppRest::find()
                ->andwhere('rest_org_id=' . $agent_fid)
                ->andwhere('supp_org_id=' . $org->org)
                ->andwhere('cat_id=' . $cat_fid)
                ->andwhere('deleted=0')
                ->one();

            if (!$catModel) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Catalog is not set to agent already', $this->ip);
                return 'Catalog error. Personal catalog is not set to agent.';
            }

            $catModel->deleted = 1;
            $catModel->updated_at = time();

            if (!$catModel->save()) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Internal error. Model cat-agent not saved', $this->ip);
                return $catModel->getErrors();
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK: ' . $catModel->id . ' is updated', $this->ip);
                return 'OK.UPDATEDFID:' . $cat_fid;
            }
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * set Personal Catalog to Agent
     * @param $sessionId
     * @param $nonce
     * @param $cat_fid
     * @param $agent_fid
     * @return mixed
     * @soap
     */
    public function setPersonalCatalogtoAgent($sessionId, $nonce, $cat_fid, $agent_fid)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $persCat = Catalog::find()
                ->where("supp_org_id = $org->org and type = 2 and id= $cat_fid")
                ->one();


            if (!isset($persCat)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal catalog not found', $this->ip);
                return 'Catalog error. Personal catalog with this FID not found.';
            }

            $agent = RelationSuppRest::find()
                ->select('rest_org_id')
                ->where("deleted = 0 and supp_org_id  = $org->org and rest_org_id = $agent_fid")
                ->one();

            if (!isset($agent)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Agent not found', $this->ip);
                return 'Catalog error. Agent for Personal catalog with this FID not found.';
            }

            $catModel = RelationSuppRest::find()
                ->andwhere('rest_org_id=' . $agent_fid)
                ->andwhere('supp_org_id=' . $org->org)
                ->andwhere('cat_id=' . $cat_fid)
                ->andwhere('deleted=0')
                ->all();

            if ($catModel) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Catalog is already set to agent', $this->ip);
                return 'Catalog error. Personal catalog already set to agent.';
            }

            $catModel = new RelationSuppRest;

            $catModel->rest_org_id = $agent_fid;
            $catModel->supp_org_id = $org->org;
            $catModel->cat_id = $cat_fid;

            if (!$catModel->save()) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Internal error. Model cat-agent not saved', $this->ip);
                return $catModel->getErrors();
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK: ' . $catModel->id . ' is updated', $this->ip);
                return 'OK.UPDATEDFID:' . $cat_fid;
            }
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Rename Personal Catalog
     * @param $sessionId
     * @param $nonce
     * @param $cat_fid
     * @param $newname
     * @return mixed
     * @soap
     */
    public function renamePersonalCatalog($sessionId, $nonce, $cat_fid, $newname)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $persCat = Catalog::find()
                ->where("supp_org_id = $org->org and type = 2 and id= $cat_fid")
                ->one();


            if (!isset($persCat)) {

                $res = $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal catalog not found', $this->ip);
                return 'Catalog error. Personal catalog with this FID not found.';
                exit;
            }

            $catModel = Catalog::find()->andwhere('id=' . $cat_fid)->one();

            if (!$catModel) {

                $res = $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal Catalog not found', $this->ip);
                return 'Product error. Personal catalog not found.';
                exit;
            }

            $catModel->name = $newname;

            if (!$catModel->save()) {
                $res = $this->save_action(__FUNCTION__, $sessionId, 0, 'Internal error. Model catalog not saved', $this->ip);
                return $catModel->getErrors();
                exit;
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK: ' . $catModel->id . ' is updated', $this->ip);
                return 'OK.UPDATEDFID:' . $catModel->id;
            }

        } else {

            $res = $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';

            exit;
        }
    }

    /**
     * Delete Personal Catalog
     * @param $sessionId
     * @param $nonce
     * @param $cat_fid
     * @return mixed
     */
    public function deletePersonalCatalog($sessionId, $nonce, $cat_fid)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $persCat = Catalog::find()
                ->where("supp_org_id = $org->org and type = 2 and id= $cat_fid")
                ->one();


            if (!isset($persCat)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal catalog not found', $this->ip);
                return 'Catalog error. Personal catalog with this FID not found.';
            }

            $catModel = Catalog::find()->andwhere('id=' . $cat_fid)->one();

            if (!$catModel) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal Catalog not found', $this->ip);
                return 'Product error. Personal catalog not found.';
            }

            $catModel->status = 0;

            if (!$catModel->save()) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Internal error. Model catalog not saved', $this->ip);
                return $catModel->getErrors();
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK: ' . $catModel->id . ' is deleted', $this->ip);
                return 'OK.DELETEDFID:' . $catModel->id;
            }
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Add Personal Catalog
     * @param $sessionId
     * @param $nonce
     * @param $newname
     * @return mixed
     * @soap
     */
    public function addPersonalCatalog($sessionId, $nonce, $newname)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $persCats = Catalog::find()
                ->where("supp_org_id = $org->org  and type = 2 and name = '$newname'")
                ->one();


            if (isset($persCats)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal catalog: name already exists', $this->ip);
                return 'Catalog error. Personal catalog with name already exists.';
            }

            $catModel = new Catalog;

            $catModel->type = 2;
            $catModel->supp_org_id = $org->org;
            $catModel->name = $newname;
            $catModel->status = 1;

            if (!$catModel->save()) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Internal error. Model catalog not saved', $this->ip);
                return $catModel->getErrors();
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK: ' . $catModel->id . ' is added', $this->ip);
                return 'OK.ADDEDFID:' . $catModel->id;
            }

        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Get Personal Catalogs
     * @param string $sessionId
     * @param string $nonce
     * @return mixed
     * @soap
     */
    public function getPersonalCatalogs($sessionId, $nonce)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $persCats = Catalog::find()
                ->where("supp_org_id = $org->org  and type = 2")
                ->one();

            if (!isset($persCats)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Personal catalogs  not found', $this->ip);
                return 'Product error. Personal catalog not found.';
            }
            return $persCats->id;
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Get Base Catalog Item
     * @param $sessionId
     * @param $nonce
     * @param $fid
     * @return mixed
     * @soap
     */
    public function getBaseCatalogItem($sessionId, $nonce, $fid)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $baseCat = Catalog::find()
                ->where("supp_org_id = $org->org and type = 1")
                ->one();

            if (!isset($baseCat)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Base catalog not found', $this->ip);
                return 'Product error. Base catalog not found.';
            }

            $item = (new \yii\db\Query())
                ->select('id as fid, cat_id as catalog_id, article, product, status, created_at, updated_at, price, units, category_id, ed, note')
                ->from(DBNameHelper::getMainName().'.'.CatalogGoods::tableName() )
                ->where("deleted = 0 and supp_org_id = $org->org and cat_id= $baseCat->id and id = $fid")
                ->all();

            if (!$item) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Item not found in Base catalog', $this->ip);
                return 'Product error. Item not found in Base catalog.';
            }
            return $item;
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Get Base Catalog IDs
     * @param $sessionId
     * @param $nonce
     * @return string
     */
    public function getBaseCatalogIDs($sessionId, $nonce)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $baseCat = Catalog::tableName()
                ->where("supp_org_id = $org->org  and type = 1")
                ->one();

            if (!isset($baseCat)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Base catalog not found', $this->ip);
                return 'Product error. Base catalog not found.';
            }

            $cats = CatalogBaseGoods::find()
                ->select('id')
                ->where("supp_org_id = $org->org and cat_id= $baseCat->id and deleted =0")
                ->scalar();

            if (!isset($cats)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Base catalog is empty', $this->ip);
                return 'Product error. Base catalog is empty.';
            }
            $jcats = json_encode($cats);
            return $jcats;
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * delete from Base Catalog
     * @param $sessionId
     * @param $nonce
     * @param $fid
     * @return mixed
     * @soap
     */
    public function deletefromBaseCatalog($sessionId, $nonce, $fid)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $baseCat = Catalog::tableName()
                ->where("supp_org_id = $org->org  and type = 1")
                ->one();

            if (!isset($baseCat)) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Base catalog not found', $this->ip);
                return 'Product error. Base catalog not found.';
            }

            $item = CatalogBaseGoods::find()
                ->select('id')
                ->where("deleted = 0 and cat_id = $baseCat->id  and supp_org_id = $org->id and id= $fid")
                ->scalar();

            if (!$item) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Item not found in base catalog', $this->ip);
                return 'Product error. Item in Base catalog not found.';
            }

            $goodsModel = CatalogBaseGoods::find()->andWhere('id =' . $item)->one();

            $goodsModel->deleted = 1;

            if (!$goodsModel->save()) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Internal error. Model not saved', $this->ip);
                return $goodsModel->getErrors();
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK: ' . $item . ' is deleted', $this->ip);
                return 'OK.DELETEDFID:' . $goodsModel->id;
            }
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Hello
     * @param string $name
     * @return string
     * @soap
     */
    public function getHello($name)
    {
        $this->save_action(__FUNCTION__, 0, 1, 'OK', $this->ip);
        return 'Hello ' . $name . ' IP - ' . $this->ip . '! Server Date:' . gmdate("Y-m-d H:i:s");
    }

    /**
     * Get Categories
     * @param string $sessionId
     * @param string $nonce
     * @param string $lang
     * @return mixed
     * @soap
     */
    public function getCategory($sessionId, $nonce, $lang)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {
            if ($lang == 'ENG') {
                $catview = 'api_category_eng_v';
            } else {
                $catview = 'api_category_rus_v';
            }

            $cats = (new \yii\db\Query())
                ->select('fid, denom, ifnull(up,0) as up')
                ->from(DBNameHelper::getMainName(). '.'.$catview )
                ->all();

            $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $this->ip);
            return $cats;
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Get Agents
     * @param string $sessionId
     * @param string $nonce
     * @return mixed
     * @soap
     */
    public function getAgents($sessionId, $nonce)
    {
        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $cats = (new \yii\db\Query())
                ->select('id as fid, type_id, name, city, address, zip_code,
          phone, email, website, created_at, updated_at, legal_entity, contact_name from organization')
                ->from(DBNameHelper::getMainName(). '.'.Organization::tableName())
                ->where("id in ( select rest_org_id from ".RelationSuppRest::tableName()." where supp_org_id = $org )")
                ->all();

            $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $this->ip);
            return $cats ? $cats : 'No agents found!';
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Add to Base Catalog
     * @param $sessionId
     * @param $nonce
     * @param $units_fid
     * @param $category_fid
     * @param $article
     * @param $product
     * @param $price
     * @param $cid
     * @param $pack
     * @return mixed
     * @soap
     */
    public function addtoBaseCatalog($sessionId, $nonce, $units_fid, $category_fid, $article, $product, $price, $cid, $pack)
    {

        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];

        if ($sess = $this->check_session($sessionId, $nonce)) {

            $org = ApiAccess::find()
                ->where("id = (select acc from api_session where token ='$sessionId')")
                ->one();

            $baseCat = Catalog::find()
                ->select('id')
                ->where("supp_org_id = $org->org and type = 1")
                ->scalar();

            if (!$baseCat) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Base catalog is not found', $this->ip);
                return 'Base catalog error. Catalog is not found.';
            }

            $clearProduct = "'" . str_replace('"', '`', $product) . "'";

            $countProd = CatalogBaseGoods::find()
                ->where("product = '$clearProduct' and cat_id= $baseCat and deleted =0")
                ->count();

            if ($countProd > 0) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Product name exists', $this->ip);
                return 'Product error. Name already exists.';
            }

            $countArt = CatalogGoods::find()
                ->where("article = '$article' and cat_id= $baseCat and deleted =0")
                ->count();

            if ($countArt > 0) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Product art exists', $this->ip);
                return 'Product error. Article already exists.';
            }

            $goodsModel = new CatalogBaseGoods;
            $goodsModel->product = str_replace('"', '`', $product);
            $goodsModel->cat_id = $baseCat;
            $goodsModel->article = $article;
            $goodsModel->units = $pack;
            $goodsModel->price = $price;
            $goodsModel->note = $cid;
            $goodsModel->deleted = 0;
            $goodsModel->market_place = 0;
            $goodsModel->status = 1;
            $goodsModel->ed = Yii::t('app', MpEd::find()->andwhere('id='.$units_fid)->one()->name);
            $goodsModel->supp_org_id = $org->org;
            $goodsModel->es_status = 1;
            $goodsModel->category_id = $category_fid;

            if (!$goodsModel->save()) {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'Internal error. Model not saved', $this->ip);
                return $goodsModel->getErrors();
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $this->ip);
                return 'OK.NEWFID:' . $goodsModel->id;
            }
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Get Units
     * @param string $sessionId
     * @param string $nonce
     * @param string $lang
     * @return mixed
     * @soap
     */
    public function getUnits($sessionId, $nonce, $lang)
    {
        if ($this->check_session($sessionId, $nonce)) {
            if ($lang == 'ENG') {
                $catview = 'api_units_eng_v';
            } else {
                $catview = 'api_units_rus_v';
            }

            $cats = (new \yii\db\Query())
                ->select('fid, denom')
                ->from(DBNameHelper::getMainName(). '.'. $catview )
                ->all();

            $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $this->ip);
            return $cats;
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Close session
     * @param string $sessionId
     * @param string $nonce
     * @return mixed result
     * @soap
     */
    public function CloseSession($sessionId, $nonce)
    {
        if ($this->check_session($sessionId, $nonce)) {
            $sess = ApiSession::find()->where('token = :token and now() between fd and td',
                [':token' => $sessionId])->one();

            $sess->td = gmdate('Y-m-d H:i:s');
            $sess->status = 2;

            if (!$sess->save()) {
                return $sess->errors;
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $sess->ip);
                return 'OK_CLOSED :' . $sess->token;
            }
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Soap authorization open session
     * @return mixed result of auth
     * @soap
     */
    public function OpenSession()
    {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($this->username)) {
            header('WWW-Authenticate: Basic realm="f-keeper.ru"');
            header('HTTP/1.0 401 Unauthorized');
            header('Warning: WSS security in not provided in SOAP header');
            $this->save_action(__FUNCTION__, 0, 0, 'Auth error HTTP/1.0 401 Unauthorized', $this->ip);
        } else {
            if (!$acc = ApiAccess::find()->where('login = :username and now() between fd and td', [':username' => $this->username])->one()) {
                $this->save_action(__FUNCTION__, 0, 0, 'Wrong login', $this->ip);
                return 'Auth error. Login is not found.';
            };

            if (Yii::$app->getSecurity()->validatePassword($this->password, $acc->password)) {

                $sessionId = Yii::$app->getSecurity()->generateRandomString();
                $oldsess = ApiSession::find()->orderBy('fid DESC')->one();
                $sess = new ApiSession();

                if ($oldsess) {
                    $sess->fid = $oldsess->fid + 1;
                } else {
                    $sess->fid = 1;
                }

                $sess->token = $sessionId;
                $sess->acc = $acc->fid;
                $sess->nonce = $this->nonce;
                $sess->fd = gmdate('Y-m-d H:i:s');
                $sess->td = gmdate('Y-m-d H:i:s', strtotime('+1 day'));
                $sess->ver = 1;
                $sess->status = 1;
                $sess->ip = $this->ip;
                $sess->extimefrom = gmdate('Y-m-d H:i:s');

                if (!$sess->save()) {
                    return $sess->errors;
                } else {
                    $this->save_action(__FUNCTION__, $sess->token, 1, 'OK', $this->ip);
                    return 'OK_SOPENED:' . $sess->token;
                }
            } else {
                $this->save_action(__FUNCTION__, 0, 0, 'Wrong password', $this->ip);
                return 'Auth error. Password is not correct.';
            }
        }
    }

    /**
     * @param $header
     * @return mixed
     */
    public function security($header)
    {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($header->UsernameToken->Username)) // Проверяем послали ли нам данные авторизации (BASIC)
        {
            header('WWW-Authenticate: Basic realm="fkeeper.ru"'); // если нет, даем отлуп - пришлите авторизацию
            header('HTTP/1.0 401 Unauthorized');
            $this->save_action(__FUNCTION__, 0, 0, 'Auth error HTTP/1.0 401 Unauthorized', $this->ip);
        } else {
            $this->username = $header->UsernameToken->Username;
            $this->password = $header->UsernameToken->Password;
            $this->nonce = $header->UsernameToken->Nonce;
            $this->extimefrom = $header->UsernameToken->Created;
            return $header;
        }
    }

    /**
     * @param $session
     * @param $nonce
     * @return bool
     */
    public function check_session($session, $nonce)
    {
        if (ApiSession::find()->where('token = :token and nonce = :nonce and now() between fd and td',
            [':token' => $session, ':nonce' => $nonce])->exists()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $func
     * @param $sess
     * @param $result
     * @param $comment
     * @param $ip
     * @return array|bool
     */
    public function save_action($func, $sess, $result, $comment, $ip)
    {
        $act = new ApiActions;
        $currSess = ApiSession::find()->where('token = :token', [':token' => $sess])->one();

        if ($currSess) {
            $act->session = $currSess->fid;
            $act->ip = $currSess->ip;
        } else {
            $act->session = 0;
            $act->ip = $ip;
        }

        $act->action = $func;
        $act->created = gmdate('Y-m-d H:i:s');
        $act->result = $result;
        $act->comment = $comment;

        if (!$act->save()) {
            return $act->errors;
        } else {
            return true;
        }
    }
}