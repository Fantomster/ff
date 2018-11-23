<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\RkServicedata;
use api\common\models\RkSession;
use api\common\models\RkAccess;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use common\models\User;
use yii\base\Object;
use Yii;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class AuthHelper extends Object
{

    public $org;
    public $restr;
    public $logCategory = "rkws_callback_log";

    public function init()
    {

        if (Yii::$app instanceof Yii\console\Application) {
            return;
        }

        if (Yii::$app->user->isGuest) {
            return;
        }

        if (isset(User::findOne(Yii::$app->user->id)->organization_id)) {
            $this->org = User::findOne(Yii::$app->user->id)->organization_id;
        }

        if (isset($this->org)) {
            $this->restr = RkServicedata::find()->andwhere('org = :org', [':org' => $this->org])->one();
        }
    }

    public function Authorizer()
    {
        //  $auth = $this->sendAuth();

        if (!$check = $this->checkAuthBool()) {

            //     echo "Проверка авторизации провалена. Перехожу к попытке авторизации";
            //    exit;

            $auth = $this->sendAuth();

            if (!$auth['respcode'] == '0') {

                echo "Can't perform authorization";
                var_dump($auth['$resp']);
                return false;
            }
        }
        return true;
    }

    public function checkAuthBool()
    {

        $xml = '<?xml version="1.0" encoding="utf-8" ?>
        <RQ cmd="get_objectinfo">
        <PARAM name="object_id" val="199990046"/>
        </RQ>';

        $res = ApiHelper::sendCurl($xml, $this->restr);

        //  echo "Checkauthbool<br>";
        //  var_dump($res);
        //  echo "dsf";
        //  var_dump($res['respcode']['code']);
        //   throw new Exception(print_r($res,true));

        if ($res['respcode']['code'] == '0') {
            file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . '========EVENT==START=================' . PHP_EOL, FILE_APPEND);
            file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . date("Y-m-d H:i:s") . ':CHECKAUTHBOLL:SUCCESS' . PHP_EOL, FILE_APPEND);
            file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . '========EVENT==END===================' . PHP_EOL, FILE_APPEND);
            return true;
        } else {
            file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . '========EVENT==START=================' . PHP_EOL, FILE_APPEND);
            file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . date("Y-m-d H:i:s") . ':CHECKAUTHBOLL:FAILED' . PHP_EOL, FILE_APPEND);
            file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . '========EVENT==END===================' . PHP_EOL, FILE_APPEND);
            return false;
        }
    }

    public function sendAuth()
    {

        // $url = "http://ws.ucs.ru/WSClient/api/Client/Login";

        $url = Yii::$app->params['rkeepAuthURL'] ? Yii::$app->params['rkeepAuthURL'] : 'http://ws.ucs.ru/WSClient/api/Client/Login';

        $restrModel = RkAccess::find()->andwhere('fid = 1')->one();

        $licReq = $restrModel->lic;
        $rlogin = $restrModel->login;
        $rpass  = $restrModel->password;
        $rtoken = $restrModel->token;

        $usrReq = base64_encode($rlogin . ';' . strtolower(md5($rlogin . $rpass)) . ';' . strtolower(md5($rtoken)));

        // var_dump($usrReq);
        // exit;

        $xml = '<?xml version="1.0" encoding="UTF-8"?><AUTHCMD key="' . $licReq . '" usr="' . $usrReq . '"/>';

        $headers = array(
            "Content-type: application/xml; charset=utf-8",
            "Content-length: " . strlen($xml),
            "Connection: close",
        );


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 1); // Раскомментировать в случае дебага, 
        //  иначе header лезет в $data строкой и не получается XML (xsupervisor 04.07.2017

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        //  curl_setopt($ch, CURLOPT_STDERR,$fp);

        $data = curl_exec($ch);
        $info = curl_getinfo($ch);

        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $data, $matches);
        $cookies = array();

        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }

        $cook = $cookies['_ASPXAUTH'];

        $partx = Substr($data, strpos($data, '<?xml'));

        $myXML = simplexml_load_string($partx);
        $array = json_decode(json_encode((array) $myXML), 1);
        $array = array($myXML->getName() => $array);

        // var_dump($myXML);
        // var_dump($cook);

        $respcode = $array['Error']['@attributes']['code'];

        if (!$respcode === null) {
            $respcode = $array['RP']['@attributes']['code'];
        }

        $objectinfo = $array['Error']['@attributes'];

        if ($cook && $respcode === '0') {
            file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . '========EVENT==START=================' . PHP_EOL, FILE_APPEND);
            file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . date("Y-m-d H:i:s") . ':SENDAUTH OK RECEIVED' . PHP_EOL, FILE_APPEND);


            $sess = RkSession::find()->andwhere('acc= :acc', [':acc' => 1])->andwhere('status = 1')->one();

            // $sessmax = RkSession::find()->max('fid');   

            $newsess = new RkSession();

            $newsess->cook   = $cook;
            $newsess->fd     = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $newsess->td     = Yii::$app->formatter->asDate('2030-01-01 23:59:59', 'yyyy-MM-dd HH:mm:ss');
            $newsess->acc    = 1;
            $newsess->status = 1;

            if ($sess) {

                //       echo "sess есть";
                //       var_dump($newsess);
                //      exit;

                $sess->td     = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                $sess->status = 0;
                $newsess->fid = $sess->fid;
                $newsess->ver = $sess->ver + 1;

                $transaction = Yii::$app->db_api->beginTransaction();

                if ($sess->save(false) && $newsess->save(false)) {

                    $transaction->commit();
                    file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . 'NEW SESSION IS CREATED (ID:' . $newsess->id . ')' . PHP_EOL, FILE_APPEND);
                } else {

                    var_dump($sess->getErrors());
                    var_dump($newsess->getErrors());

                    $transaction->rollback();
                    exit;
                }
            } else {
                $newsess->fid    = 1;
                $newsess->ver    = 1;
                $newsess->status = 1;

                //    var_dump($newsess);
                //    exit;

                if (!$newsess->save(false)) {
                    var_dump($newsess->getErrors());
                    exit;
                } else {
                    file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . '=================' . PHP_EOL, FILE_APPEND);
                    file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . print_r($objectinfo, true) . PHP_EOL, FILE_APPEND);
                    file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . '=================' . PHP_EOL, FILE_APPEND);
                    file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . print_r($respcode, true) . PHP_EOL, FILE_APPEND);
                    file_put_contents(Yii::getAlias('@app') . '/runtime/logs/auth.log', PHP_EOL . '========EVENT==END===================' . PHP_EOL, FILE_APPEND);
                }
            }
        } else {

            echo "Auth not successful";
            exit;
            return false;
        }

        if (curl_errno($ch)) {
            print curl_error($ch);
        } else {
            curl_close($ch);
        }

        return ['resp' => $objectinfo, 'respcode' => $respcode];
    }

    protected function log($message) {
        \Yii::info($message, $this->logCategory);
    }
}
