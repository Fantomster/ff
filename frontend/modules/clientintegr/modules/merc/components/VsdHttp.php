<?php

namespace frontend\modules\clientintegr\modules\merc\components;

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
class VsdHttp extends \yii\base\Component
{

    public $authLink;
    public $vsdLink;
    public $pdfLink;
    public $chooseFirmLink;
    public $username;
    public $password;
    public $firmGuid;
    private $sessionName = 'vsd-http-cookie';

    public function getVsdNumberByUuid($uuid)
    {
        $step = $this->getPage($this->vsdLink . $uuid, true);
        $data = \darkdrim\simplehtmldom\SimpleHTMLDom::str_get_html($step['content']);
        $rows = $data->find('.profile-info-row');
        foreach ($rows as $row) {
            $itemName = $row->find('.profile-info-name')[0];
            if ($itemName->innertext == 'Номер ВСД') {
                return $row->find('.profile-info-value')[0]->find('span')[0]->innertext;
            }
        }
        return 0;
    }

    public function getPdfData($uuid)
    {
        $this->getCookie();
        $vsdNumber = $this->getVsdNumberByUuid($uuid);
        $step = $this->getPage($this->pdfLink . $vsdNumber, true, \Yii::$app->session[$this->sessionName]);
        $data = $step['content'];
        header('Cache-Control: public');
        header('Content-type: application/pdf');
        return $data;
    }

    private function getCookie()
    {
        if (!isset(\Yii::$app->session[$this->sessionName])) {
            return $this->auth();
        }
        return ['success' => true];
    }

    public function checkAuthData()
    {
        return $this->getCookie();
    }

    private function auth()
    {
        //TODO: переписать нахуй все
        $step0 = $this->getPage($this->authLink, false);

        $step1 = $this->getPage($step0['redirect_url'], true, $step0['cookies']);
        
        $client = new \GuzzleHttp\Client();
        $res0 = $client->request('GET', $this->authLink, ['decode_content' => false]);

        $srv_id = explode(";", $step0['cookies'])[0] . ";";

        //$headers = $res0->getHeaders();
        $body = (string) $res0->getBody();
//        $data = \darkdrim\simplehtmldom\SimpleHTMLDom::str_get_html($step1['content']);
        $data = \darkdrim\simplehtmldom\SimpleHTMLDom::str_get_html($body);

        $forms = $data->find('form');

        $inputs = [];

        $action = $forms[0]->action;
        foreach ($forms[0]->find('input') as $input) {
            $inputs[$input->name] = $input->value;
        }

        $step2 = $this->postForm($action, true, $inputs, $step1['cookies']);

        $authData = ['j_username' => $this->username, 'j_password' => $this->password, '_eventId_proceed' => ''];

        $step3 = $this->postForm($step2['url'], false, $authData, $srv_id . $step2['cookies']);

        if ($step3['content'] === '') {
            return ['success' => false, 'error' => 'Неверные данные для авторизации'];
        }

        $data2 = \darkdrim\simplehtmldom\SimpleHTMLDom::str_get_html($step3['content']);

        $forms2 = $data2->find("form");

        $action2 = html_entity_decode($forms2[0]->action);

        $inputs2 = [];

        foreach ($forms2[0]->find('input') as $input) {
            $inputs2[$input->name] = $input->value;
        }

        $step4 = $this->postForm($action2, false, $inputs2, $step0['cookies']);

        $step5 = $this->getPage($step4['redirect_url'], true, $step4['cookies']);

        $step6 = $this->getPage($this->chooseFirmLink . $this->firmGuid, true, $step4['cookies']);

        \Yii::$app->session[$this->sessionName] = $step4['cookies'];

        return ['success' => true];
    }

    private function getPage($url, $follow, $cookiesIn = '')
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => true, //return headers in addition to content
            CURLOPT_FOLLOWLOCATION => $follow, // follow redirects
            CURLOPT_ENCODING => "identity", // handle all encodings
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
            CURLINFO_HEADER_OUT => true,
            CURLOPT_SSL_VERIFYPEER => true, // Validate SSL Certificates
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_COOKIE => $cookiesIn,
            CURLOPT_HTTPHEADER => ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:63.0) Gecko/20100101 Firefox/63.0'],
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

    private function postForm($url, $follow = false, $vars = [], $cookiesIn = '')
    {

        $options = [
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => true, //return headers in addition to content
            CURLOPT_FOLLOWLOCATION => $follow, // follow redirects
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
