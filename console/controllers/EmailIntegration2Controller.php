<?php

namespace console\controllers;

use common\components\ParserTorg12;
use common\models\IntegrationInvoice;
use common\models\IntegrationSettingFromEmail;
use console\helpers\VendorEmailWaybillsHelper;
use Eden\Mail\Imap;
use Eden\Mail\Pop3;
use golovchanskiy\parseTorg12\exceptions\ParseTorg12Exception;
use yii\base\Exception;
use yii\base\Module;
use yii\console\Controller;

/**
 * Class EmailIntegration2Controller
 *
 * @package console\controllers
 */
class EmailIntegration2Controller extends Controller
{

    /**
     * @var Pop3 $connect
     */
    private $connect;
    /**
     * @var array
     */
    private $log = [];
    /**
     * @var
     */
    public $setting_id;

    /**
     * @var VendorEmailWaybillsHelper
     */
    public $helper;

    /**
     * EmailIntegration2Controller constructor.
     *
     * @param string $id
     * @param Module $module
     * @param array  $config
     */
    public function __construct(string $id, Module $module, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->helper = new VendorEmailWaybillsHelper();
    }

    /**
     * @param string $actionID
     * @return array|string[]
     */
    public function options($actionID)
    {
        return ['setting_id'];
    }

    /**
     * @return array
     */
    public function optionAliases()
    {
        return ['sid' => 'setting_id'];
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed            $result
     * @return mixed
     */
    public function afterAction($action, $result)
    {
        if ($action->id == 'index') {
            echo implode(PHP_EOL, $this->log);
        }
        return parent::afterAction($action, $result);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function actionTest()
    {
        $temp_file[68] = '/app/console/runtime/testnac65.xls';

        $i = 1;

        foreach ($temp_file as $filet) {

            $parser = new ParserTorg12($filet);
            try {
                $parser->parse();
            } catch (ParseTorg12Exception $e) {
                exit('ERROR PARSING TORG12 FILE' . $e->getMessage());
            }

            if (empty($parser->invoice->rows)) {
                exit('Error: empty rows ');
            }

            //Данные необходимые для сохранения в базу
            $result[] = [
                'invoice' => \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($parser->invoice), true),
            ];

            echo $filet . PHP_EOL;
            print_r("Result date:" . $result[$i - 1]['invoice']['date'] . PHP_EOL);
            print_r("Result number:" . $result[$i - 1]['invoice']['number'] . PHP_EOL);
            print_r("Result name:" . $result[$i - 1]['invoice']['namePostav'] . PHP_EOL);
            print_r("Result inn:" . $result[$i - 1]['invoice']['innPostav'] . PHP_EOL);
            print_r("Result kpp:" . $result[$i - 1]['invoice']['kppPostav'] . PHP_EOL);
            print_r("Result consignee:" . $result[$i - 1]['invoice']['nameConsignee'] . PHP_EOL);
            print_r("Result price_without_tax_sum:" . $result[$i - 1]['invoice']['price_without_tax_sum'] . PHP_EOL);
            print_r("Result price_with_tax_sum:" . $result[$i - 1]['invoice']['price_with_tax_sum'] . PHP_EOL);
            print_r("=================================" . PHP_EOL);
            $i++;
        }
    }

    /**
     * @throws \Exception
     */
    public function actionIndex()
    {
        /**
         * @var IntegrationSettingFromEmail $setting
         */
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
        //Получаем все активные настройки или конкретную настройку
        $where    = (isset($this->setting_id) ? ['id' => $this->setting_id] : ['is_active' => 1]);
        $settings = IntegrationSettingFromEmail::find()->where($where)
            ->andWhere(['version' => 2])->all();
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
                $this->helper->addLog('SETTING IS DISABLED! For user ' . $setting->user, 'auth');
                continue;
            }
            $this->helper->orgId = $setting->organization_id;

            try {
                //Подключаемся
                $this->connect($setting);
                //Получаем последние 20 емайлов
                $emails = $this->getEmails(0, 20);
                foreach ($emails as $email) {
                    //Пропускаем емайлы без вложений
                    if (empty($email['attachment'])) {
                        continue;
                    }
                    //Получаем только подходящие нам вложения из е-мэйла
                    if ($files = $this->getAttachments($email, $setting)) {
                        foreach ($files as $file) {
                            $transaction = \Yii::$app->db->beginTransaction();
                            try {
                                $this->helper->processFile($file);
                                $transaction->commit();
                            } catch (\Exception $e) {
                                $transaction->rollBack();
                                $this->helper->addLog($e->getMessage() . ' FILE:' . $e->getFile() . ' ROW:' . $e->getLine(), 'parsing');
                            }
                        }
                        $this->log([
                            PHP_EOL . str_pad('', 100, '='),
                            str_pad('END ' . $message_console, 99, ' ') . '|',
                            str_pad('', 100, '=')
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $this->helper->addLog($e->getMessage() . ' FILE:' . $e->getFile() . ' ROW:' . $e->getLine(), 'auth');
            } finally {
                $this->connect->disconnect();
            }
        }
    }

    /**
     * Подключение к серверу
     * @param IntegrationSettingFromEmail $setting
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
                throw new Exception('Не определён тип сервера.');
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
     * @throws \Exception
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
            //print $setting->language.PHP_EOL;
            //Узнаём тип вложения
            $mime_type = array_keys($file)[0];
            //Собираем только разрешённые вложения
            if (!in_array(trim($mime_type), $allow_mime_types)) {
                //echo '- Missed File MIME-TYPE:' . $mime_type . PHP_EOL;
                continue;
            }

            /* $this->log([
              str_pad('', 100, '_'),
              'EMAIL ID: ' . $email['id'],
              'FROM: ' . $email['from']['email'],
              'SUBJECT: ' . $email['subject'] . PHP_EOL
              ]); */

            //Получаем тело файла
            $content      = array_values($file)[0];
            //Декодируем имя файла
            $name_file    = iconv_mime_decode($name_file, 0, "UTF-8");
            //Темповый файл, для прочтения и парсинга
            $temp_file    = \Yii::getAlias('@app') . '/runtime/' . md5($email['id']) . '_' . $name_file;
            //Тело файла в BASE64 для возможности записи в базу
            $file_content = base64_encode($content);
            //Проверяем на всякий темповый файл, если есть, удаляем
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }
            //Проверяем, нет ли уже этой накладной у этой организации
            $model = IntegrationInvoice::findOne([
                        'integration_setting_from_email_id' => $setting->id,
                        'organization_id'                   => $setting->organization_id,
                        'file_hash_summ'                    => md5($file_content),
            ]);
            if (!empty($model)) {
                //$this->log('- File (' . $name_file . ') has previously been processed by the parser `integration_invoice`.`id` = ' . $model->id);
                continue;
            }
            //Сохраняем темп файл
            file_put_contents($temp_file, $content);
            //Загружаем его в парсер
            $parser = new ParserTorg12($temp_file);
            try {
                // запускаем обработку накладной
                $parser->parse();
                if ($parser->sumNotEqual === true) {
                    $parser->sendMailNotEqualSum($email['from']['email'], $name_file, $setting->language);
                }
            } catch (ParseTorg12Exception $e) {
                $this->log([
                    PHP_EOL,
                    'ERROR PARSING TORG12 FILE: ' . $name_file,
                    '--!-- ' . $e->getMessage()
                ]);
                $this->helper->addLog('ERROR PARSING TORG12 FILE: ' . $name_file . ' ' . $e->getMessage(), 'parsing');
                //Удаляем темп файл
                if (file_exists($temp_file)) {
                    unlink($temp_file);
                }
                continue;
            }

            if (empty($parser->invoice->rows)) {
                $this->log([
                    PHP_EOL,
                    'Error: empty rows ' . $name_file
                ]);
                $this->helper->addLog('Error: empty rows ' . $name_file, 'parsing');
                //Удаляем темп файл
                if (file_exists($temp_file)) {
                    unlink($temp_file);
                }
                continue;
            }

            //Данные, необходимые для сохранения в базу
            $result[] = [
                'integration_setting_from_email_id' => $setting->id,
                'organization_id'                   => $setting->organization_id,
                'email_id'                          => $email['id'],
                'file_mime_type'                    => $mime_type,
                'file_content'                      => $file_content,
                'file_hash_summ'                    => md5($file_content),
                'invoice'                           => \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($parser->invoice), true),
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
        try {
            if (is_array($message)) {
                foreach ($message as $m) {
                    $this->log[] = trim($m);
                }
            } else {
                $this->log[] = trim($message);
            }
        } catch (\Throwable $t){
            $this->log($t->getTraceAsString());
        }
    }

}
