<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use Yii;
use yii\web\Controller;
use api\common\models\RkDicSearch;
use api\common\models\RkDic;
use common\models\Organization;

// use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class DefaultController extends \frontend\modules\clientintegr\controllers\DefaultController {
    
    public $enableCsrfValidation = false;
    
    protected $authenticated = false;
    
    private $sessionId = '';
    private $username;
    private $password;
    
        
    public function actionIndex() {
        
        $searchModel = new RkDicSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        $organization = Yii::$app->user->identity->organization;
        $lic0 = $organization->getLicenseList();
        //$lic = $this->checkLic();
        $lic = $lic0['rkws'];
        $licucs = $lic0['rkws_ucs'];
        $vi = (($lic) && ($licucs)) ? 'index' : '/default/_nolic';
        $spravoch_zagruzhen = RkDicSearch::getDicsLoad();

        if ($spravoch_zagruzhen) {
            return Yii::$app->response->redirect(['clientintegr/rkws/waybill/index']);
        } else {
            if (Yii::$app->request->isPjax) {
                return $this->renderPartial($vi, [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'lic' => $lic,
                    'licucs' => $licucs,
                ]);
            } else {
                return $this->render($vi, [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'lic' => $lic,
                    'licucs' => $licucs,
                ]);
            }
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
  
    protected function checkLic() {
     
    // $lic = \api\common\models\RkService::find()->andWhere('org = :org',['org' => Yii::$app->user->identity->organization_id])->one();
    $lic = \api\common\models\RkServicedata::find()->andWhere('org = :org',['org' => Yii::$app->user->identity->organization_id])->one();
    $t = strtotime(date('Y-m-d H:i:s',time()));
    /*print "<pre>";
    var_dump($lic);
    print "</pre>";
    die();*/
    
    if ($lic) {
       /*if ($t >= strtotime($lic->fd) && $t<= strtotime($lic->td) && $lic->status_id === 2 ) {*/
       $res = $lic;
       /* print "<pre>";
        var_dump($lic);
        print "</pre>";
        die();*/
    /*} else {
       $res = 0; 
    }*/
    } else 
       $res = 0; 
    
    
    return $res ? $res : null;
        
    }

    protected function checkLicMc() {

        // $lic = \api\common\models\RkService::find()->andWhere('org = :org',['org' => Yii::$app->user->identity->organization_id])->one();
        $lic = \api\common\models\RkService::find()->andWhere('org = :org',['org' => Yii::$app->user->identity->organization_id])->one();
        $t = strtotime(date('Y-m-d H:i:s',time()));
        /*print "<pre>";
        var_dump($lic);
        print "</pre>";
        die();*/

        if ($lic) {
            /*if ($t >= strtotime($lic->fd) && $t<= strtotime($lic->td) && $lic->status_id === 2 ) {*/
            $res = $lic;
            /* print "<pre>";
             var_dump($lic);
             print "</pre>";
             die();*/
            /*} else {
               $res = 0;
            }*/
        } else
            $res = 0;


        return $res ? $res : null;

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

    public function actionMain()
    {
        $searchModel = new RkDicSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $organization = Yii::$app->user->identity->organization;
        $lic0 = $organization->getLicenseList();
        //$lic = $this->checkLic();
        $lic = $lic0['rkws'];
        $licucs = $lic0['rkws_ucs'];
        $vi = (($lic) && ($licucs)) ? 'index' : '/default/_nolic';
            if (Yii::$app->request->isPjax) {
                return $this->renderPartial($vi, [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'lic' => $lic,
                    'licucs' => $licucs,
                ]);
            } else {
                return $this->render($vi, [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'lic' => $lic,
                    'licucs' => $licucs,
                ]);
            }
    }
  
   
}
