<?php

namespace common\components;

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

}
