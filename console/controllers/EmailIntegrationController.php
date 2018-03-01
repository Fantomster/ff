<?php

namespace console\controllers;

use common\components\ParserTorg12;
use common\models\IntegrationInvoice;
use common\models\IntegrationSettingFromEmail;
use Eden\Mail\Imap;
use Eden\Mail\Pop3;
use golovchanskiy\parseTorg12\exceptions\ParseTorg12Exception;
use yii\base\Exception;
use yii\console\Controller;

class EmailIntegrationController extends Controller
{
    /**
     * @var $connect Pop3
     */
    private $connect;
    private $log = [];
    public $setting_id;

    public function options($actionID)
    {
        return ['setting_id'];
    }

    public function optionAliases()
    {
        return ['sid' => 'setting_id'];
    }

    public function afterAction($action, $result)
    {
        if ($action->id == 'index') {
            echo implode(PHP_EOL, $this->log);
        }
        return parent::afterAction($action, $result);
    }

    public function actionIndex()
    {
        /**
         * @var $setting IntegrationSettingFromEmail
         */
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
        //Получаем все активные настройки или конкретную настройку
        $where = (isset($this->setting_id) ? ['id' => $this->setting_id] : ['is_active' => 1]);
        $settings = IntegrationSettingFromEmail::find()->where($where)->all();
        \Yii::$app->db->createCommand('SET SESSION wait_timeout = 28800;')->execute();
        //Побежали по серверам
        foreach ($settings as $setting) {

            $message_console = 'SETTING: ' . $setting->id . '  ' . 'ORGANIZATION: ' . $setting->organization->id;

            $this->log([
                PHP_EOL . str_pad('', 100, '='),
                str_pad('RUN ' . $message_console, 99, ' ') . '|',
                str_pad('', 100, '=')
            ]);

            if ($setting->is_active == 0) {
                $this->log('SETTING IS DISABLED!');
                continue;
            }

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                //Подключаемся
                $this->connect($setting);
                //Получаем последние 100 емайлов
                $emails = $this->getEmails(0, 20);
                foreach ($emails as $email) {
                    //Пропускаем емайлы без вложений
                    if (empty($email['attachment'])) {
                        continue;
                    }
                    //Получаем только одходящие нам вложения из емайла
                    if ($files = $this->getAttachments($email, $setting)) {
                        foreach ($files as $file) {
                            $this->log('+ CREATED INVOICE: id = ' . (new IntegrationInvoice())->saveInvoice($file) . PHP_EOL);
                        }
                    }
                }
                $this->connect->disconnect();
                $transaction->commit();
                $this->log([
                    PHP_EOL . str_pad('', 100, '='),
                    str_pad('END ' . $message_console, 99, ' ') . '|',
                    str_pad('', 100, '=')
                ]);
            } catch (\Exception $e) {
                $transaction->rollBack();
                $this->log('SETTING_ID:' . $setting->id . ' - ' . $e->getMessage() . ' FILE:' . $e->getFile() . ' ROW:' . $e->getLine());
                \Yii::error($this->log, 'email-integration-error');
            }
        }
    }

    /**
     * Подключение к серверу
     * @param IntegrationSettingFromEmail $setting
     * @return Imap|Pop3
     * @throws Exception
     */
    private function connect(IntegrationSettingFromEmail $setting)
    {
        switch ($setting->server_type) {
            case 'imap':
                $connect = new Imap($setting->server_host, $setting->user, $setting->password, $setting->server_port, $setting->server_ssl);
                $connect->setActiveMailbox('INBOX');
                break;
            case 'pop3':
                $connect = new Pop3($setting->server_host, $setting->user, $setting->password, $setting->server_port, $setting->server_ssl);
                break;
            default:
                throw new Exception('Не определен тип сервера.');
        }
        $this->connect = $connect;
    }

    /**
     * Получим 20 последних сообщений
     * @param int $start
     * @param int $limit
     * @return array
     */
    private function getEmails($start = 0, $limit = 20)
    {
        $messages = [];

        if ($this->connect instanceof Imap) {
            $messages = $this->connect->getEmails($start, $limit, true);
        }

        if ($this->connect instanceof Pop3) {
            $messages = $this->connect->getEmails($start, $limit);
        }

        return $messages;
    }

    /**
     * Получим список вложений, которые не обрабатывали
     * @param array $email
     * @param IntegrationSettingFromEmail $setting
     * @return array|null
     */
    private function getAttachments(array $email, IntegrationSettingFromEmail $setting)
    {
        //Разрешенные типы вложений
        $allow_mime_types = [
            'application/vnd.ms-excel',
            'application/vnd.ms-office',
            'application/vnd-xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/xls',
            'application/x-xls',
            'application/vnd.ms-excel',
            'application/msexcel',
            'application/x-msexcel',
            'application/x-ms-excel',
            'application/x-excel',
            'application/x-dos_ms_excel',
            'application/excel'
        ];

        foreach ($email['attachment'] as $name_file => $file) {
            //Узнаме тип вложения
            $mime_type = array_keys($file)[0];
            //Собираем только разрешенные вложения
            if (!in_array(trim($mime_type), $allow_mime_types)) {
                //echo '- Missed File MIME-TYPE:' . $mime_type . PHP_EOL;
                continue;
            }

            $this->log([
                str_pad('', 100, '_'),
                'EMAIL ID: ' . $email['id'],
                'FROM: ' . $email['from']['email'],
                'SUBJECT: ' . $email['subject'] . PHP_EOL
            ]);

            //Получаем тело файла
            $content = array_values($file)[0];
            //Декодируем имя файла
            $name_file = iconv_mime_decode($name_file, 0, "UTF-8");
            //Темповый файл, для прочтения и парсинга
            $temp_file = \Yii::getAlias('@app') . '/runtime/' . md5($email['id']) . '_' . $name_file;
            //Тело файла в BASE64 для возможности записи в базу
            $file_content = base64_encode($content);
            //Проверяем на всякий темповый файл, если есть, удаляем
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }
            //Проверяем, нет ли уже этой накладной у этой организации
            $model = IntegrationInvoice::findOne([
                'integration_setting_from_email_id' => $setting->id,
                'organization_id' => $setting->organization_id,
                'file_hash_summ' => md5($file_content),
            ]);
            if (!empty($model)) {
                $this->log('- File (' . $name_file . ') has previously been processed by the parser `integration_invoice`.`id` = ' . $model->id);
                continue;
            }
            //Сохраняем темп файл
            file_put_contents($temp_file, $content);
            //Загружаем его в парсер
            $parser = new ParserTorg12($temp_file);
            try {
                // запускаем обработку накладной
                $parser->parse();
            } catch (ParseTorg12Exception $e) {
                $this->log([
                    PHP_EOL,
                    'ERROR PARSING TORG12 FILE: ' . $name_file,
                    '--!-- ' . $e->getMessage()
                ]);
                continue;
            }

            //Данные необходимые для сохранения в базу
            $result[] = [
                'integration_setting_from_email_id' => $setting->id,
                'organization_id' => $setting->organization_id,
                'email_id' => $email['id'],
                'file_mime_type' => $mime_type,
                'file_content' => $file_content,
                'file_hash_summ' => md5($file_content),
                'invoice' => \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($parser->invoice), true),
            ];
            //Удаляем темп файл
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }
        }
        return $result ?? null;
    }

    /**
     * @param $message
     */
    private function log($message)
    {
        if (is_array($message)) {
            foreach ($message as $m) {
                $this->log[] = trim($m);
            }
        } else {
            $this->log[] = trim($message);
        }
    }
}