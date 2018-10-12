<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:10 PM
 */

namespace common\components\ecom\providers;


use common\components\ecom\AbstractProvider;
use common\components\ecom\EdiClass;
use common\components\ecom\ProviderInterface;
use common\components\ecom\SendInput;
use common\models\Organization;
use yii\base\Exception;

/**
 * Class Provider
 *
 * @package common\components\ecom\providers
 */
class KorusProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var mixed
     */
    public $client;

    /**
     * Provider constructor.
     */
    public function __construct()
    {
        $this->client = \Yii::$app->siteApiKorus;
    }

    /**
     * @param $login
     * @param $pass
     * @return null
     * @throws \yii\base\Exception
     */
    public function getResponse($login, $pass){
        $object = $this->client->getList(['user' => ['login' => $login, 'pass' => $pass]]);

        if ($object->result->errorCode != 0) {
            throw new Exception('EComIntegration getList Error №' . $object->result->errorCode);
        }
        $list = $object->result->list ?? null;
        if (!$list) {
            throw new Exception('No files for ' . $login);
        }

        return $list;
    }

    /**
     * @param array $list
     * @throws \yii\db\Exception
     */
    public function insertFilesInQueue(array $list)
    {
        $batch = [];
        $files = (new \yii\db\Query())
            ->select(['name'])
            ->from('edi_files_queue')
            ->where(['name' => $list])
            ->indexBy('name')
            ->all();

        foreach ($list as $name) {
            if (!array_key_exists($name, $files)) {
                $batch[] = [$name];
            }
        }

        if (!empty($batch)) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                \Yii::$app->db->createCommand()->batchInsert('edi_files_queue', ['name'], $batch)->execute();
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollback();
                \Yii::error($e->getMessage());
            }
        }
    }


    /**
     * @param \common\models\Organization $vendor
     * @param String                      $string
     * @param String                      $remoteFile
     * @param String                      $login
     * @param String                      $pass
     * @return bool
     */
    public function sendDoc(String $string, String $remoteFile, String $login, String $pass): bool
    {
        //da(base64_encode($string));
        //da($string);
        //$client = $this->client;

        //$client2 = new \SoapClient('https://edi-ws.esphere.ru/edi.wsdl');

        //$relation = $this->getRelation($client, 0, $login, $pass);
        $edi = new EdiClass();
        $sendInp = new SendInput();
        $sendInp->Name = $login;
        $sendInp->Password = $pass;
        $sendInp->RelationId = 156541035152131;
        $sendInp->DocumentContent = 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48T1JERVI+CiAgICA8RE9DVU1FTlROQU1FPjIyMDwvRE9DVU1FTlROQU1FPgogICAgPE5VTUJFUj4xMzgwODwvTlVNQkVSPgogICAgPERBVEU+MjAxOC0xMC0xMjwvREFURT4KICAgIDxERUxJVkVSWURBVEU+MjAxOC0xMC0xMjwvREVMSVZFUllEQVRFPgogICAgPENVUlJFTkNZPlJVQjwvQ1VSUkVOQ1k+CiAgICA8U1VQT1JERVI+MTM4MDg8L1NVUE9SREVSPgogICAgPERPQ1RZUEU+TzwvRE9DVFlQRT4KICAgIDxDQU1QQUlHTk5VTUJFUj4xMzgwODwvQ0FNUEFJR05OVU1CRVI+CiAgICA8T1JEUlRZUEU+T1JJR0lOQUw8L09SRFJUWVBFPgogICAgPEhFQUQ+CiAgICAgICAgPFNVUFBMSUVSPjIwMDAwMDAwMDAxMzY8L1NVUFBMSUVSPgogICAgICAgIDxCVVlFUj4yMDAwMDAwMDAwNzc3PC9CVVlFUj4KICAgICAgICA8REVMSVZFUllQTEFDRT4yMDAwMDAwMDAwNzc3PC9ERUxJVkVSWVBMQUNFPgogICAgICAgIDxTRU5ERVI+MjAwMDAwMDAwMDc3NzwvU0VOREVSPgogICAgICAgIDxSRUNJUElFTlQ+MjAwMDAwMDAwMDEzNjwvUkVDSVBJRU5UPgogICAgICAgIDxFRElJTlRFUkNIQU5HRUlEPjEzODA4PC9FRElJTlRFUkNIQU5HRUlEPgogICAgICAgICAgICAgICAgICAgICAgICA8L0hFQUQ+CjwvT1JERVI+Cg==';


        $array =[
            "Name" => $login,
            'Password' => $pass,
            "RelationId" => 156541035152131,
            "DocumentContent" => 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48T1JERVI+CiAgICA8RE9DVU1FTlROQU1FPjIyMDwvRE9DVU1FTlROQU1FPgogICAgPE5VTUJFUj4xMzgwODwvTlVNQkVSPgogICAgPERBVEU+MjAxOC0xMC0xMjwvREFURT4KICAgIDxERUxJVkVSWURBVEU+MjAxOC0xMC0xMjwvREVMSVZFUllEQVRFPgogICAgPENVUlJFTkNZPlJVQjwvQ1VSUkVOQ1k+CiAgICA8U1VQT1JERVI+MTM4MDg8L1NVUE9SREVSPgogICAgPERPQ1RZUEU+TzwvRE9DVFlQRT4KICAgIDxDQU1QQUlHTk5VTUJFUj4xMzgwODwvQ0FNUEFJR05OVU1CRVI+CiAgICA8T1JEUlRZUEU+T1JJR0lOQUw8L09SRFJUWVBFPgogICAgPEhFQUQ+CiAgICAgICAgPFNVUFBMSUVSPjIwMDAwMDAwMDAxMzY8L1NVUFBMSUVSPgogICAgICAgIDxCVVlFUj4yMDAwMDAwMDAwNzc3PC9CVVlFUj4KICAgICAgICA8REVMSVZFUllQTEFDRT4yMDAwMDAwMDAwNzc3PC9ERUxJVkVSWVBMQUNFPgogICAgICAgIDxTRU5ERVI+MjAwMDAwMDAwMDc3NzwvU0VOREVSPgogICAgICAgIDxSRUNJUElFTlQ+MjAwMDAwMDAwMDEzNjwvUkVDSVBJRU5UPgogICAgICAgIDxFRElJTlRFUkNIQU5HRUlEPjEzODA4PC9FRElJTlRFUkNIQU5HRUlEPgogICAgICAgICAgICAgICAgICAgICAgICA8L0hFQUQ+CjwvT1JERVI+Cg=='
        ];

        $soap_request = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:edi="http://edi-express.esphere.ru/">
   <soapenv:Header/>
   <soapenv:Body>
      <edi:SendInput>
         <edi:Name>2000000000777</edi:Name>
         <edi:Password>hgf8rt1c4</edi:Password>
         <edi:RelationId>156541035152131</edi:RelationId>
         <edi:DocumentContent>PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48T1JERVI+CiAgICA8RE9DVU1FTlROQU1FPjIyMDwvRE9DVU1FTlROQU1FPgogICAgPE5VTUJFUj4xMzgwODwvTlVNQkVSPgogICAgPERBVEU+MjAxOC0xMC0xMjwvREFURT4KICAgIDxERUxJVkVSWURBVEU+MjAxOC0xMC0xMjwvREVMSVZFUllEQVRFPgogICAgPENVUlJFTkNZPlJVQjwvQ1VSUkVOQ1k+CiAgICA8U1VQT1JERVI+MTM4MDg8L1NVUE9SREVSPgogICAgPERPQ1RZUEU+TzwvRE9DVFlQRT4KICAgIDxDQU1QQUlHTk5VTUJFUj4xMzgwODwvQ0FNUEFJR05OVU1CRVI+CiAgICA8T1JEUlRZUEU+T1JJR0lOQUw8L09SRFJUWVBFPgogICAgPEhFQUQ+CiAgICAgICAgPFNVUFBMSUVSPjIwMDAwMDAwMDAxMzY8L1NVUFBMSUVSPgogICAgICAgIDxCVVlFUj4yMDAwMDAwMDAwNzc3PC9CVVlFUj4KICAgICAgICA8REVMSVZFUllQTEFDRT4yMDAwMDAwMDAwNzc3PC9ERUxJVkVSWVBMQUNFPgogICAgICAgIDxTRU5ERVI+MjAwMDAwMDAwMDc3NzwvU0VOREVSPgogICAgICAgIDxSRUNJUElFTlQ+MjAwMDAwMDAwMDEzNjwvUkVDSVBJRU5UPgogICAgICAgIDxFRElJTlRFUkNIQU5HRUlEPjEzODA4PC9FRElJTlRFUkNIQU5HRUlEPgogICAgICAgICAgICAgICAgICAgICAgICA8L0hFQUQ+CjwvT1JERVI+Cg==</edi:DocumentContent>
      </edi:SendInput>
   </soapenv:Body>
</soapenv:Envelope>';
        $header = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"run\"",
            "Content-length: ".strlen($soap_request),
        );

        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, "https://edi-ws.esphere.ru/send" );
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST,           true );
        curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $soap_request);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
        da(curl_exec($soap_do));



//        $obj = $client->sendDoc(['user' => ['login' => $login, 'pass' => $pass], 'fileName' => $remoteFile, 'content' => $string]);
//        if (isset($obj) && isset($obj->result->errorCode) && $obj->result->errorCode == 0) {
//            return true;
//        } else {
//            Yii::error("Ecom returns error code");
//            return false;
//        }
    }


    private function getRelation($client, $index, $login, $pass){
        $res = $client->process(["Name" => $login, 'Password' => $pass]);
        $cnt = $res->Cnt;
        $arr = (array)$cnt;
        $relResp = $arr['relation-response'];
        $relation = $relResp->relation[$index];
        return (array)$relation;
    }
}