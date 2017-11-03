<?php
/**
 * Created by PhpStorm.
 * User: MikeN
 * Date: 03.11.2017
 * Time: 14:10
 */
namespace common\components\sms\providers;

class Qtelecom extends \common\components\sms\AbstractProvider
{
    public $sender;    // Имя отправителя
    public $user;      // ваш логин в системе
    public $pass;      // ваш пароль в системе
    public $period;    // период
    public $post_id;   // пост id
    public $hostname;  // host замените на адрес сервера указанный в меню "Поддержка -> протокол HTTP"  без префикса http://
    public $on_ssl;    // 1 - использовать HTTPS соединение, 0 - HTTP
    public $path = '/public/http/';

    /**
     * Отправка сообщения, возвращает ID отправленной СМС
     * @param $message
     * @param $target
     * @return mixed
     */
    public function send($message, $target)
    {
        $result = $this->post_message($message, $target);
        return $this->getId($result);
    }

    /**
     * Разбор ответа, выдергиваем id sms
     * @param $result
     * @return mixed
     * @throws \yii\db\Exception
     */
    private function getId($result)
    {
        $r = $xml = simplexml_load_string($result);
        $array = json_decode(json_encode((array)$r), TRUE);

        if(empty($array)) {
            throw new \yii\db\Exception('Пришел пустой результат, или не удалось распарсить.');
        }

        if(isset($array['errors'])) {
            throw new \yii\db\Exception($array['errors']['error']);
        }

        return $array['result']['sms']['@attributes']['id'];
    }

    /**
     * @param $mes
     * @param $target
     * @return mixed
     */
    private function post_message($mes, $target)
    {
        if (is_array($target)) {
            $target = implode(',', $target);
        }

        $post = [
            'action' => 'post_sms',
            'message' => $mes,
            'sender' => $this->sender,
            'target' => $target,
            'post_id' => $this->post_id,
            'period' => $this->period,
        ];

        return $this->get_post_request($post);
    }

    /**
     *  Функиции ниже взяты из класса предоставленного провайдером
     */


    /**
     * запрос на сервер и получение результата
     * @param $post
     * @return string
     */
    private function get_post_request($post)
    {
        $post['user'] = ($this->user);
        $post['pass'] = ($this->pass);
        $post['CLIENTADR'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
        $post['HTTP_ACCEPT_LANGUAGE'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : false;
        $PostData = http_build_query($post, '', '&');
        $len = strlen($PostData);

        $nn = "\r\n";

        $send = "POST " . $this->path . " HTTP/1.0" . $nn . "Host: " . $this->hostname . "" . $nn . "Content-Type: application/x-www-form-urlencoded" . $nn . "Content-Length: $len" . $nn . "User-Agent: AISMS PHP class" . $nn . $nn . $PostData;
        if (($fp = @fsockopen(($this->on_ssl ? 'ssl://' : '') . $this->hostname, ($this->on_ssl ? '443' : '80'), $errno, $errstr, 30)) !== false) {
            fputs($fp, $send);
            $header = '';

            do {
                $header .= fgets($fp, 4096);
            } while (strpos($header, "\r\n\r\n") === false);

            if (get_magic_quotes_runtime()) {
                $header = $this->decode_header(stripslashes($header));
            } else {
                $header = $this->decode_header($header);
            }

            $body = '';

            while (!feof($fp)) {
                $body .= fread($fp, 8192);
            }

            if (get_magic_quotes_runtime()) {
                $body = $this->decode_body($header, stripslashes($body));
            } else {
                $body = $this->decode_body($header, $body);
            }

            fclose($fp);
            return $body;
        } else {
            return 'Невозможно соединиться с сервером.';
        }
    }

    /**
     * @param $str
     * @return array
     */
    private function decode_header($str)
    {
        $part = preg_split("/\r?\n/", $str, -1, PREG_SPLIT_NO_EMPTY);
        $out = array();
        for ($h = 0; $h < sizeof($part); $h++) {
            if ($h != 0) {
                $pos = strpos($part[$h], ':');
                $k = strtolower(str_replace(' ', '', substr($part[$h], 0, $pos)));
                $v = trim(substr($part[$h], ($pos + 1)));
            } else {
                $k = 'status';
                $v = explode(' ', $part[$h]);
                $v = $v[1];
            }
            if ($k == 'set-cookie') {
                $out['cookies'][] = $v;
            } else
                if ($k == 'content-type') {
                    if (($cs = strpos($v, ';')) !== false) {
                        $out[$k] = substr($v, 0, $cs);
                    } else {
                        $out[$k] = $v;
                    }
                } else {
                    $out[$k] = $v;
                }
        }
        return $out;
    }

    /**
     * @param $info
     * @param $str
     * @param string $eol
     * @return string
     */
    private function decode_body($info, $str, $eol = "\r\n")
    {
        $tmp = $str;
        $add = strlen($eol);
        if (isset($info['transfer-encoding']) && $info['transfer-encoding'] == 'chunked') {
            $str = '';
            do {
                $tmp = ltrim($tmp);
                $pos = strpos($tmp, $eol);
                $len = hexdec(substr($tmp, 0, $pos));
                if (isset($info['content-encoding'])) {
                    $str .= gzinflate(substr($tmp, ($pos + $add + 10), $len));
                } else {
                    $str .= substr($tmp, ($pos + $add), $len);
                }
                $tmp = substr($tmp, ($len + $pos + $add));
                $check = trim($tmp);
            } while (!empty($check));
        } elseif (isset($info['content-encoding'])) {
            $str = gzinflate(substr($tmp, 10));
        }
        return $str;
    }
}