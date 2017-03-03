<?php

namespace franchise\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Html;

/**
 * Site controller
 */
class FrController extends Controller {


    public function actionPost() {
//        if (!Yii::$app->user->isGuest) {
//            return $this->redirect(["site/index"]);
//        }
        $this->layout = 'main-landing';
        if (Yii::$app->request->post('FIELDS')) {
            $fields = Yii::$app->request->post('FIELDS');
            $cname = Html::encode($fields['name']);
            $cphone = Html::encode($fields['phone']);
            $cemail = Html::encode($fields['email']);

            $lpartner = isset($fields['partner']) ? Html::encode($fields['partner']) : '';
            $lname = Html::encode($fields['lead_name']);
            $response = null;
            if (strlen(trim($cname)) < 2 || strlen(trim($cphone)) < 7 || strlen(trim($cemail)) < 2) {
                die('error');
            }

            $responsible_user_id = 1295688; //id ответственного по сделке, контакту, компании
            $lead_name = $lname; //Название добавляемой сделки
            $lead_status_id = 465726; //id этапа продаж, куда помещать сделку
            $contact_name = $cname; //Название добавляемого контакта
            $contact_phone = $cphone; //Телефон контакта
            $contact_email = $cemail; //Емейл контакта
            $lead_partner = $lpartner; //Тип партнерства
            //АВТОРИЗАЦИЯ
            $user = array(
                'USER_LOGIN' => 'artur@f-keeper.ru', #логин
                'USER_HASH' => '98343695877a420c329e30940df91d71' #Хэш для доступа к API
            );
            $subdomain = 'fkeeper';

            #Формируем ссылку для запроса
            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/auth.php?type=json';
            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            #Устанавливаем необходимые опции для сеанса cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($user));
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
            curl_close($curl);  #Завершаем сеанс cURL
            $Response = json_decode($out, true);

            //ПОЛУЧАЕМ ДАННЫЕ АККАУНТА
            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/accounts/current'; #$subdomain уже объявляли выше
            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            //Устанавливаем необходимые опции для сеанса cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            $Response = json_decode($out, true);
            $account = $Response['response']['account'];
            //ПОЛУЧАЕМ СУЩЕСТВУЮЩИЕ ПОЛЯ
            $amoAllFields = $account['custom_fields']; //Все поля
            $amoConactsFields = $account['custom_fields']['contacts']; //Поля контактов
            //echo '<b>Поля из амо:</b>'; echo '<pre>'; print_r($amoConactsFields); echo '</pre>';
            //ФОРМИРУЕМ МАССИВ С ЗАПОЛНЕННЫМИ ПОЛЯМИ КОНТАКТА
            //Стандартные поля амо:
            $sFields = array_flip(array(
                'PHONE', //Телефон. Варианты: WORK, WORKDD, MOB, FAX, HOME, OTHER
                'EMAIL' //Email. Варианты: WORK, PRIV, OTHER
                    )
            );
            //Проставляем id этих полей из базы амо
            foreach ($amoConactsFields as $afield) {
                if (isset($sFields[$afield['code']])) {
                    $sFields[$afield['code']] = $afield['id'];
                }
            }

            //// Проверка на уже существующий контакт

            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/contacts/list?query=' . $contact_phone . '&query=' . $contact_email;
            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            //Устанавливаем необходимые опции для сеанса cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

            $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            $this->CheckCurlResponse($code);
            if ($out) {
            //die('Контакт с таким телефоном уже существует в amoCRM');
                die('errorPhone');
            }
            
            //ДОБАВЛЯЕМ СДЕЛКУ
            $roistatData = array(
                'roistat' => isset($_COOKIE['roistat_visit']) ? $_COOKIE['roistat_visit'] : null,
                'key' => 'MTMyMjU6MzQwNjY6Mzk0MDdmZjFmMDljMDQ3N2Y3Mjc1Yzk1MTg4ZWNjYTk=', // Замените SECRET_KEY на секретный ключ из пункта меню Каталог интеграций -> Ваша CRM -> Настройки -> в нижней части экрана и строчке Ключ для интеграций
                'title' => $lname,
                //'comment' => 'Комментарий к сделке',
                'name' => $cname,
                'email' => $cemail,
                'phone' => $cphone,
                'is_need_callback' => '0', // Для автоматического использования обратного звонка при отправке контакта и сделки нужно поменять 0 на 1
                'fields' => array(
                    // Массив дополнительных полей, если нужны, или просто пустой массив. Более подробно про работу доп. полей можно посмотреть в видео в начале статьи
                    // Примеры использования:
                    "price" => 0, // Поле бюджет в amoCRM
                    "responsible_user_id" => $responsible_user_id, // Ответственный по сделке
                    '85130' => $lpartner, // Заполнение доп. поля 
                    "status_id" => $lead_status_id, // Создавать лид с определенным статусом в определенной воронке. Указывать необходимо ID статуса.
                //"charset" => "Windows-1251", // Сервер преобразует значения полей из указанной кодировки в UTF-8
                //"tags" => "Тег1, Тег2", // Название тегов через запятую
                ),
            );
            $this->send_form("https://cloud.roistat.com/api/proxy/1.0/leads/add?" . http_build_query($roistatData));
            die('success');
        }
        return $this->render('index');
    }

    private function CheckCurlResponse($code) {
        $code = (int) $code;
        $errors = array(
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable'
        );
        try {
            #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
            if ($code != 200 && $code != 204)
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
        } catch (Exception $E) {
            //die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
            die('error');
        }
    }

    private function send_form($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Устанавливаем параметр, чтобы curl возвращал данные, вместо того, чтобы выводить их в браузер.
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        die('success');
    }

}
