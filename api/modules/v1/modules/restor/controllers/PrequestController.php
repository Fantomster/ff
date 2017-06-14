<?php

namespace api\modules\v1\modules\restor\controllers;

use Yii;
use yii\web\Controller;
// use yii\mongosoft\soapserver\Action;
use yii\httpclient\Client;
use api\common\models\RkSession;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class PrequestController extends Controller {
    
    public $enableCsrfValidation = false;
    
    protected $authenticated = false;
    
    private $sessionId = '';
    private $username;
    private $password;
    
        
    public function actionIndex() {
        
      $url = "http://ws-w01m.ucs.ru/WSClient/api/Client/Cmd";
    
    $restr = "199990046";
    
    $model = RkSession::find()->where('id=1')->one();
    $cook = $model->cook;
    
    // $cook =    '8AC676108E295CBD193F9FD1D92D97E95DB023F2C4715BBAE4E73FF47CBDCA9463F8443FC5A0119BADF2BB57A40A9FF33653C1FAE71FD3FD2CC3592B2250356D965390F5931C26522575A500EE42B2999DA3468B07A0C908FEF94AE6C7E0DD618F2890EF88AB125223F5DF9ACBE36F988CCDE622DAACEB2B03BB5E34A2D4E1A06184DFDCEBF11821F874C7A211491B021C365FCBE1BB3D304E264627C74B8BC1D986BF1E80AE01AECBDD150BD5B3179B6714BF8213001E3B983708AEF70764161CF254F3F2B9512FFC06955EEA2DDE841438B21E20F8448F0E1BCCBFEC4C4BCF33DD6F70ED5F2CCDEDBBCD6A4F5FB344F4301D98F381EC42024DD5E82877135EDB167188E4E20A0D5FC3EB328CE15942B23E680CADBDFF7EFB9F0D535FFC02BD8F322F90254DA19442170E9FDFE3A2BCFCFE06C2C79B50407E2B37ACD8BB4A21286532D379A7A2A31DAD9B46A11BB2B2DE97D00C5189837788BC91744DEBF405';
    
    
/*
     $xml = '<?xml version="1.0" encoding="utf-8" ?>
        <RQ cmd="get_objectinfo">
        <PARAM name="object_id" val="'.$restr.'"/>
        </RQ>';   
  */
    
    $xml = '<?xml version="1.0" encoding="utf-8" ?>
    <RQ cmd="sh_get_goods" tasktype="any_call" guid="sdfsd" callback="https://api.f-keeper.ru/api/web/v1/restor/callback" timeout="int">
    <PARAM name="object_id" val="'.$restr.'" />
    <PARAM name="goodgroup_rid" val="1" />
    </RQ>';
       
    // setcookie('_ASPXAUTH',$cook);
    
    $headers = array(
        "Content-type: application/xml; charset=utf-8",
        "Content-length: " . strlen($xml),
        "Connection: close", 
    );
/*
    echo "<hr>";
    var_dump($xml);
    echo "<hr>";
  */
    
  //  $fp = fopen('runtime/logs/http-request1.log', 'w');
    
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt ($ch, CURLOPT_COOKIE, ".ASPXAUTH=".$cook.";"); 
    
    curl_setopt($ch, CURLOPT_VERBOSE, true);
 //   curl_setopt($ch, CURLOPT_STDERR,$fp);

    $data = curl_exec($ch); 
    
    
    $info = curl_getinfo($ch);
    // echo "Request result:<br>"; 
    // var_dump($info);
    // echo "<hr>";
    // echo "Response:<br>";
    // echo $data;
    // echo "<hr><hr><hr>";
    // var_dump ($data);
    
    
   // $myXML = new \SimpleXMLElement($data);
    $myXML   = simplexml_load_string($data);
   // $array = $this->XML2Array($myXML);
    $array = json_decode(json_encode((array) $myXML), 1);
    $array = array($myXML->getName() => $array);
    

    /*
    foreach ($myobj->xpath('//OBJECTINFO') as $obj) {
    echo 'Объект с id ', $obj->id, ', имя ', $obj->name, ', адрес:', $obj->address, PHP_EOL;
    }
    */
    /*
    $myXML = new \DOMDocument('1.0', 'utf-8');
    
    $myXML->load($data);
    */
   // $root = $myXML->documentElement;
    
   //  $objects = $myXML->childNodes;
    
    // var_dump($myXML);
    
    // var_dump($array);
    
    $objectinfo = $array['RP']['OBJECTINFO'];
    
   // var_dump($objectinfo);
    
    if (!$objectinfo) {
        
            foreach ($array['Error'] as $obj) {
          $res = 'Ошибка: '.$obj['code'].'<br> Описание ошибки: '.$obj['Text'].PHP_EOL;
            }
        
    } else {
            
            foreach ($array['RP']['OBJECTINFO'] as $obj) {
          $res = 'Объект id: '.$obj['id'].'<br>имя: '.$obj['name'].'<br>адрес: '.$obj['address'].PHP_EOL;
            }
    
    }
    /*
    foreach ($objects as $object) {
    
    $id = $object->getAttribute('id');
    $name = $object->getAttribute('name');
    $address = $object->getAttribute('address');
    
    $restors[] = array('id' => $id, 'name' => $name, 'address' => $address);
    }
    
    print_r($restors);
    
    */
    
    return $this->render('index'  ,[
                   'myXML' => $myXML,
                   'objectinfo' => $objectinfo,
                   'data' => $data,
                   'info' => $info,
                   'res'  => $res,
         
               ]);
    
    if(curl_errno($ch))
    print curl_error($ch);
    else
    curl_close($ch);
        
    }
    


public function actionSendlogin() {
        
    $licReq = "TaS1MFk5aRk=tuKE2zLI2eqnCJnATjuErPNyIFl/vTQ1+IgJj7Rhx+nnoNq+k1K90kqofh4qDg+g4Lo4mlIg2tCQfxnDmitpzKkIyUIDFy4J6tud0pZf9nahgfFcwiGtZNFUM1I3h/J+Vu78vxp9wHkWRQ3sI9yy7A/o1QKKOyGi03S5/9TMA1v92TdYURdb8jdUcQJgui1dQIgHzE56O9OqV/DGVT5DhqjSfsvZIOmaj0+0FHJSrQZt7cO628h6UrA916dDTECb9fDWjprydt+oYPudzcwx02m7CmEDBSEn7CJcY+OE0y3+Q3vBUZNuEQ=="; 
    $rlogin = '5889';
    $rpass = 'uqbihcj';
    $rtoken = '48eabe9e-fc50-4b12-833c-ccc41480852d';
                  
    $usrReq = base64_encode($rlogin.';'.strtolower(md5($rlogin.$rpass)).';'.strtolower(md5($rtoken)));
    
    echo $usrReq."<br>";
    
    
    
    // usr : Base64(userName + “;” + lowercase(hPassword) + “;” + lowercase(md5(token))).
    // hPassword – вычисляется как MD5(userName+password)
    
    
    $url = "http://ws-w01m.ucs.ru/WSClient/api/Client/Login";
    // $xml =  mb_convert_encoding('<?xml version="1.0" encoding="utf-8"  ><AUTHCMD key="test" usr="test2"/>','utf-8');
    
    //$xml = New \DOMDocument();
    //$xml->loadXML('<AUTHCMD key="test" usr="test2"/>');
    //$xml = array('AUTHCMD'=>array('key'=>'test1','usr'=>'test2'));
    $xml ='<?xml version="1.0" encoding="UTF-8"?><AUTHCMD key="'.$licReq.'" usr="'.$usrReq.'"/>';
    
    echo "<hr>";
    var_dump($xml);
    echo "<hr>";
   
    $headers = array(
        "Content-type: application/xml; charset=utf-8",
        "Content-length: " . strlen($xml),
        "Connection: close", 
    );

    $fp = fopen('runtime/logs/http-request.log', 'w');
    
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR,$fp);

    $data = curl_exec($ch); 
    
    $info = curl_getinfo($ch);
    
    echo "Request result:<br>"; 
    var_dump($info);
    echo "<hr>";
    echo "Response:<br>";
    echo $data;
    var_dump ($data);
    
    echo "<hr><hr><hr>";
    
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $data, $matches);
    $cookies = array();
    
    foreach($matches[1] as $item) {
    parse_str($item, $cookie);
    $cookies = array_merge($cookies, $cookie);
    }
    
    echo "Cookies:<br>";
    var_dump($cookies);

if(curl_errno($ch))
    print curl_error($ch);
else
    curl_close($ch);




    
    /*
    
    $sData = New \DOMDocument();
   // $sData->encoding = "utf-8";
   
        
    $sData->loadXML("<AUTHCMD key='тест' usr='test2'/>");
        
    if (!$sData) {
    echo 'Ошибка при разборе документа';
    exit;
}
    
        echo "Sending auth request..<br>";
        $client = new Client([
                                'transport' => 'yii\httpclient\CurlTransport'
                            ]);
        $response = $client->createRequest()
        ->setMethod('post')
        ->setUrl('http://ws-w01m.ucs.ru/WSClient/api/Client/Login')
        ->setFormat(Client::FORMAT_XML)
       // ->charset('utf-8')        
          ->setData($sData)      
              
        ->send();

   //     if ($response->isOk) {
      //  $newUserId = $response->data['id'];
      // var_dump($response);    
      //   echo $response->content;
      //   echo "<hr";
         echo "<hr>";  
         var_dump($response->content);
         echo "<hr>";
         var_dump($response);
   // }
    */
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
 
    public function actionGetgoods() {
        
    $url = "http://ws-w01m.ucs.ru/WSClient/api/Client/Cmd";
    
    $restr = "199990046";
    
    $cook =   '80661EBADE55D903C2F795E1801AA79144A7C7B82CAE34A3C43D1BA45FD04B2'
             .'31DED8E7144D8AA99847BE4A6E8EE2A8658AE3533FDFACFA2EF1ABE0EBA88FF'
             .'DA5875A440CFC30F8B62A32844AD6819C5A043E6B36D9B89EADA0379B4052D8'
             .'E3EE5244CF494905D5C76DAE32432B88C972D06A4E3E03579410F0442110516'
             .'497F52C41B7CB2C2437D8EB4986FF31ED92F4733AD833ED32C79C9FE02B0067'
             .'FCB49EED4936638CC45ACF0134C093DB8A570F65AD433512894E39531F435EF'
             .'120D6C0181D9610689B3AB13F1450B1953C1511A2641DD609722C5BEE37F486'
             .'AD5CDA3A492154B7B4F0232A27C19FC6F9C33DD57935C56D068A4D146F35A6C'
             .'5C6DF522E3E6D988C3CFB05C95B3FA19945A8E0C666B68D07989FE018EEFE12'
             .'C2B4714D524D7E2B05CA4A743944DC83807A12EB6243D742671F83600DD0FA0'
             .'12DE1D35BC66105CD6328EE98672B1DBFA41A29AC3392BEFF25C4D296E80005'
             .'5036ECD73FD';
    
    

    $xml = '<?xml version="1.0" encoding="utf-8" ?>
        <RQ cmd="get_objectinfo">
        <PARAM name="object_id" val="'.$restr.'"/>
        </RQ>';    
       
    // setcookie('_ASPXAUTH',$cook);
    
    $headers = array(
        "Content-type: application/xml; charset=utf-8",
        "Content-length: " . strlen($xml),
        "Connection: close", 
    );

    echo "<hr>";
    var_dump($xml);
    echo "<hr>";
    
    $fp = fopen('runtime/logs/http-request1.log', 'w');
    
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt ($ch, CURLOPT_COOKIE, ".ASPXAUTH=".$cook.";"); 
    
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR,$fp);

    $data = curl_exec($ch); 
    
    
    $info = curl_getinfo($ch);
    echo "Request result:<br>"; 
    var_dump($info);
    echo "<hr>";
    echo "Response:<br>";
    echo $data;
    echo "<hr><hr><hr>";
    var_dump ($data);
    
    
   // $myXML = new \SimpleXMLElement($data);
    $myXML   = simplexml_load_string($data);
   // $array = $this->XML2Array($myXML);
    $array = json_decode(json_encode((array) $myXML), 1);
    $array = array($myXML->getName() => $array);
    

    /*
    foreach ($myobj->xpath('//OBJECTINFO') as $obj) {
    echo 'Объект с id ', $obj->id, ', имя ', $obj->name, ', адрес:', $obj->address, PHP_EOL;
    }
    */
    /*
    $myXML = new \DOMDocument('1.0', 'utf-8');
    
    $myXML->load($data);
    */
   // $root = $myXML->documentElement;
    
   //  $objects = $myXML->childNodes;
    
    // var_dump($myXML);
    
    // var_dump($array);
    
    $objectinfo = $array['RP']['OBJECTINFO'];
    
    var_dump($objectinfo);
    
    if (!$objectinfo) {
        
            foreach ($array['Error'] as $obj) {
            echo 'Ошибка: ', $obj['code'], '<br> Описание ошибки: ', $obj['Text'], PHP_EOL;
            }
        
    } else {
            
            foreach ($array['RP']['OBJECTINFO'] as $obj) {
            echo 'Объект id: ', $obj['id'], '<br>имя: ', $obj['name'], '<br>адрес: ', $obj['address'], PHP_EOL;
            }
    
    }
    /*
    foreach ($objects as $object) {
    
    $id = $object->getAttribute('id');
    $name = $object->getAttribute('name');
    $address = $object->getAttribute('address');
    
    $restors[] = array('id' => $id, 'name' => $name, 'address' => $address);
    }
    
    print_r($restors);
    
    */
    
    if(curl_errno($ch))
    print curl_error($ch);
    else
    curl_close($ch);
    
    }
    
        
    public function actionHello() 
    {
      // echo "1";
       phpinfo();
    }
    
    public function actionSettings() 
    {
      
        return $this->render('settings' // ,[
              //      'searchModel' => $searchModel,
              //      'dataProvider' => $dataProvider,
              // ]
                );
      //  $langs = Yii::$app->db_api->createCommand('SELECT * FROM api_lang')
      //      ->queryAll();
        
      //  var_dump($langs);
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
  
  function XML2Array(\SimpleXMLElement $parent)
{
    $array = array();

    foreach ($parent as $name => $element) {
        ($node = & $array[$name])
            && (1 === count($node) ? $node = array($node) : 1)
            && $node = & $node[];

        $node = $element->count() ? XML2Array($element) : trim($element);
    }

    return $array;
}

   
}
