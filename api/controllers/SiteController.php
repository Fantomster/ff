<?php

namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class SiteController extends Controller {
    
    public $enableCsrfValidation = false;
    
    protected $authenticated = false;
    
    private $sessionId = '';
    private $username;
    private $password;
    
        
    public function actionIndex() {
     
        echo "index";
        
    }
    
    public function actionHello() {
     
        echo "hello";
        
    }


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
   
     //   if ()
    
         $sessionId = Yii::$app->getSecurity()->generateRandomString();
        
        // $sessionId = md5(uniqid(rand(),1));
          
        return 'OK_SOPENED:'.$sessionId.'::'.Yii::$app->user->isGuest;
    
   // return $_SERVER['PHP_AUTH_USER'].'/'.$_SERVER['PHP_AUTH_PW']; 
    
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
  
}
