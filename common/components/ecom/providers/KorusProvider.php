<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:10 PM
 */

namespace common\components\ecom\providers;


use common\components\ecom\AbstractProvider;
use common\components\ecom\ProviderInterface;
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
        da(base64_encode($string));
        $client = $this->client;
        //da(get_class_methods($client));
        $client2 = new \SoapClient('https://edi-ws.esphere.ru/edi.wsdl');

        $relation = $this->getRelation($client, 0, $login, $pass);
        $array = [
            "Name" => $login,
            'Password' => $pass,
            "PartnerIln" => $relation['partner-iln'],
            "DocumentType" => $relation['document-type'],
            "DocumentVersion" => $relation['document-version'],
            "DocumentStandard" => $relation["document-standard"],
            "DocumentTest" => $relation['document-test'],
            "RelationId" => $relation['relation-id'],
            "DocumentContent" => $string,
            "action" => "send"
        ];
        //da($client2->__getFunctions());
        $result = $client2->__soapCall("process", ["SendInput" => $array]);
        da($result);

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