<?php

namespace common\components;

use common\models\Order;
use common\models\OrderContent;
use common\models\Organization;
use Yii;
use yii\base\Component;

/**
 * Class for E-COM integration methods
 *
 * @author alexey.sergeev
 *
 */
class EComIntegration extends Component {


    public function connect(array $eComParams)
    {
        try{
            $open = ftp_connect($eComParams['host'], $eComParams['port'], $eComParams['timeout']);
            ftp_login($open, $eComParams['login'], $eComParams['password']);
        }catch (ErrorException $e){
            Yii::error("E-COM FTP connection error");
            return null;
        }
        return $open;
    }


    public function handleFilesList($open, array $eComParams): void
    {
        $site = ftp_nlist($open, $eComParams['directory']);
        $d = count($site);
        for ($i = 0; $i < $d; $i++) {
            $localFile = "/tmp/" . time() . rand(9999, 99999999) . '.xml';
            $resource = fopen($localFile, 'w');

            if(ftp_get($open, $localFile, $site[$i], FTP_BINARY)){
                $content = simplexml_load_file($localFile);
                //dd($content);
            }
            fclose($resource);

            try{
                unlink($localFile);
            }catch (ErrorException $e){
                Yii::error('Error delete file with e-com data.');
            }
        }
        ftp_close($open);
    }


    public function sendOrderInfo(Order $order, Organization $vendor, Organization $client): bool
    {
        $orderContent = OrderContent::findAll(['order_id'=>$order->id]);
        $dateArray = $this->getDateData($order);
        $string = Yii::$app->controller->renderPartial('@common/views/e_com/create_order', compact('order', 'vendor', 'client', 'dateArray', 'orderContent'));
        $currentDate = date("Ymdhis");
        $remoteFile = 'order_' . $currentDate . '_' . $order->id . '.xml';
        return $this->sendDoc($string, $remoteFile);
    }


    private function formatDate(String $dateString): String
    {
        $date = new \DateTime($dateString);
        return $date->format('Y-m-d');
    }


    private function formatTime(String $dateString): String
    {
        $date = new \DateTime($dateString);
        return $date->format('H:i');
    }


    private function getDateData(Order $order): array
    {
        $arr = [];
        $arr['created_at'] = $this->formatDate($order->created_at ?? '');
        $arr['requested_delivery_date'] = $this->formatDate($order->requested_delivery ?? '');
        $arr['requested_delivery_time'] = $this->formatTime($order->requested_delivery ?? '');
        $arr['actual_delivery_date'] = $this->formatDate($order->actual_delivery ?? '');
        $arr['actual_delivery_time'] = $this->formatTime($order->actual_delivery ?? '');
        return $arr;
    }


    private function sendDoc(String $string, String $remoteFile): bool
    {
        $client = Yii::$app->siteApi;
        $res = $client->sendDoc(['user' => ['login' => Yii::$app->params['e_com']['login'], 'pass' => Yii::$app->params['e_com']['pass']], 'fileName' => $remoteFile, 'content' => $string]);
        if(isset($res->errorCode) && $res->errorCode == 0){
            return true;
        }
        return false;
    }

}
