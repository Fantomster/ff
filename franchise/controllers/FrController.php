<?php

namespace franchise\controllers;

use common\models\AmoFields;
use Yii;
use yii\helpers\Html;
use yii\web\Response;

/**
 * Site controller
 */
class FrController extends \yii\rest\Controller {

    public function behaviors() {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    'Origin' => Yii::$app->params['cors'],
                    'Access-Control-Request-Method' => ['POST', 'GET', 'HEAD'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 3600,
                    'Access-Control-Allow-Headers' => ['Authorization', 'Origin', 'X-Requested-With', 'Content-Type', 'Accept'],
                ],
            ],
        ];
    }

    public function actionPost() {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->post('FIELDS')) {
            $fields = Yii::$app->request->post('FIELDS');
            $sitepage = isset($fields['sitepage']) ? Html::encode($fields['sitepage']) : '';
            $formtype = isset($fields['formtype']) ? Html::encode($fields['formtype']) : '';
            $cname = Html::encode($fields['name']);
            $cphone = Html::encode($fields['phone']);
            $cemail = isset($fields['email']) ? Html::encode($fields['email']) : '';
            $city = isset($fields['city']) ? Html::encode($fields['city']) : '';
            $company_name = isset($fields['company_name']) ? Html::encode($fields['company_name']) : '';
            $comment = isset($fields['comment']) ? Html::encode($fields['comment']) : '';
            $type = isset($fields['type']) ? Html::encode($fields['type']) : '';
            $lpartner = isset($fields['partner']) ? Html::encode($fields['partner']) : '';
            $lname = isset($fields['lead_name']) ? Html::encode($fields['lead_name']) : '';

            if ($sitepage == "gastreet") {
                $result = Yii::$app->mailer->compose('gastreet', compact("cname", "cphone", "cemail", "city"))
                        ->setTo("gastreet2018@mixcart.ru")
                        ->setSubject("Заявка на Гастрит от $cname")
                        ->send();
                if ($result) {
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'error'];
                }
            }

            $response = null;
            if (strlen(trim($cname)) < 2 || strlen(trim($cphone)) < 7) {
                return ['result' => 'error'];
            }

            $responsible_user_id = 1515736; //id ответственного по сделке, контакту, компании
            $lead_name = $lname; //Название добавляемой сделки

            $lead_status_id = 643219; //465726; //id этапа продаж, куда помещать сделку

            if ($lpartner == '199894' || $lpartner == '199896') {
                $lead_status_id = 465726; //643219; //id этапа продаж, куда помещать сделку
            }

            if (!empty($type) && $type == 'restaurant') {
                $lpartner = '';
                $lead_name = Yii::t('app', 'franchise.controllers.rest_request', ['ru'=>'Заявка ресторана']);
                $responsible_user_id = 1427371;
                $lead_status_id = 465729;
            }
            if ($sitepage == "franch") {
                $lead_status_id = 465726;
                $responsible_user_id = 1515736;
            }
            if ($sitepage == "2017") {
                $lead_status_id = 773370;
                $responsible_user_id = 1295688;
            }
            if ($sitepage == "fkeeper") {
                if ($formtype == 1) {
                    $lead_status_id = 465726;
                    if ($lpartner == 1) {
                        $lead_status_id = 643219;
                        $responsible_user_id = 1515736; // Родион
                        $lead_name = 'fkeeper: Хочет стать партнером 50';
                    }
                    if ($lpartner == 2) {
                        $responsible_user_id = 1427371; // Денис
                        $lead_name = 'fkeeper: Хочет стать партнером 500';
                    }
                    if ($lpartner == 3) {
                        $responsible_user_id = 1427371; // Денис
                        $lead_name = 'fkeeper: Хочет стать партнером 900';
                    }
                }
                if ($formtype == 2) {
                    $lead_status_id = 465729;
                    $lpartner = '';
                    $lead_name = 'fkeeper: Ресторан';
                    $responsible_user_id = 1427371; // Денис
                }
            }
            if ($formtype == 3) {
                $lead_status_id = 463335;
                $lpartner = '';
                $lead_name = 'fkeeper: Поставщик';
                $responsible_user_id = 1427371;
            }
        }
        if (!isset($sitepage)) {
            return ['result' => 'error'];
        }
        if ($sitepage == "client") {
            $lead_status_id = 465729;
            $responsible_user_id = 1427371;
        }

        $amoFields = AmoFields::findOne(['amo_field'=>$sitepage]);
        if($amoFields){
            $lead_status_id = $amoFields->pipeline_id;
            $responsible_user_id = $amoFields->responsible_user_id;
        }

        $contact_name = $cname; //Название добавляемого контакта
        $contact_phone = $cphone; //Телефон контакта
        $contact_email = $cemail; //Емейл контакта
        $lead_partner = $lpartner; //Тип партнерства
        
        
        if (!isset(Yii::$app->params['amo'])) {
            return ['result' => 'error'];
        }
        
        //АВТОРИЗАЦИЯ
        $user = array(
            'USER_LOGIN' => Yii::$app->params['amo']['email'], #логин
            'USER_HASH' => Yii::$app->params['amo']['hash'] #Хэш для доступа к API
        );
        $subdomain = 'fkeeper';
        #Формируем ссылку для запроса
        $link = 'https://' . $subdomain . '.amocrm.ru/private/api/auth.php?type=json';
        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
//        curl_setopt($curl, CURLOPT_URL, $link);
//        curl_setopt($curl, CURLOPT_POST, true);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($user));
//        curl_setopt($curl, CURLOPT_HEADER, false);
//        curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../cookie/cookie.txt');
//        curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../cookie/cookie.txt');
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
//        $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
//        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
//        curl_close($curl);  #Завершаем сеанс cURL
//        $Response = json_decode($out, true);

        //ПОЛУЧАЕМ ДАННЫЕ АККАУНТА
        $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/accounts/current?'.http_build_query($user); #$subdomain уже объявляли выше
        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
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
        //ФОРМИРУЕМ МАССИВ С ЗАПОЛНЕННЫМИ ПОЛЯМИ КОНТАКТА
        //Стандартные поля амо:
        $sFields = array_flip(array(
            'PHONE', //Телефон. Варианты: WORK, WORKDD, MOB, FAX, HOME, OTHER
            'EMAIL', //Email. Варианты: WORK, PRIV, OTHER
                )
        );
        //Проставляем id этих полей из базы амо
        foreach ($amoConactsFields as $afield) {
            if (isset($sFields[$afield['code']])) {
                $sFields[$afield['code']] = $afield['id'];
            }
        }

//        //// Проверка на уже существующий контакт - DISABLED FOR GREAT JUSTICE
//        if ($sitepage == "client") {
//            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/contacts/list?query=' . $contact_phone;
//        }
//        if ($type == "restaurant") {
//            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/contacts/list?query=' . $contact_phone;
//        }
//        if ($type != "restaurant" && $sitepage != "client") {
//            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/contacts/list?query=' . $contact_phone . '&query=' . $contact_email;
//        }
//        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
//        #Устанавливаем необходимые опции для сеанса cURL
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
//        curl_setopt($curl, CURLOPT_URL, $link);
//        curl_setopt($curl, CURLOPT_HEADER, false);
//        curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../cookie/cookie.txt');
//        curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../cookie/cookie.txt');
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
//
//        $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
//        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//        curl_close($curl);
//        if (!$this->CheckCurlResponse($code)) {
//            Yii::$app->response->format = Response::FORMAT_JSON;
//            return ['result' => 'error'];
//        }
//        if ($out) {
//            Yii::$app->response->format = Response::FORMAT_JSON;
//            return ['result' => 'errorPhone'];
//        }
        //ДОБАВЛЯЕМ СДЕЛКУ
        $post = Yii::$app->request->post();
        $roistat_cookie = isset($post['roi']) ? $post['roi'] : Yii::t('app', 'franchise.controllers.undefined', ['ru'=>"неизвестно"]);
        $leads['request']['leads']['add'] = array(
            array(
                'name' => $lead_name,
                'pipeline_id' => $lead_status_id,
                'responsible_user_id' => $responsible_user_id, //id ответственного по сделке
                'custom_fields' => [
                    [
                        'id' => 85130, //парнерство
                        'values' => [
                            [
                                'value' => $lead_partner
                            ]
                        ]
                    ],
                    [
                        'id' => 544713, //комментарий
                        'values' => [
                            [
                                'value' => $comment
                            ]
                        ]
                    ],
                    //Добавляем поле roistat
                    [
                        'id' => 78106,
                        'values' => [
                            [
                                'value' => $roistat_cookie
                            ],
                        ],
                    ],
                ]
            )
        );
        $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/leads/set?'.http_build_query($user);
        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($leads));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $Response = json_decode($out, true);

        if (is_array($Response['response']['leads']['add']))
            foreach ($Response['response']['leads']['add'] as $lead) {
                $lead_id = $lead["id"]; //id новой сделки
            };
        //ДОБАВЛЯЕМ СДЕЛКУ - КОНЕЦ
        //ДОБАВЛЕНИЕ КОНТАКТА
        $contact = array(
            'name' => $contact_name,
            'linked_leads_id' => array($lead_id), //id сделки
            'responsible_user_id' => $responsible_user_id, //id ответственного
            'company_name' => $company_name,
            'custom_fields' => array(
                array(
                    'id' => $sFields['PHONE'],
                    'values' => array(
                        array(
                            'value' => $contact_phone,
                            'enum' => 'MOB'
                        )
                    )
                ),
                array(
                    'id' => $sFields['EMAIL'],
                    'values' => array(
                        array(
                            'value' => $contact_email,
                            'enum' => 'WORK'
                        )
                    )
                ),
                array(
                    'id' => 105128,
                    'values' => array(
                        array(
                            'value' => $city,
                            'type_id' => 1,
                            'multiple' => 'N',
                        )
                    )
                )
            )
        );
        $set['request']['contacts']['add'][] = $contact;
        #Формируем ссылку для запроса
        $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/contacts/set?'.http_build_query($user);
        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($set));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (!$this->CheckCurlResponse($code)) {
            return ['result' => 'error'];
        }

        $Response = json_decode($out, true);

        return ['result' => 'success'];
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
            return false;
        }
        return true;
    }

}
