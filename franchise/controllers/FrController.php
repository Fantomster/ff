<?php

namespace franchise\controllers;

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
                    'Origin' => ['http://fr.f-keeper.dev', 'http://f-keeper.dev', 'https://f-keeper.dev', 'https://fr.f-keeper.dev', 'https://fr.f-keeper.ru',
                        'https://franch.f-keeper.dev','http://franch.f-keeper.dev','https://franch.f-keeper.ru',
                        'http://franch.f-keeper.ru', 'https://tmp.f-keeper.ru',
                        'http://client.f-keeper.dev', 'https://client.f-keeper.dev', 'https://client.f-keeper.ru'],
                    'Access-Control-Request-Method' => ['POST', 'GET', 'HEAD'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 3600,
                    'Access-Control-Allow-Headers' => ['Authorization', 'Origin', 'X-Requested-With', 'Content-Type', 'Accept'],
                ],
            ],
        ];
    }

    public function actionPost() {
        if (Yii::$app->request->post('FIELDS')) {
            $fields = Yii::$app->request->post('FIELDS');
            $sitepage = isset($fields['sitepage']) ? Html::encode($fields['sitepage']) : '';
            $cname = Html::encode($fields['name']);
            $cphone = Html::encode($fields['phone']);
            $cemail = isset($fields['email']) ? Html::encode($fields['email']) : '';
            $city = Html::encode($fields['city']);
            $comment = isset($fields['comment']) ? Html::encode($fields['comment']) : '';
            $type = isset($fields['type']) ? Html::encode($fields['type']) : '';
            $lpartner = isset($fields['partner']) ? Html::encode($fields['partner']) : '';
            $lname = isset($fields['lead_name']) ? Html::encode($fields['lead_name']) : '';
            $response = null;
            if (strlen(trim($cname)) < 2 || strlen(trim($cphone)) < 7) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => 'error'];
            }

            $responsible_user_id = 1515736; //id ответственного по сделке, контакту, компании
            $lead_name = $lname; //Название добавляемой сделки

            $lead_status_id = 643219; //465726; //id этапа продаж, куда помещать сделку

            if ($lpartner == '199894' || $lpartner == '199896') {
                $lead_status_id = 465726; //643219; //id этапа продаж, куда помещать сделку
            }
            
            if (!empty($type) && $type == 'restaurant'){
                $lpartner = '';
                $lead_name = 'Заявка ресторана';
                $responsible_user_id = 1427371;
                $lead_status_id = 465729;
            }
            if($sitepage == "franch"){
                $lead_status_id = 465726;
                $responsible_user_id = 1515736;
            }
            if($sitepage == "fkeeper"){
                if($fields['formtype']==1){
                $lead_status_id = 465726;
                $responsible_user_id = 1515736; 
                    if($lpartner==1){$lead_name = 'fkeeper: Хочет стать партнером 50';}
                    if($lpartner==2){$lead_name = 'fkeeper: Хочет стать партнером 500';}
                    if($lpartner==3){$lead_name = 'fkeeper: Хочет стать партнером 900';}
                }
                if($fields['formtype']==2){
                $lead_status_id = 465729;
                $lpartner = '';
                $lead_name = 'fkeeper: Ресторан';
                $responsible_user_id = 1427371;   
                }
                if($fields['formtype']==3){
                $lead_status_id = 463335;
                $lpartner = '';
                $lead_name = 'fkeeper: Поставщик';
                $responsible_user_id = 1427371;   
                }  
            }
            if($sitepage == "client"){
                $lead_status_id = 465729;
                $responsible_user_id = 1427371;
            }
            $contact_name = $cname; //Название добавляемого контакта
            $contact_phone = $cphone; //Телефон контакта
            $contact_email = $cemail; //Емейл контакта
            $lead_partner = $lpartner; //Тип партнерства
            //АВТОРИЗАЦИЯ
            $user = array(
                'USER_LOGIN' => 'artur@f-keeper.ru', #логин
                'USER_HASH' => '74ed35efba91ce97c029ceb8006b447b' #Хэш для доступа к API
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
            #Устанавливаем необходимые опции для сеанса cURL
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

            //// Проверка на уже существующий контакт
            if($type == 'restaurant' || $sitepage == "client" || $sitepage == "fkeeper"){
            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/contacts/list?query=' . $contact_phone;    
            }else{
            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/contacts/list?query=' . $contact_phone . '&query=' . $contact_email;
            }
            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            #Устанавливаем необходимые опции для сеанса cURL
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
            if (!$this->CheckCurlResponse($code)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => 'error'];
            }
            if ($out) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => 'errorPhone'];
            }
            //ДОБАВЛЯЕМ СДЕЛКУ
            $post = Yii::$app->request->post();
            $roistat_cookie = isset($post['roi']) ? $post['roi'] : "неизвестно";
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
            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/leads/set';
            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            #Устанавливаем необходимые опции для сеанса cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($leads));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../cookie/cookie.txt');
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
            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/contacts/set';
            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            #Устанавливаем необходимые опции для сеанса cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($set));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (!$this->CheckCurlResponse($code)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => 'error'];
            }

            $Response = json_decode($out, true);

            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['result' => 'success'];
        }
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
