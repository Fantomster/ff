<?php

namespace common\components;

use common\models\Order;
use common\models\Organization;
use Yii;
use yii\base\Component;


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
        $eComParams = Yii::$app->params['e_com'];
        $open = $this->connect($eComParams);
        $localFile = "/tmp/" . time() . rand(9999, 99999999) . '.xml';
        $resource = fopen($localFile, 'w');
        $createdAt = $this->formateDate($order->created_at ?? '');
        $requestedDeliveryDate = $this->formateDate($order->requested_delivery ?? '');
        $actualDeliveryDate = $this->formateDate($order->actual_delivery ?? '');
        $string = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<ORDER>
<DOCUMENTNAME>220</DOCUMENTNAME>
<NUMBER>1</NUMBER>
<DATE>{$createdAt}</DATE>
<DELIVERYDATE>{$requestedDeliveryDate}</DELIVERYDATE>
<CAMPAIGNNUMBER>test26042018</CAMPAIGNNUMBER>
<CURRENCY>RUB</CURRENCY>    
<DOCTYPE>O</DOCTYPE>
<ORDRTYPE>ORIGINAL</ORDRTYPE>
<EARLIESTDELIVERYDATE>{$requestedDeliveryDate}</EARLIESTDELIVERYDATE>
<LATESTDELIVERYDATE>{$actualDeliveryDate}</LATESTDELIVERYDATE>
<HEAD>
<SUPPLIER>9864232240006</SUPPLIER>
<BUYER>9864232239956</BUYER>
<DELIVERYPLACE>9864232239956</DELIVERYPLACE>
<FINALRECIPIENT>9864232239956</FINALRECIPIENT>
<SENDER>9864232240006</SENDER>
<RECIPIENT>9864232239956</RECIPIENT>
<EDIINTERCHANGEID>1</EDIINTERCHANGEID>
<POSITION>
<POSITIONNUMBER>1</POSITIONNUMBER>
<PRODUCT>4602541000202</PRODUCT>
    <ORDEREDQUANTITY>10.00</ORDEREDQUANTITY>
<QUANTITYOFCUINTU>0.00</QUANTITYOFCUINTU>
<ORDERUNIT>GB</ORDERUNIT>
<ORDERPRICE>50.00</ORDERPRICE>
<PRICEWITHVAT>59.00</PRICEWITHVAT>
<ORDERPRICEBASIS>50.00</ORDERPRICEBASIS><
ORDERPRICEUNIT>GB</ORDERPRICEUNIT>
<VAT>18.00</VAT>
<CHARACTERISTIC>
</CHARACTERISTIC>
    </POSITION>
</HEAD>
</ORDER>

XML;
        //Yii::error(print_r($string));
        fwrite($resource, $string);
        fclose($resource);
        //$resource = fopen($localFile, 'r');
        $remote_file = 'remote.xml';
        ftp_chdir($open, "inbox");

        $ret = ftp_nb_fput($open, $remote_file, $resource, FTP_BINARY);

        // upload a file
        if (ftp_put($open, $remote_file, $localFile, FTP_ASCII)) {
            echo "successfully uploaded $localFile\n";
        } else {
            echo "There was a problem while uploading $localFile\n";
        }

        while ($ret == FTP_MOREDATA) {

            Yii::error(print_r($ret));

            // Continue upload...
            $ret = ftp_nb_continue($open);
        }
        if ($ret != FTP_FINISHED) {
            echo "There was an error uploading the file...";
            exit(1);
        }

        ftp_close($open);
        //fclose($resource);

        return true;
    }


    private function formateDate(String $dateString): String
    {
        $date = new \DateTime($dateString);
        return $date->format('Y-m-d');
    }

}
