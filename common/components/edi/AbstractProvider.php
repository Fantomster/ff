<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:08 PM
 */

namespace common\components\edi;

use common\models\edi\EdiProvider;
use yii\db\Expression;
use Yii;

abstract class AbstractProvider
{

    const STATUS_NEW = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_ERROR = 3;
    const STATUS_HANDLED = 4;

    /**@var RealizationInterface|AbstractRealization */
    public $realization;

    /**
     * Подключение к провайдеру, подключать в __construct
     *
     * @var
     */
    private $client;
    private $isDebug = false;

    /**
     * Получение файла от провадера
     *
     * @param $item
     */
    public function getFile($item)
    {
        $this->realization->file = '';
    }

    protected function getProviderID($className)
    {
        $pos = strrpos($className, '\\');
        $class = substr($className, $pos + 1);
        $provider = EdiProvider::findOne(['provider_class' => $class]);
        return $provider->id;
    }

    /**
     * Разбор файла
     */
    public function parseFile($content)
    {
        $this->realization->parseFile($content);
    }

    /**
     * Отправка файла на сервер EDI
     */
    public function sendOrderInfo($order, $done)
    {
        $file = $this->realization->getSendingOrderContent();
        //sending order
    }

    public function updateQueue(int $ediFilesQueueID, int $status, String $errorText = '', String $jsonData = ''): void
    {
        if (!$this->isDebug) {
            Yii::$app->db->createCommand()->update('edi_files_queue', ['updated_at' => new Expression('NOW()'), 'status' => $status, 'error_text' => $errorText, 'json_data' => $jsonData], 'id=' . $ediFilesQueueID)->execute();
        }
    }

    /**
     * @return array
     */
    public function getFilesList($orgID): array
    {
        return (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('edi_files_queue')
            ->where(['status' => [AbstractRealization::STATUS_NEW, AbstractRealization::STATUS_ERROR]])
            ->andWhere(['organization_id' => $orgID])
            ->all();
    }

    /**
     * @param array $list
     * @throws \yii\db\Exception
     */
    public function insertFilesInQueue(array $list, $orgID)
    {
        $batch = [];
        $files = (new \yii\db\Query())
            ->select(['name'])
            ->from('edi_files_queue')
            ->where(['name' => $list])
            ->indexBy('name')
            ->all();

        foreach ($list as $name) {
            if (!array_key_exists($name, $files)) {
                if (strpos($name, 'rder_') || strpos($name, 'ecadv_')) continue;
                $batch[] = ['name' => $name, 'organization_id' => $orgID];
            }
        }

        if (!empty($batch)) {
            \Yii::$app->db->createCommand()->batchInsert('edi_files_queue', ['name', 'organization_id'], $batch)->execute();
        }
    }
}