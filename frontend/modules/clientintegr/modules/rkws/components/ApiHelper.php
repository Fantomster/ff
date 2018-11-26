<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\RkAccess;
use yii;
use api\common\models\RkSession;
use XMLReader;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use common\models\User;
use api\common\models\RkTasks;

class ApiHelper
{

    const logCategory = "rkws_log";
    
    protected static function log($message) {
        \Yii::info($message, self::logCategory);
    }
    
    public static function sendCurl($xml, $restr)
    {
        $objectinfo = [];
        $respcode   = [];

        // $url = "http://ws.ucs.ru/WSClient/api/Client/Cmd";

        $url = Yii::$app->params['rkeepCmdURL'] ? Yii::$app->params['rkeepCmdURL'] : 'http://ws.ucs.ru/WSClient/api/Client/Cmd';

        $sess = RkSession::find()->andwhere('acc= :acc', [':acc' => 1])->andwhere('status=1')->one(); // Ищем активную сессию

        if (!$sess) {
            echo "SendCurl. Session is not found :((";
            // !! Потом переписать эту дебажную хрень по всему файлу на DebugHelper
            // wtf?                   
//        try{
//            @file_put_contents('runtime/logs/rk.log',PHP_EOL.'2222'.PHP_EOL,FILE_APPEND);
//        }catch (yii\base\Exception $e){
//            Yii::error($e->getMessage());
//        }
            exit;
        }

        $cook = $sess->cook; // Достаем куки активной сессии

        if (empty($cook)) {
            echo "SendCurl.Session is not found :(";
            //wtf?
//            try {
//                file_put_contents('runtime/logs/rk.log', PHP_EOL . '33333' . PHP_EOL, FILE_APPEND);
//            } catch (yii\base\Exception $e) {
//                Yii::error($e->getMessage());
//            }
            exit;
        }

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
        // curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_COOKIE, ".ASPXAUTH=" . $cook . ";");

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        //  curl_setopt($ch, CURLOPT_STDERR,$fp);

        $data = curl_exec($ch);
        $info = curl_getinfo($ch);

        self::log('**************');
        self::log(date("Y-m-d H:i:s"));
        self::log('**************');
        self::log(print_r($xml, true));
        self::log(print_r($url, true));
        self::log(print_r($data, true));
        self::log('**************');
        self::log(print_r($info, true));
        self::log('^^^^^^^^^^^^^^');

        $myXML = simplexml_load_string($data);

        if (!isset($myXML->OBJECTS)) {

            // echo "&&&&&&&&&&&&&<br>";
            // var_dump($data,true);

            foreach ($myXML->OBJECTINFO as $obj) {

                foreach ($obj->attributes() as $a => $b) {
                    $objectinfo[$a] = strval($b[0]);
                }
            }

            if (!empty($objectinfo)) {

                $respcode['taskguid'] = strval($myXML['taskguid']);
                $respcode['code']     = strval($myXML['code']);
                $respcode['version']  = strval($myXML['version']);
            } else {

                if (isset($myXML->Error)) {
                    $objectinfo = ['Статус' => 'Ошибка'];

                    foreach ($myXML->Error->attributes() as $a => $b) {
                        $respcode[$a] = strval($b[0]);
                    }
                } else {

                    $objectinfo['taskguid'] = strval($myXML['taskguid']);
                    $objectinfo['code']     = strval($myXML['code']);
                    $objectinfo['version']  = strval($myXML['version']);

                    $respcode = $objectinfo;
                }
            }
        } else { // Запрос о списке объектов
            $rcount = 0;
            foreach ($myXML->OBJECTS->OBJECT as $obj) {

                $rcount++;
                foreach ($obj->attributes() as $c => $d) {
                    $objectinfo[$rcount][$c] = strval($d[0]);
                }
            }

            $respcode['taskguid'] = strval($myXML['taskguid']);
            $respcode['code']     = strval($myXML['code']);
            $respcode['version']  = strval($myXML['version']);
        }

        if (curl_errno($ch))
            print curl_error($ch);
        else
            curl_close($ch);

        return ['resp' => $objectinfo, 'respcode' => $respcode];
    }

}
