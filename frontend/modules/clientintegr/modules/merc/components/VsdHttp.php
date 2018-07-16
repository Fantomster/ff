<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VsdHttp
 *
 * @author elbabuino
 */
class VsdHttp {

    public $authLink = 'https://t2-mercury.vetrf.ru/hs/';
    public $vsdLink = 'https://t2-mercury.vetrf.ru/pub/operatorui?_language=ru&_action=showVetDocumentFormByUuid&uuid=';
    public $pdfLink = 'https://t2-mercury.vetrf.ru/hs/operatorui?printType=1&preview=false&_action=printVetDocumentList&_language=ru&isplayPreview=false&displayRecipient=true&transactionPk=&vetDocument=&batchNumber=&printPk=';
    public $username;
    public $password;
    private $session = 'vsd-http-cookie';

    private function getCookie() {
        if (!isset(Yii::app()->session[$this->session])) {
            
        }
    }

    private function getPage($url, $follow, $cookiesIn = '') {
        $options = array(
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => true, //return headers in addition to content
            CURLOPT_FOLLOWLOCATION => $follow, // follow redirects
            CURLOPT_ENCODING => "", // handle all encodings
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
            CURLINFO_HEADER_OUT => true,
            CURLOPT_SSL_VERIFYPEER => true, // Validate SSL Certificates
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_COOKIE => $cookiesIn,
            CURLOPT_HTTPHEADER => ['User-Agent: Mozilla/5.0 (X11; Ubuntu; Linu…) Gecko/20100101 Firefox/61.0'],
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $rough_content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        $header_content = substr($rough_content, 0, $header['header_size']);
        $body_content = trim(str_replace($header_content, '', $rough_content));
        $pattern = "#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m";
        preg_match_all($pattern, $header_content, $matches);
        $cookiesOut = implode("; ", $matches['cookie']);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['headers'] = $header_content;
        $header['content'] = $body_content;
        $header['cookies'] = $cookiesOut;
        return $header;
    }

    private function postForm($url, $vars = [], $cookiesIn = '') {

        $options = [
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => true, //return headers in addition to content
            CURLOPT_FOLLOWLOCATION => false, // follow redirects
            CURLOPT_POST => true, // handle all encodings
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
            CURLINFO_HEADER_OUT => true,
            CURLOPT_SSL_VERIFYPEER => true, // Validate SSL Certificates
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_COOKIE => $cookiesIn,
            CURLOPT_POSTFIELDS => http_build_query($vars),
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $rough_content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        $header_content = substr($rough_content, 0, $header['header_size']);
        $body_content = trim(str_replace($header_content, '', $rough_content));
        $pattern = "#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m";
        preg_match_all($pattern, $header_content, $matches);
        $cookiesOut = implode("; ", $matches['cookie']);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['headers'] = $header_content;
        $header['content'] = $body_content;
        $header['cookies'] = $cookiesOut;
        return $header;
    }

}
