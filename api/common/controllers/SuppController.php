<?php

namespace api\common\controllers;

use Yii;
use yii\web\Controller;
use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class SuppController extends Controller {
    
    public $enableCsrfValidation = false;
    
    protected $authenticated = false;
    
    private $sessionId = '';
    private $username;
    private $password;
    
        
    public function actionIndex() {
     
        echo "Welcome to F-Keeper API gateway for Suppliers. Please use SOAP client to connect this service.";
        
    }
    
    public function actionHello() {
     
        echo "hello";
        
    }


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
* Get Languages dictionary 
* @return array 
* @soap
*/
    
    public function getlang() 
    {
        // $ar = array('1'=>'Шт', '2' =>'Литр', '3' =>'Упаковка');
      $langs = Yii::$app->db_api->createCommand('SELECT * FROM api_lang')
              ->queryAll();
        
        // return 'Hello ' . $login.'/'.$pass.'/ Date:'.date("Y-m-d H:i:s") ;
        
        return $langs;
    }

/**
* Get Categories 
* @return array 
* @soap
*/
    
    
    
    public function getCategory() 
    {
        // $ar = array('1'=>'Шт', '2' =>'Литр', '3' =>'Упаковка');
      $cats = Yii::$app->db_api->createCommand('SELECT id, name, ifnull(parent,0) as up FROM api_category_rus_v')
              ->queryAll();
        
        // return 'Hello ' . $login.'/'.$pass.'/ Date:'.date("Y-m-d H:i:s") ;
        
        return $cats;
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
  
}
