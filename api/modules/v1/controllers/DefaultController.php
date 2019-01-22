<?php

namespace api\modules\v1\controllers;

use common\helpers\DBNameHelper;
use common\models\Organization;
use common\models\RelationSuppRest;
use Yii;
use yii\web\Controller;
use yii\mongosoft\soapserver\Action;

use api\common\models\ApiAccess;
use api\common\models\ApiSession;
use api\common\models\ApiActions;
use common\models\CatalogBaseGoods;
use common\models\MpEd;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */
class DefaultController extends Controller
{

    public $enableCsrfValidation = false;

    protected $authenticated = false;

    private $sessionId = '';
    private $username;
    private $password;
    private $nonce;
    private $extimefrom;
    private $ip;

    public function actionIndex()
    {

        return $this->render('index' // ,[
        //      'searchModel' => $searchModel,
        //      'dataProvider' => $dataProvider,
        // ]
        );

    }

    public function actions()
    {
        return [
            'wsdl'  => [
                'class'          => 'mongosoft\soapserver\Action',
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
     * @param string $name
     * @return string
     * @soap
     */

    public function getHello($name)
    {
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];

        $this->save_action(__FUNCTION__, 0, 1, 'OK', $this->ip);

        return 'Hello ' . $name . '! Server Date:' . gmdate("Y-m-d H:i:s");
    }

    /**
     * Get Categories
     *
     * @param string $sessionId
     * @param string $nonce
     * @param string $lang
     * @return mixed
     * @soap
     */

    public function getCategory($sessionId, $nonce, $lang)
    {

        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];

        if ($sess = $this->check_session($sessionId, $nonce)) {

            // return $sess;

            if ($lang == 'ENG') {

                $catview = 'api_category_eng_v';

            } else {

                $catview = 'api_category_rus_v';
            }

            $cats = (new \yii\db\Query())
                ->select('fid, denom, ifnull(up,0) as up')
                ->from(DBNameHelper::getApiName() . '.'.$catview)
                ->all();

            $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $this->ip);
            return $cats;

            exit;

        } else {

            $res = $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';

            exit;
        }

    }

    /**
     * Get Categories
     *
     * @param string $sessionId
     * @param string $nonce
     * @param string $lang
     * @return mixed
     * @soap
     */

    public function getAgents($sessionId, $nonce, $lang)
    {

        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];

        if ($sess = $this->check_session($sessionId, $nonce)) {

            $acc = ApiSession::find()
                ->where('token = :session_id',[':session_id' => $sessionId])
                ->one();

            $org = (new \yii\db\Query())
                ->select('org')
                ->from(DBNameHelper::getApiName() . '.'.ApiAccess::tableName())
                ->where('id = :acc',[':acc' => $acc->acc])
                ->scalar();

            $orgs = RelationSuppRest::find()
                ->select('rest_org_id')
                ->where("supp_org_id = $org");

            $cats = (new \yii\db\Query())
                ->select('id as fid, type_id, name, city, address, zip_code,
          phone, email, website, created_at, updated_at, legal_entity, contact_name')
                ->from(DBNameHelper::getMainName() . '.'.Organization::tableName())
                ->where('in', 'id', $orgs)
                ->all();

            $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $this->ip);

            return $cats ? $cats : 'No agents found!';

            exit;

        } else {

            $res = $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';

            exit;
        }

    }

    /**
     * add goods to base catalog
     *
     * @param string  $sessionId
     * @param string  $nonce
     * @param string  $lang
     * @param integer $units_fid
     * @param integer $category_fid
     * @param string  $article
     * @param string  $product
     * @param double  $price
     * @param double  $pack
     * @param string  $cid
     * @return mixed
     * @soap
     */

    public function addtoBaseCatalog($sessionId, $nonce, $lang, $units_fid, $category_fid, $article, $product, $price, $cid, $pack)
    {

        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];

        if ($sess = $this->check_session($sessionId, $nonce)) {

            $acc = ApiSession::find()
                ->where('token = :session_id',[':session_id' => $sessionId])
                ->one();

            $org = (new \yii\db\Query())
                ->select('org')
                ->from(DBNameHelper::getApiName() . '.'.ApiAccess::tableName())
                ->where('id = :acc',[':acc' => $acc->acc])
                ->scalar();

            $baseCat = (new \yii\db\Query())
                ->select('id')
                ->from(DBNameHelper::getMainName() . '.'.Catalog::tableName())
                ->where('supp_org_id = :org and type = 1', [':org' => $org])
                ->scalar();

            $clearProduct = "'" . str_replace('"', '`', $product) . "'";


            $countProd = (new \yii\db\Query())
                ->select('*')
                ->from(DBNameHelper::getMainName() . '.'.CatalogBaseGoods::tableName())
                ->where("product = $clearProduct and cat_id = :baseCat and deleted = 0", [':baseCat' => $baseCat])
                ->count();

            if ($countProd > 0) {

                $res = $this->save_action(__FUNCTION__, $sessionId, 0, 'Product name exists', $this->ip);
                return 'Product error. Name already exists.';
                exit;
            }

            $countArt = (new \yii\db\Query())
                ->select('*')
                ->from(DBNameHelper::getMainName() . '.'.CatalogBaseGoods::tableName())
                ->where("article = :article and cat_id = :baseCat and deleted = 0", [':article' => $article,':baseCat' => $baseCat])
                ->count();

            if ($countArt > 0) {

                $res = $this->save_action(__FUNCTION__, $sessionId, 0, 'Product art exists', $this->ip);
                return 'Product error. Article already exists.';
                exit;
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
            $goodsModel->ed = Yii::t('app', MpEd::find()->andwhere('id=' . $units_fid)->one()->name);
            $goodsModel->supp_org_id = $org;
            $goodsModel->es_status = 1;
            $goodsModel->category_id = $category_fid;

            if (!$goodsModel->save()) {
                $res = $this->save_action(__FUNCTION__, $sessionId, 0, 'Internal error. Model not saved', $this->ip);
                return $goodsModel->getErrors();
                exit;
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $this->ip);
                return 'OK.NEWFID:' . $goodsModel->id;
            }

            //  $cats = Yii::$app->db->createCommand('select id as fid, type_id, name, city, address, zip_code,
            //      phone, email, website, created_at, updated_at, legal_entity, contact_name from organization
            //      where id in ( select rest_org_id from relation_supp_rest where supp_org_id ='.$org.')')
            //  ->queryAll();

            //  return $cats ? $cats : 'No agents found!';

            exit;

        } else {

            $res = $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';

            exit;
        }

    }

    /**
     * Get Units
     *
     * @param string $sessionId
     * @param string $nonce
     * @param string $lang
     * @return mixed
     * @soap
     */

    public function getUnits($sessionId, $nonce, $lang)
    {

        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];

        if ($this->check_session($sessionId, $nonce)) {

            if ($lang == 'ENG') {

                $catview = 'api_units_eng_v';

            } else {

                $catview = 'api_units_rus_v';
            }

            $cats = (new \yii\db\Query())
                ->select('fid, denom')
                ->from(DBNameHelper::getApiName() . '.'.$catview)
                ->all();

            $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $this->ip);
            return $cats;
            exit;

        } else {

            $res = $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            //return $res;
            return 'Session error. Active session is not found.';
            exit;
        }

    }

    /**
     * Soap authorization open session
     *
     * @return mixed result of auth
     * @soap
     */

    public function OpenSession()
    {

        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($this->username)) {
            header('WWW-Authenticate: Basic realm="f-keeper.ru"');
            header('HTTP/1.0 401 Unauthorized');
            header('Warning: WSS security in not provided in SOAP header');

            if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];
            $this->save_action(__FUNCTION__, 0, 0, 'Auth error HTTP/1.0 401 Unauthorized', $this->ip);
            exit;

        } else {


            if (!$acc = ApiAccess::find()->where('login = :username and now() between fd and td', [':username' => $this->username])->one()) {

                $this->save_action(__FUNCTION__, 0, 0, 'Wrong login', $this->ip);
                return 'Auth error. Login is not found.';
                exit;
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
                $sess->extimefrom = $this->extimefrom;

                if (!$sess->save()) {
                    return $sess->errors;
                    exit;
                } else

                    $res = $this->save_action(__FUNCTION__, $sess->token, 1, 'OK', $this->ip);

                return 'OK_SOPENED:' . $sess->token;

            } else {

                $res = $this->save_action(__FUNCTION__, 0, 0, 'Wrong password', $this->ip);

                return 'Auth error. Password is not correct.';

                exit;
            }

            // $identity = new UserIdentity($this->username, $this->password);

            /*    if (($this->username != 'cyborg') || ($this->password != 'mypass'))
                {
                    return 'Auth error. Login or password is not correct.';
                } else {

                    $sessionId = Yii::$app->getSecurity()->generateRandomString();
                    // $sessionId = md5(uniqid(rand(),1));

                    return 'OK_SOPENED:'.$sessionId;
                }
               */
        }

    }

    /**
     * Close session
     *
     * @param string $sessionId
     * @param string $nonce
     * @return mixed result
     * @soap
     */

    public function CloseSession($sessionId, $nonce)
    {

        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];

        if ($this->check_session($sessionId, $nonce)) {

            $sess = ApiSession::find()->where('token = :token and now() between fd and td',
                [':token' => $sessionId])->one();

            $sess->td = gmdate('Y-m-d H:i:s');
            $sess->status = 2;

            if (!$sess->save()) {
                return $sess->errors;
                exit;
            } else {

                $res = $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $sess->ip);
                return 'OK_CLOSED :' . $sess->token;
            }

        } else {


            $res = $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
            exit;
        }
    }

    public function security($header)
    {


        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($header->UsernameToken->Username)) // Проверяем послали ли нам данные авторизации (BASIC)
        {
            header('WWW-Authenticate: Basic realm="fkeeper.ru"'); // если нет, даем отлуп - пришлите авторизацию
            header('HTTP/1.0 401 Unauthorized');

            if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];
            $this->save_action(__FUNCTION__, 0, 0, 'Auth error HTTP/1.0 401 Unauthorized', $this->ip);
            exit;

        } else {

            $this->username = $header->UsernameToken->Username;
            $this->password = $header->UsernameToken->Password;
            $this->nonce = $header->UsernameToken->Nonce;
            $this->extimefrom = $header->UsernameToken->Created;

            if (isset($_SERVER['REMOTE_ADDR']))
                $this->ip = $_SERVER['REMOTE_ADDR'];

            //     $this->username =  Yii::$app->request->getAuthUser();
            //     $this->password =  Yii::$app->request->getAuthPassword();

            return $header;

        }

    }

    public function check_session($session, $nonce)
    {

        if ($sess = ApiSession::find()->where('token = :token and nonce = :nonce and now() between fd and td',
            [':token' => $session, 'nonce' => $nonce])->one()) {

            return true;

        } else {

            return false;

        }

    }

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
            exit;
        } else {

            return true;
        }

        return $act->session;

    }

}