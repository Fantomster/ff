<?php

namespace common\components;

use common\models\Order;
use common\models\OrderContent;
use common\models\Organization;
use Yii;
use yii\base\Component;
use yii\base\ErrorException;

/**
 * Class for E-COM integration methods
 *
 * @author alexey.sergeev
 *
 */
class EComIntegration extends Component {


    public function handleFilesList(String $login, String $pass): void
    {
        $client = Yii::$app->siteApi;
        $object = $client->getList(['user' => ['login' => $login, 'pass' => $pass]]);
        if($object->result->errorCode != 0){
            throw new ErrorException();
        }
        $list = $object->result->list;
        if(is_iterable($list)){
            foreach ($list as $fileName){
                $this->sendDoc($client, $fileName);
            }
        }else{
            $this->sendDoc($client, $list);
        }
    }


    private function getDoc(Object $client, String $fileName): bool
    {

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
