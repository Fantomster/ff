<?php

namespace frontend\modules\clientintegr\controllers;

use Yii;
use yii\web\Controller;
use common\models\Organization;
use frontend\modules\clientintegr\modules\rkws\components\ServiceHelper;
// use yii\mongosoft\soapserver\Action;
use common\components\AccessRule;
use yii\filters\AccessControl;
use common\models\Role;


/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class DefaultController extends \frontend\controllers\DefaultController {
    
    public $enableCsrfValidation = false;
    
    protected $authenticated = false;

    protected $mercCategoryLog = 'merc_log';
    
    private $sessionId = '';
    private $username;
    private $password;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        /*'actions' => [
                            '*',
                        ],*/
                        'allow' => false,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_BUYER,
                            Role::ROLE_RESTAURANT_JUNIOR_BUYER,
                            Role::ROLE_RESTAURANT_ORDER_INITIATOR,
                        ],
                    ],
                    [
                        'allow' => TRUE,
                        'roles' => ['@'],
                    ],

                ],
            ],
        ];
    }
    
        
    public function actionIndex() {
        
    return $this->render('index' // ,[
              //      'searchModel' => $searchModel,
              //      'dataProvider' => $dataProvider,
              // ]
                );
      //  $langs = Yii::$app->db_api->createCommand('SELECT * FROM api_lang')
      //      ->queryAll();
        
      //  var_dump($langs);
        
    
        
    }
    
    public function actionHello() {
     
        echo "hello";
        
    }
    
    public function actionTabson() {
     
        echo "tabson";
        
    }
    
    protected function setLayout($orgType) {
        switch ($orgType) {
                case Organization::TYPE_RESTAURANT:
                    $this->layout = '@frontend/views/layouts/main-client.php';
                    break;
                case Organization::TYPE_SUPPLIER:
                    $this->layout = '@frontend/views/layouts/main-vendor.php';
                    break;
            }
    }
    
    

/*
    public function actions()
{
    return [
        'hello' => [
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
*/
    
/**
* @param string $login
* @param string $pass
* @return string 
* @soap
*/
 
    
    public function getHello($login,$pass) 
    {
        return 'Hello ' . $login.'/'.$pass.'/ Date:'.date("Y-m-d H:i:s") ;
    }


   
/**
   * Soap authorization
   * @return mixed result of auth
   * @soap
   */
   
  public function OpenSession() {
      
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($this->username)) 
    {
    header('WWW-Authenticate: Basic realm="f-keeper.ru"');
    header('HTTP/1.0 401 Unauthorized');
    header('Warning: WSS security in not provided in SOAP header');
    exit;
   
    } else { 
        
    // $identity = new UserIdentity($this->username, $this->password);    
   
        if (($this->username != 'cyborg') || ($this->password != 'mypass')) 
        {
            return 'Auth error. Login or password is not correct.';
        } else {
    
            $sessionId = Yii::$app->getSecurity()->generateRandomString();
            // $sessionId = md5(uniqid(rand(),1));
          
            return 'OK_SOPENED:'.$sessionId;
        }
       
    }  
    
  }
  
    public function security($header) {
    
       
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($header->UsernameToken->Username)) // Проверяем послали ли нам данные авторизации (BASIC)
        {
            header('WWW-Authenticate: Basic realm="fkeeper.ru"'); // если нет, даем отлуп - пришлите авторизацию
            header('HTTP/1.0 401 Unauthorized');
            exit;
   
        } else {
            
        $this->username = $header->UsernameToken->Username;
        $this->password = $header->UsernameToken->Password;
         
    //     $this->username =  Yii::$app->request->getAuthUser();
    //     $this->password =  Yii::$app->request->getAuthPassword();
         
         return $header;
         
                     
        }

  }

    public function actionGetws() {

        $res = new ServiceHelper();
        $res->getObjects();

        $vrem = date("Y-m-d H:i:s");
        $query0 = "update `rk_actions` set `created` = '".$vrem."' where `id` = '1'";
        $a = Yii::$app->db_api->createCommand($query0)->execute();
        $query0 = "select `td` from `rk_service` where `code` = '199990046'";
        $a = Yii::$app->db_api->createCommand($query0)->queryScalar();
        if($a=='0001-01-05 00:00:00') {
            $query0 = "update `rk_service` set `td` = '2100-01-01 00:00:00' where `code` = '199990046'";
            $a = Yii::$app->db_api->createCommand($query0)->execute();
        }

        $this->redirect('index');

    }

    protected function getErrorText($e)
    {
        if ($e->getCode() == 600) {
            return "При обращении к api Меркурий возникла ошибка. Ошибка зарегистрирована в журнале за номером №" . $e->getMessage() . ". Если ошибка повторяется обратитесь в техническую службу.";
        } else {
            Yii::error($e->getMessage()." ".$e->getTraceAsString());
            return "При обращении к api Меркурий возникла ошибка. Если ошибка повторяется обратитесь в техническую службу.";
        }
    }

}
