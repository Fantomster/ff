<?php

namespace common\components;

use Yii;
use yii\base\Component;

/**
 * Класс для записи сделок в Amo CRM
 *
 * @author elbabuino
 * 
 * @property string $url
 * @property string $email
 * @property string $hash
 */
class AmoCRM extends Component {

    public $url;
    public $email;
    public $hash;

    /**
     * @throws Exception
     */
    public function init() {
        if (empty($this->url) || empty($this->email) || empty($this->hash)) {
            throw new Exception('Missing AmoCRM settings');
        }
    }

    /**
     * 
     * @param integer $pipelineId
     * @param integer $responsibleUserId
     * @param string $leadName
     * @param array $contactFields ['name' => '', 'company_name' => '', 'email' => '', 'phone' => '', 'city' => '']
     */
    public function send($pipelineId, $responsibleUserId, $leadName, $contactFields) {
        //АВТОРИЗАЦИЯ
        $user = [
            'USER_LOGIN' => $this->email, #логин
            'USER_HASH' => $this->hash #Хэш для доступа к API
        ];
        //ПОЛУЧАЕМ ДАННЫЕ АККАУНТА
        $link = $this->url . '/private/api/v2/json/accounts/current?' . http_build_query($user); #$subdomain уже объявляли выше
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
        $response = json_decode($out, true);
        $account = $response['response']['account'];
        //ПОЛУЧАЕМ СУЩЕСТВУЮЩИЕ ПОЛЯ
        $amoContactsFields = $account['custom_fields']['contacts']; //Поля контактов
        //ФОРМИРУЕМ МАССИВ С ЗАПОЛНЕННЫМИ ПОЛЯМИ КОНТАКТА
        //Стандартные поля амо:
        $sFields = array_flip([
            'PHONE', //Телефон. Варианты: WORK, WORKDD, MOB, FAX, HOME, OTHER
            'EMAIL', //Email. Варианты: WORK, PRIV, OTHER
                ]
        );
        //Проставляем id этих полей из базы амо
        foreach ($amoContactsFields as $afield) {
            if (isset($sFields[$afield['code']])) {
                $sFields[$afield['code']] = $afield['id'];
            }
        }

        //ДОБАВЛЯЕМ СДЕЛКУ
        $leads['request']['leads']['add'] = [
            [
                'name' => $leadName,
                'pipeline_id' => $pipelineId,
                'responsible_user_id' => $responsibleUserId, //id ответственного по сделке
            ]
        ];
        $link = $this->url . '/private/api/v2/json/leads/set?' . http_build_query($user);
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

        $response = json_decode($out, true);

        if (is_array($response['response']['leads']['add']))
            foreach ($response['response']['leads']['add'] as $lead) {
                $lead_id = $lead["id"]; //id новой сделки
            };
        //ДОБАВЛЯЕМ СДЕЛКУ - КОНЕЦ
        //ДОБАВЛЕНИЕ КОНТАКТА
        $contact = array(
            'name' => $contactFields['name'],
            'linked_leads_id' => array($lead_id), //id сделки
            'responsible_user_id' => $responsibleUserId, //id ответственного
            'company_name' => $contactFields['company_name'],
            'custom_fields' => [
                [
                    'id' => $sFields['PHONE'],
                    'values' => [
                        [
                            'value' => $contactFields['phone'],
                            'enum' => 'MOB'
                        ]
                    ]
                ],
                [
                    'id' => $sFields['EMAIL'],
                    'values' => [
                        [
                            'value' => $contactFields['email'],
                            'enum' => 'WORK'
                        ]
                    ]
                ],
                [
                    'id' => 105128,
                    'values' => [
                        [
                            'value' => $contactFields['city'],
                            'type_id' => 1,
                            'multiple' => 'N',
                        ]
                    ]
                ]
            ]
        );
        $set['request']['contacts']['add'][] = $contact;
        #Формируем ссылку для запроса
        $link = $this->url . '/private/api/v2/json/contacts/set?' . http_build_query($user);
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
        if (($code == 200) || ($code = 204)) {
            return true;
        }
        return false;
    }

}
