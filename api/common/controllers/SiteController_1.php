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
        
    public function actionIndex() {
     
        echo "index";
        
    }
    
    public function actionHello() {
     
        echo "hello";
        
    }

 /*   public function actions() 
    {
        return [
            'hello' => 'mongosoft\soapserver\Action',
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }   
 */
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
* @param string $test
* @return mixed 
* @soap
*/
    
    public function getTest($test) 
    {
        return '<xml><start>Hello</start><start2>'.$test.'</start2></xml>';
    }
    
    /**
   * Авторизация клиента soap
   * @param object $username
   * @param object $password
   * @return boolean result of auth
   * @soap
   */
  public function auth($username, $password) {

   // $identity = new UserIdentity($username, $password);

   // if ($identity->authenticate())
   //   $this->authenticated = true;

    return true;
  }
}
