<?php

namespace api\modules\v1\modules\supp\controllers;

use Yii;
use yii\web\Controller;
use yii\mongosoft\soapserver\Action;

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

class DefaultController extends Controller {
    
    public $enableCsrfValidation = false;
    
    protected $authenticated = false;
    
    private $sessionId = '';
    private $username;
    private $password;
    private $nonce;
    private $extimefrom;
    private $ip;
    
        
    public function actionIndex() {
     
        return $this->render('index' // ,[
              //      'searchModel' => $searchModel,
              //      'dataProvider' => $dataProvider,
              // ]
                );
        
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

// Start SOAP procedures


 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @param integer $cat_fid
* @return mixed 
* @soap
*/
    
    public function deleteItemfromPersonalCatalog($sessionId, $nonce, $cat_fid, $fid) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $persCat = Yii::$app->db->createCommand('select id from catalog where supp_org_id ='.$org.' and type = 2 and id='.$cat_fid)
      ->queryScalar();   
        
        if (!$persCat) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal catalog not found',$this->ip);       
        return 'Catalog error. Personal catalog not found.';   
        exit;
       }
      
      $priceModel = CatalogGoods::find()->andwhere('base_goods_id='.$fid)->andwhere('cat_id='.$persCat)->one();

      if (!$priceModel) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Item with FID is not found in catalog',$this->ip);       
        return 'Product error. Item with FID not found in personal catalog';   
        exit;
      }
       
     if (!$priceModel->delete()) {
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Internal error. Model Item not deleted',$this->ip);       
        return $priceModel->getErrors();
        exit;    
     } else {
         $this->save_action(__FUNCTION__, $sessionId, 1,'OK: '.$priceModel->id.' is deleted',$this->ip);
         return 'OK.DELETEDFID:'.$fid;        
     } 
       
            
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }


 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @param integer $cat_fid
* @return mixed 
* @soap
*/
    
    public function setItemtoPersonalCatalog($sessionId, $nonce, $cat_fid, $fid, $newprice) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $persCat = Yii::$app->db->createCommand('select id from catalog where supp_org_id ='.$org.' and type = 2 and id='.$cat_fid)
      ->queryScalar();   
        
        if (!$persCat) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal catalog not found',$this->ip);       
        return 'Catalog error. Personal catalog not found.';   
        exit;
       }
      
       
      $priceModel = CatalogGoods::find()->andwhere('base_goods_id='.$fid)->andwhere('cat_id='.$persCat)->one();

      if ($priceModel) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Item with FID exists in catalog already',$this->ip);       
        return 'Product error. Item with FID exists in catalog already.';   
        exit;
      }

      $priceModel = New CatalogGoods;
             
      $priceModel->price = $newprice;
      $priceModel->cat_id = $cat_fid;
      $priceModel->base_goods_id = $fid;
      $priceModel->updated_at = time();
      
             
     if (!$priceModel->save()) {
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Internal error. Model Item not saved',$this->ip);       
        return $priceModel->getErrors();
        exit;    
     } else {
         $this->save_action(__FUNCTION__, $sessionId, 1,'OK: '.$priceModel->id.' is added',$this->ip);
         return 'OK.ADDEDFID:'.$fid;        
     } 
       
            
            
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }


 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @param integer $cat_fid
* @return mixed 
* @soap
*/
    
    public function updatePersonalPrice($sessionId, $nonce, $cat_fid, $fid, $newprice) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $persCat = Yii::$app->db->createCommand('select id from catalog where supp_org_id ='.$org.' and type = 2 and id='.$cat_fid)
      ->queryScalar();   
        
        if (!$persCat) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal catalog not found',$this->ip);       
        return 'Catalog error. Personal catalog not found.';   
        exit;
       }
      
       
      $priceModel = CatalogGoods::find()->andwhere('base_goods_id='.$fid)->andwhere('cat_id='.$persCat)->one();

      if (!$priceModel) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Item with FID is not found',$this->ip);       
        return 'Product error. Item with FID is not found.';   
        exit;
      }

      $priceModel->price = $newprice;
            
             
     if (!$priceModel->save()) {
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Internal error. Model Item not saved',$this->ip);       
        return $priceModel->getErrors();
        exit;    
     } else {
         $this->save_action(__FUNCTION__, $sessionId, 1,'OK: '.$priceModel->id.' is updated',$this->ip);
         return 'OK.UPDATEDFID:'.$fid;        
     } 
       
            
            
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }

 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @param integer $cat_fid
* @return mixed 
* @soap
*/
    
    public function updateBasePrice($sessionId, $nonce, $fid, $newprice) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $baseCat = Yii::$app->db->createCommand('select id from catalog where supp_org_id ='.$org.' and type = 1')
      ->queryScalar();   
        
        if (!$baseCat) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Base catalog not found',$this->ip);       
        return 'Catalog error. Base catalog not found.';   
        exit;
      }
      
      $priceModel = CatalogBaseGoods::find()->andwhere('id='.$fid)->andwhere('cat_id='.$baseCat)->one();

      if (!$priceModel) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Item with FID is not found',$this->ip);       
        return 'Product error. Item with FID is not found.';   
        exit;
      }

      $priceModel->price = $newprice;
            
             
     if (!$priceModel->save()) {
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Internal error. Model Item not saved',$this->ip);       
        return $priceModel->getErrors();
        exit;    
     } else {
         $this->save_action(__FUNCTION__, $sessionId, 1,'OK: '.$priceModel->id.' is updated',$this->ip);
         return 'OK.UPDATEDFID:'.$fid;        
     } 
       
            
            
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }

 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @param integer $cat_fid
* @return mixed 
* @soap
*/
    
    public function getPersonalCatalogIDs($sessionId, $nonce, $cat_fid) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $persCat = Yii::$app->db->createCommand('select id from catalog where supp_org_id ='.$org.' and type = 2 and id='.$cat_fid)
      ->queryScalar();   
        
        if (!$persCat) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal catalog not found',$this->ip);       
        return 'Catalog error. Personal catalog not found.';   
        exit;
      }
      
      $cats = Yii::$app->db->createCommand('select base_goods_id as fid from catalog_goods where cat_id='.$cat_fid)
      ->queryAll();
      
      if (!$cats) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal catalog is empty',$this->ip);       
        return 'Catalog error. Personal catalog is empty.';   
        exit;
      }
           
      $jcats = json_encode($cats);
      return $jcats;
            
            
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }


 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @param string $newname
* @return mixed 
* @soap
*/
    
    public function unsetPersonalCatalogfromAgent($sessionId, $nonce, $cat_fid, $agent_fid) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $persCat = Yii::$app->db->createCommand("select id from catalog where supp_org_id =".$org." and type = 2 and id=".$cat_fid)
      ->queryScalar();   
                
        if (!$persCat) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal catalog not found',$this->ip);       
        return 'Catalog error. Personal catalog with this FID not found.';   
        exit;
      }
      
        $agent = Yii::$app->db->createCommand("select rest_org_id from relation_supp_rest where supp_org_id =".$org." and rest_org_id =".$agent_fid)
      ->queryScalar();  
       
        if (!$agent) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Agent not found',$this->ip);       
        return 'Catalog error. Agent for Personal catalog with this FID not found.';   
        exit;
      }
        
       $catModel = RelationSuppRest::find()
               ->andwhere('rest_org_id='.$agent_fid)
               ->andwhere('supp_org_id='.$org)
               ->andwhere('cat_id='.$cat_fid)
               ->andwhere('deleted=0')
               ->one();
       
       if (!$catModel) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Catalog is not set to agent already',$this->ip);       
        return 'Catalog error. Personal catalog is not set to agent.';   
        exit;
      }
       
       $catModel->deleted = 1;
       $catModel->updated_at = time();
       
     if (!$catModel->save()) {
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Internal error. Model cat-agent not saved',$this->ip);       
        return $catModel->getErrors();
        exit;    
     } else {
         $this->save_action(__FUNCTION__, $sessionId, 1,'OK: '.$catModel->id.' is updated',$this->ip);
         return 'OK.UPDATEDFID:'.$cat_fid;        
     } 
       
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }


 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @param string $newname
* @return mixed 
* @soap
*/
    
    public function setPersonalCatalogtoAgent($sessionId, $nonce, $cat_fid, $agent_fid) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $persCat = Yii::$app->db->createCommand("select id from catalog where supp_org_id =".$org." and type = 2 and id=".$cat_fid)
      ->queryScalar();   
                
        if (!$persCat) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal catalog not found',$this->ip);       
        return 'Catalog error. Personal catalog with this FID not found.';   
        exit;
      }
      
        $agent = Yii::$app->db->createCommand("select rest_org_id from relation_supp_rest where deleted = 0 and supp_org_id =".$org." and rest_org_id =".$agent_fid)
      ->queryScalar();  
       
        if (!$agent) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Agent not found',$this->ip);       
        return 'Catalog error. Agent for Personal catalog with this FID not found.';   
        exit;
      }
        
       $catModel = RelationSuppRest::find()
               ->andwhere('rest_org_id='.$agent_fid)
               ->andwhere('supp_org_id='.$org)
               ->andwhere('cat_id='.$cat_fid)
               ->andwhere('deleted=0')
               ->all();
       
       if ($catModel) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Catalog is already set to agent',$this->ip);       
        return 'Catalog error. Personal catalog already set to agent.';   
        exit;
      }
       
       $catModel = new RelationSuppRest;
              
       $catModel->rest_org_id = $agent_fid;
       $catModel->supp_org_id = $org;
       $catModel->cat_id = $cat_fid;
       $catModel->created_at = time();
       
     if (!$catModel->save()) {
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Internal error. Model cat-agent not saved',$this->ip);       
        return $catModel->getErrors();
        exit;    
     } else {
         $this->save_action(__FUNCTION__, $sessionId, 1,'OK: '.$catModel->id.' is updated',$this->ip);
         return 'OK.UPDATEDFID:'.$cat_fid;        
     } 
       
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }


 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @param string $newname
* @return mixed 
* @soap
*/
    
    public function renamePersonalCatalog($sessionId, $nonce, $cat_fid, $newname) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $persCat = Yii::$app->db->createCommand("select id from catalog where supp_org_id =".$org." and type = 2 and id=".$cat_fid)
      ->queryScalar();   
                
        if (!$persCat) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal catalog not found',$this->ip);       
        return 'Catalog error. Personal catalog with this FID not found.';   
        exit;
      }
      
       $catModel = Catalog::find()->andwhere('id='.$cat_fid)->one();
       
       if (!$catModel) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal Catalog not found',$this->ip);       
        return 'Product error. Personal catalog not found.';   
        exit;
      }
            
       $catModel->name = $newname;
       
     if (!$catModel->save()) {
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Internal error. Model catalog not saved',$this->ip);       
        return $catModel->getErrors();
        exit;    
     } else {
         $this->save_action(__FUNCTION__, $sessionId, 1,'OK: '.$catModel->id.' is updated',$this->ip);
         return 'OK.UPDATEDFID:'.$catModel->id;        
     } 
       
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }

 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @param string $newname
* @return mixed 
* @soap
*/
    
    public function deletePersonalCatalog($sessionId, $nonce, $cat_fid) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $persCat = Yii::$app->db->createCommand("select id from catalog where supp_org_id =".$org." and type = 2 and id=".$cat_fid)
      ->queryScalar();   
                
        if (!$persCat) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal catalog not found',$this->ip);       
        return 'Catalog error. Personal catalog with this FID not found.';   
        exit;
      }
      
       $catModel = Catalog::find()->andwhere('id='.$cat_fid)->one();
       
       if (!$catModel) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal Catalog not found',$this->ip);       
        return 'Product error. Personal catalog not found.';   
        exit;
      }
            
       $catModel->status = 0;
       
     if (!$catModel->save()) {
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Internal error. Model catalog not saved',$this->ip);       
        return $catModel->getErrors();
        exit;    
     } else {
         $this->save_action(__FUNCTION__, $sessionId, 1,'OK: '.$catModel->id.' is deleted',$this->ip);
         return 'OK.DELETEDFID:'.$catModel->id;        
     } 
       
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }

 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @param string $newname
* @return mixed 
* @soap
*/
    
    public function addPersonalCatalog($sessionId, $nonce, $newname) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $persCats = Yii::$app->db->createCommand("select count(*) from catalog where supp_org_id =".$org." and type = 2 and name='".$newname."'")
      ->queryScalar();   
        
        if ($persCats > 0) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal catalog: name already exists',$this->ip);       
        return 'Catalog error. Personal catalog with name already exists.';   
        exit;
      }
      
       $catModel = new Catalog;
            
       $catModel->type = 2;
       $catModel->supp_org_id = $org;
       $catModel->name = $newname;
       $catModel->status = 1;
       $catModel->created_at = time();
          
     if (!$catModel->save()) {
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Internal error. Model catalog not saved',$this->ip);       
        return $goodsModel->getErrors();
        exit;    
     } else {
         $this->save_action(__FUNCTION__, $sessionId, 1,'OK: '.$catModel->id.' is added',$this->ip);
         return 'OK.ADDEDFID:'.$catModel->id;        
     } 
       
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }


 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @return mixed 
* @soap
*/
    
    public function getPersonalCatalogs($sessionId, $nonce) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $persCats = Yii::$app->db->createCommand('select id as fid, name, status, created_at, updated_at from catalog where supp_org_id ='.$org.' and type = 2')
      ->queryAll();   
        
        if (!$persCats) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Personal catalogs  not found',$this->ip);       
        return 'Product error. Personal catalog not found.';   
        exit;
      }
      
      return $persCats;
            
            
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }



 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @return mixed 
* @soap
*/
    
    public function getBaseCatalogItem($sessionId, $nonce, $fid) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $baseCat = Yii::$app->db->createCommand('select id from catalog where supp_org_id ='.$org.' and type = 1')
      ->queryScalar();   
        
        if (!$baseCat) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Base catalog not found',$this->ip);       
        return 'Product error. Base catalog not found.';   
        exit;
      }
        
      $item = Yii::$app->db->createCommand('select '
              .' id as fid,'
              .' cat_id as catalog_id,'
              .' article,'
              .' product,'
              .' status,'
              .' created_at,'
              .' updated_at,'
              .' price,'
              .' units,'
              .' category_id,'
              .' ed,'  
              .' note'          
              . ' from catalog_base_goods where deleted = 0 and supp_org_id ='.$org.' and cat_id='.$baseCat.' and id = '.$fid)
      ->queryAll();
      
      if (!$item) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Item not found in Base catalog',$this->ip);       
        return 'Product error. Item not found in Base catalog.';   
        exit;
      }

      return $item;
            
            
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }

 /**
* get goods IDs from base catalog
* @param string $sessionId 
* @param string $nonce 
* @return mixed 
* @soap
*/
    
    public function getBaseCatalogIDs($sessionId, $nonce) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $baseCat = Yii::$app->db->createCommand('select id from catalog where supp_org_id ='.$org.' and type = 1')
      ->queryScalar();   
        
        if (!$baseCat) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Base catalog not found',$this->ip);       
        return 'Product error. Base catalog not found.';   
        exit;
      }
        
      $cats = Yii::$app->db->createCommand('select id as fid from catalog_base_goods where supp_org_id ='.$org.' and cat_id='.$baseCat.' and deleted =0')
      ->queryAll();
      
      if (!$cats) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Base catalog is empty',$this->ip);       
        return 'Product error. Base catalog is empty.';   
        exit;
      }

      $jcats = json_encode($cats);
      return $jcats;
            
            
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }

 /**
* add goods to base catalog
* @param string $sessionId 
* @param string $nonce 
* @param integer $fid
* @return mixed 
* @soap
*/
    
    public function deletefromBaseCatalog($sessionId, $nonce, $fid) 
    {    
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
            
        $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();      
        
        $baseCat = Yii::$app->db->createCommand('select id from catalog where supp_org_id ='.$org.' and type = 1')
      ->queryScalar();   
        
        if (!$baseCat) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Base catalog not found',$this->ip);       
        return 'Product error. Base catalog not found.';   
        exit;
      }
        
        $item = Yii::$app->db->createCommand('select id from catalog_base_goods where deleted = 0 and cat_id = '.$baseCat.' and supp_org_id ='.$org.' and id='.$fid)
      ->queryScalar();
        
       if (!$item) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Item not found in base catalog',$this->ip);       
        return 'Product error. Item in Base catalog not found.';   
        exit;
      }
        
        $goodsModel = CatalogBaseGoods::find()->andWhere('id ='.$item)->one();
     
        $goodsModel->deleted = 1;
          
     if (!$goodsModel->save()) {
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Internal error. Model not saved',$this->ip);       
        return $goodsModel->getErrors();
        exit;    
     } else {
         $this->save_action(__FUNCTION__, $sessionId, 1,'OK: '.$item.' is deleted',$this->ip);
         return 'OK.DELETEDFID:'.$goodsModel->id;        
     } 
        
       // return $item;
            
            
        } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
    }

    
/**
* @param string $name
* @return string 
* @soap
*/
 
    
    public function getHello($name) 
    {
        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        $this->save_action(__FUNCTION__,0, 1,'OK',$this->ip);  
         
        return 'Hello ' . $name.'! Server Date:'.gmdate("Y-m-d H:i:s") ;
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

        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
          
      // return $sess;    
          
      if ($lang == 'ENG') {
          
          $catview = 'api_category_eng_v';
          
      } else {
          
          $catview = 'api_category_rus_v';      
      }
      
      $cats = Yii::$app->db_api->createCommand('SELECT fid, denom, ifnull(up,0) as up FROM '.$catview)
      ->queryAll();
     
      $this->save_action(__FUNCTION__, $sessionId, 1,'OK',$this->ip);     
      return $cats;
    
      exit;
            
          
      } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
        
      
    }
    
/**
* Get Categories
* @param string $sessionId 
* @param string $nonce 
* @param string $lang
* @return mixed 
* @soap
*/
    
    public function getAgents($sessionId, $nonce, $lang) 
    {

        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
          
      // return $sess;    
      
      $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();   
      
      // return $org;
         
      $cats = Yii::$app->db->createCommand('select id as fid, type_id, name, city, address, zip_code,
          phone, email, website, created_at, updated_at, legal_entity, contact_name from organization 
          where id in ( select rest_org_id from relation_supp_rest where supp_org_id ='.$org.')')
      ->queryAll();
     
      $this->save_action(__FUNCTION__, $sessionId, 1,'OK',$this->ip);     
      
      return $cats ? $cats : 'No agents found!';
    
      exit;
            
          
      } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
      }
        
      
    }    
   
   
 /**
* add goods to base catalog
* @param string $sessionId 
* @param string $nonce 
* @param string $lang
* @param integer $units_fid
* @param integer $category_fid
* @param string $article
* @param string $product
* @param double $price
* @param double $pack
* @param string $cid  
* @return mixed 
* @soap
*/
    
    public function addtoBaseCatalog($sessionId, $nonce, $lang, $units_fid, $category_fid, $article, $product, $price, $cid, $pack) 
    {

        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
        
        if ($sess = $this->check_session($sessionId,$nonce)) {
         
      // return $sess;    
      
      $org = Yii::$app->db_api->createCommand('select org from api_access where id = (select acc from api_session where token ="'.$sessionId.'");')
      ->queryScalar();   
      
      $baseCat = Yii::$app->db->createCommand('select id from catalog where supp_org_id ='.$org.' and type = 1')
       ->queryScalar(); 
      
      if (!$baseCat) {
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Base catalog is not found',$this->ip);       
        return 'Base catalog error. Catalog is not found.';   
        exit;
      }
      
      $clearProduct = "'".str_replace('"','`',$product)."'";
      
      $countProd = Yii::$app->db->createCommand('select count(*) from catalog_base_goods where product ='.$clearProduct.'and cat_id='.$baseCat." and deleted =0" )
      ->queryScalar();   
      
      if ($countProd > 0) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Product name exists',$this->ip);       
        return 'Product error. Name already exists.';   
        exit;
      }
      
      $countArt = Yii::$app->db->createCommand("select count(*) from catalog_base_goods where article ='".$article."'and cat_id=".$baseCat." and deleted = 0")
      ->queryScalar();   
      
      if ($countArt > 0) {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Product art exists',$this->ip);       
        return 'Product error. Article already exists.';   
        exit;
      }
      
     $goodsModel = new CatalogBaseGoods;
     
     $goodsModel->product = str_replace('"','`',$product);
     $goodsModel->cat_id = $baseCat;
     $goodsModel->article = $article;
     $goodsModel->units = $pack;
     $goodsModel->created_at = time();
     $goodsModel->price = $price;
     $goodsModel->note = $cid;
     $goodsModel->deleted = 0;
     $goodsModel->market_place = 0;
     $goodsModel->status = 1;
     $goodsModel->ed = MpEd::find()->andwhere('id='.$units_fid)->one()->name;
     $goodsModel->supp_org_id = $org;
     $goodsModel->es_status = 1;
     $goodsModel->category_id = $category_fid;
     
     
     if (!$goodsModel->save()) {
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'Internal error. Model not saved',$this->ip);       
        return $goodsModel->getErrors();
        exit;    
     } else {
         $this->save_action(__FUNCTION__, $sessionId, 1,'OK',$this->ip);
         return 'OK.NEWFID:'.$goodsModel->id;        
     }
     
            
     
             
         
    //  $cats = Yii::$app->db->createCommand('select id as fid, type_id, name, city, address, zip_code,
    //      phone, email, website, created_at, updated_at, legal_entity, contact_name from organization 
    //      where id in ( select rest_org_id from relation_supp_rest where supp_org_id ='.$org.')')
    //  ->queryAll();
     
           
      
    //  return $cats ? $cats : 'No agents found!';
    
      exit;
            
          
      } else {
          
        $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip);       
        return 'Session error. Active session is not found.';

      exit;   
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
      
      if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
      
      if ($this->check_session($sessionId,$nonce)) {
          
      if ($lang == 'ENG') {
          
          $catview = 'api_units_eng_v';
          
      } else {
          
          $catview = 'api_units_rus_v';      
      }
      
      $cats = Yii::$app->db_api->createCommand('SELECT fid, denom FROM '.$catview)
      ->queryAll();
     
      $this->save_action(__FUNCTION__, $sessionId, 1,'OK',$this->ip); 
      return $cats;
      exit;
      
      } else {
      
      $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip); 
      //return $res;
      return 'Session error. Active session is not found.';
      exit;   
      }
        
      
    }
   
 
  /**
   * Close session
   * @param string $sessionId 
   * @param string $nonce 
   * @return mixed result
   * @soap
   */
   
  public function CloseSession($sessionId,$nonce) {
      
    if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];  
      
    if ($this->check_session($sessionId,$nonce)) {
          
        $sess = ApiSession::find()->where('token = :token and now() between fd and td',
                [':token' => $sessionId])->one();   
        
        $sess->td = gmdate('Y-m-d H:i:s');
           $sess->status = 2;           
                      
           if(!$sess->save())
           {
                return $sess->errors;
                exit;  
           } else {
           
           $res = $this->save_action(__FUNCTION__, $sessionId, 1,'OK',$sess->ip);    
           return 'OK_CLOSED :'.$sess->token; 
            }

      
      } else {
      
          
      $res = $this->save_action(__FUNCTION__, $sessionId, 0,'No active session',$this->ip); 
      return 'Session error. Active session is not found.';
      exit;   
      }
  }
  
    
/**
   * Soap authorization open session
   * @return mixed result of auth
   * @soap
   */
   
  public function OpenSession() {
      
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($this->username)) 
    {
    header('WWW-Authenticate: Basic realm="f-keeper.ru"');
    header('HTTP/1.0 401 Unauthorized');
    header('Warning: WSS security in not provided in SOAP header');
    
    if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];
    $this->save_action(__FUNCTION__,0, 0,'Auth error HTTP/1.0 401 Unauthorized',$this->ip);
    exit;
   
    } else { 
        
               
        if (!$acc = ApiAccess::find()->where('login = :username and now() between fd and td',[':username' => $this->username])->one())
        {
            
            $this->save_action(__FUNCTION__, 0, 0,'Wrong login',$this->ip); 
            return 'Auth error. Login is not found.';
            exit;
        };
        
        if (Yii::$app->getSecurity()->validatePassword($this->password, $acc->password)) {
            
           $sessionId = Yii::$app->getSecurity()->generateRandomString();
           
           $oldsess = ApiSession::find()->orderBy('fid DESC')->one();  
           
           $sess = new ApiSession();
           
           if ($oldsess) {
           $sess->fid = $oldsess->fid+1;    
           } else {
           $sess->fid = 1;    
           }
                     
           $sess->token = $sessionId;
           $sess->acc = $acc->fid;
           $sess->nonce = $this->nonce;
           $sess->fd = gmdate('Y-m-d H:i:s');
           $sess->td = gmdate('Y-m-d H:i:s',strtotime('+1 day'));
           $sess->ver = 1;
           $sess->status = 1;           
           $sess->ip = $this->ip;
           $sess->extimefrom = $this->extimefrom;
           
           if(!$sess->save())
           {
                return $sess->errors;
                exit;  
           } else 
                      
           $res = $this->save_action(__FUNCTION__, $sess->token, 1,'OK',$this->ip);
           
           return 'OK_SOPENED:'.$sess->token;
           
           
        } else {
        
           $res = $this->save_action(__FUNCTION__, 0, 0,'Wrong password',$this->ip); 
           
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
  
  
  
    public function security($header) {
    
       
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($header->UsernameToken->Username)) // Проверяем послали ли нам данные авторизации (BASIC)
        {
            header('WWW-Authenticate: Basic realm="fkeeper.ru"'); // если нет, даем отлуп - пришлите авторизацию
            header('HTTP/1.0 401 Unauthorized');
            
            if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];
            $this->save_action(__FUNCTION__,0, 0,'Auth error HTTP/1.0 401 Unauthorized',$this->ip);
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
  
  public function check_session($session, $nonce) {
  
      if ($sess = ApiSession::find()->where('token = :token and nonce = :nonce and now() between fd and td',
                [':token' => $session,'nonce' => $nonce])->one()) {
            
        return true;
        
        } else {
            
        return false;
        
        }
      
      
  }
  
  public function save_action ($func, $sess, $result, $comment,$ip)   {
      
     $act = new ApiActions;
     
     $currSess = ApiSession::find()->where('token = :token',[':token' => $sess])->one();
     
     if ($currSess) {
         $act->session = $currSess->fid;
         $act->ip = $currSess->ip;
     } else {
         $act->session=0;
         $act->ip=$ip;
     }
         
     $act->action = $func;
     $act->created = gmdate('Y-m-d H:i:s');
     $act->result = $result;
     $act->comment = $comment;
           
     if(!$act->save())
           {
                return $act->errors;
                exit;  
           } else {
                      
           return true; 
            }  
    
            return $act->session;
      
  }
  
   
}