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

    public function actionTest()
    {

        //$temp_file[1] = '/app/console/runtime/testnac.xls';
        //$temp_file[2] = '/app/console/runtime/testnac2.xls';
        //$temp_file[3] = '/app/console/runtime/testnac3.xls';
        //$temp_file[4] = '/app/console/runtime/testnac4.xls';
        //$temp_file[5] = '/app/console/runtime/testnac10.xlsx';
        //$temp_file[6] = '/app/console/runtime/testnac11.xlsx';
        //$temp_file[7] = '/app/console/runtime/testnac12.xls';
        //$temp_file[8] = '/app/console/runtime/testnac13.xlsx';
        //$temp_file[9] = '/app/console/runtime/testnac22.xlsx';
        //$temp_file[10] = '/app/console/runtime/testnac23.xls';
        //$temp_file[11] = '/app/console/runtime/testnac24.xls';
        //$temp_file[12] = '/app/console/runtime/testnac25.xlsx';
        //$temp_file[13] = '/app/console/runtime/testnac26.xlsx';
        //$temp_file[14] = '/app/console/runtime/testnac27.xlsx';
        //$temp_file[15] = '/app/console/runtime/testnac28.xlsx';
        //$temp_file[16] = '/app/console/runtime/ЕКТД 22007.xls';
        //$temp_file[17] = '/app/console/runtime/ЕКТД 22010.xls';
        //$temp_file[18] = '/app/console/runtime/ЕКТД 22015.xls';
        //$temp_file[19] = '/app/console/runtime/ЕКТД 22016.xls';
        //$temp_file[20] = '/app/console/runtime/ЕКТД 22017.xls';
        //$temp_file[21] = '/app/console/runtime/ЕКТД 22018.xls';
        //$temp_file[22] = '/app/console/runtime/ЕКТД 22028.xls';
        //$temp_file[23] = '/app/console/runtime/ЕКТД 22029.xls';
        //$temp_file[24] = '/app/console/runtime/ЕКТД 22030.xls';
        //$temp_file[25] = '/app/console/runtime/ЕКТД 22031.xls';
        //$temp_file[26] = '/app/console/runtime/ЕКТД 22032.xls';
        //$temp_file[27] = '/app/console/runtime/ЕКТД 22033.xls';
        //$temp_file[28] = '/app/console/runtime/ЕКТД 22034.xls';
        //$temp_file[29] = '/app/console/runtime/testnac29.xls'; // накладная, где сумма итоговая не совпадает с суммой по строкам
        //$temp_file[30] = '/app/console/runtime/test0307n12.xlsx';
        //$temp_file[31] = '/app/console/runtime/test0307xlsx.xls';
        //$temp_file[32] = '/app/console/runtime/id7905.xlsx';
        //$temp_file[33] = '/app/console/runtime/testnac30.xlsx';
        //$temp_file[34] = '/app/console/runtime/testnac31.xlsx';
        //$temp_file[35] = '/app/console/runtime/testnac32.xls';
        //$temp_file[36] = '/app/console/runtime/testnac33.xlsx';
        //$temp_file[37] = '/app/console/runtime/testnac34.xls';
        //$temp_file[38] = '/app/console/runtime/testnac35.xls'; // файл Excel 5.0/95, не читается из-за кодировки
        //$temp_file[39] = '/app/console/runtime/testnac36.xls'; // файл Excel 5.0/95, не читается из-за кодировки
        //$temp_file[40] = '/app/console/runtime/testnac37.xls'; // файл Excel 5.0/95, не читается из-за кодировки
        //$temp_file[41] = '/app/console/runtime/testnac38.xlsx';
        //$temp_file[42] = '/app/console/runtime/testnac39.xlsx';
        //$temp_file[43] = '/app/console/runtime/testnac40.xlsx';
        //$temp_file[44] = '/app/console/runtime/testnac41.xls'; // файл Excel 5.0/95, не читается из-за кодировки
        //$temp_file[45] = '/app/console/runtime/testnac42.xlsx';
        //$temp_file[46] = '/app/console/runtime/testnac43.xlsx';
        //$temp_file[47] = '/app/console/runtime/testnac44.xlsx';
        ////$temp_file[48] = '/app/console/runtime/testnac45.xlsx'; //Не парсится в принципе!!! Вся накладная в одной ячейке
        //$temp_file[49] = '/app/console/runtime/testnac46.xls';
        //$temp_file[50] = '/app/console/runtime/testnac47.xls';
        //$temp_file[51] = '/app/console/runtime/testnac48.xls';
        //$temp_file[52] = '/app/console/runtime/testnac49.xls';
        //$temp_file[53] = '/app/console/runtime/testnac50.xls';
        //$temp_file[54] = '/app/console/runtime/testnac51.xls';
        //$temp_file[55] = '/app/console/runtime/testnac52.xls';
        //$temp_file[56] = '/app/console/runtime/testnac53.xls';
        //$temp_file[57] = '/app/console/runtime/testnac54.xlsx';
        //$temp_file[58] = '/app/console/runtime/testnac55.xlsx';
        //$temp_file[59] = '/app/console/runtime/testnac56.xlsx';
        //$temp_file[60] = '/app/console/runtime/testnac57.xlsx';
        //$temp_file[61] = '/app/console/runtime/testnac58.xls';
        //$temp_file[62] = '/app/console/runtime/testnac59.xls';
        //$temp_file[63] = '/app/console/runtime/testnac60.xls';
        //$temp_file[64] = '/app/console/runtime/testnac61.xls';
        //$temp_file[65] = '/app/console/runtime/testnac62.xlsx';
        //$temp_file[66] = '/app/console/runtime/testnac63.xlsx';
        //$temp_file[67] = '/app/console/runtime/testnac64.xlsx';
        //$temp_file[68] = '/app/console/runtime/testnac65.xls';
        //$temp_file[69] = '/app/console/runtime/testnac66.xlsx';
        //$temp_file[70] = '/app/console/runtime/testnac70.XLS';
        //$temp_file[71] = '/app/console/runtime/testnac71.xlsx';
        $temp_file[72] = '/app/console/runtime/testnac72.xlsx';

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
            print_r($result[$i - 1]['invoice']['rows']);
            //file_put_contents('result_'.$i.'.txt', $filet.PHP_EOL,true);
            //file_put_contents('result_'.$i.'.txt', print_r($result[$i-1],true));
            $i++;
        }
    }

    public function actionIndex()
    {
        /**
         * @var $setting IntegrationSettingFromEmail
         */
        ini_set('memory_limit', '384M');
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

        //Получаем все активные настройки или конкретную настройку
        $where = (isset($this->setting_id) ? ['id' => $this->setting_id] : ['is_active' => 1]);
        $settings = IntegrationSettingFromEmail::find()->where($where)
            ->andWhere(['version' => 1])->all();
        \Yii::$app->db->createCommand('SET SESSION wait_timeout = 28800;')->execute();
        //Побежали по серверам
        foreach ($settings as $setting) {

            $message_console = 'SETTING: ' . $setting->id . '  ' . 'ORGANIZATION: ' . $setting->organization->id;

            /*$this->log([
                PHP_EOL . str_pad('', 100, '='),
                str_pad('RUN ' . $message_console, 99, ' ') . '|',
                str_pad('', 100, '=')
            ]);*/

            if ($setting->is_active == 0) {
                $this->log('SETTING ' . $setting->id . ' IS DISABLED! ORGANIZATION: ' . $setting->organization->id . PHP_EOL);
                continue;
            }

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
                    //Получаем только подходящие нам вложения из е-мэйла
                    if ($files = $this->getAttachments($email, $setting)) {
                        foreach ($files as $file) {
                            $transaction = \Yii::$app->db->beginTransaction();
                            try {
                                //$this->log('+ CREATED INVOICE: id = ' . (new IntegrationInvoice())->saveInvoice($file) . PHP_EOL);
                                (new IntegrationInvoice())->saveInvoice($file);
                                $transaction->commit();
                            } catch (\Exception $e) {
                                $transaction->rollBack();
                                $this->log('ERROR_' . $setting->organization->id . ' CREATED INVOICE');
                                $this->log('SETTING_ID:' . $setting->id . ' - ' . $e->getMessage() . ' FILE:' . $e->getFile() . ' ROW:' . $e->getLine() . PHP_EOL);
                            }
                        }
                        /*$this->log([
                            PHP_EOL . str_pad('', 100, '='),
                            str_pad('END ' . $message_console, 99, ' ') . '|',
                            str_pad('', 100, '=')
                        ]);*/
                    }
                }
                $this->connect->disconnect();
            } catch (\Exception $e) {
                $this->log('ERROR_' . $setting->organization->id);
                $this->log('SETTING_ID:' . $setting->id . ' - ' . $e->getMessage() . ' FILE:' . $e->getFile() . ' ROW:' . $e->getLine() . PHP_EOL);
            }
        }
        \Yii::error($this->log, 'email-integration-log');
    }

    public function beforeAction($action)
    {
        $targets = \Yii::$app->getLog()->targets;
        foreach ($targets as $name => $target) {
            $target->enabled = ($name == 'email-integration');
        }
        \Yii::$app->getLog()->targets = $targets;
        \Yii::$app->getLog()->init();

        return parent::beforeAction($action);
    }

    /**
     * Подключение к серверу
     *
     * @param IntegrationSettingFromEmail $setting
     * @return Imap|Pop3
     * @throws Exception
     */
    private function connect(IntegrationSettingFromEmail $setting)
    {
        $password = \Yii::$app->get('encode')->decrypt($setting->password, $setting->user);
        switch ($setting->server_type) {
            case 'imap':
                $connect = new Imap($setting->server_host, $setting->user, $password, $setting->server_port, $setting->server_ssl);
                $connect->setActiveMailbox('INBOX');
                break;
            case 'pop3':
                throw new Exception("pop3 set for organization: {$setting->organization_id} (mail:{$setting->user})");
            //$connect = new Pop3($setting->server_host, $setting->user, $setting->password, $setting->server_port, $setting->server_ssl);
            //break;
            default:
                throw new Exception('Не определён тип сервера.');
        }
        $this->connect = $connect;
    }

    /**
     * Получим 20 последних сообщений
     *
     * @param int $start
     * @param int $limit
     * @return array
     */
    private function getEmails($start = 0, $limit = 20)
    {
        $messages = [];

        if ($this->connect instanceof Imap) {
            $messages = $this->connect->search(["UNSEEN"], $start, $limit, true, true);
        }

//        if ($this->connect instanceof Pop3) {
//            $messages = $this->connect->getEmails($start, $limit);
//        }

        return isset($messages['id']) ? [$messages] : $messages;
    }

    /**
     * Получим список вложений, которые не обрабатывали
     *
     * @param array                       $email
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
            //print $setting->language.PHP_EOL;
            //Узнаём тип вложения
            $mime_type = array_keys($file)[0];
            //Декодируем имя файла
            $name_file = iconv_mime_decode($name_file, 0, "UTF-8");
            $excelExtension = (substr(mb_strtolower($name_file), -4) === ".xls") || (substr(mb_strtolower($name_file), -5) === ".xlsx");
            //Собираем только разрешённые вложения
            if (!(in_array(trim($mime_type), $allow_mime_types) || $excelExtension)) {
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
            $content = array_values($file)[0];
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
        if (is_array($message)) {
            foreach ($message as $m) {
                $this->log[] = trim($m);
            }
        } else {
            $this->log[] = trim($message);
        }
    }

}
