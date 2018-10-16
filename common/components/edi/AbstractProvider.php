<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:08 PM
 */

namespace common\components\edi;


use common\models\Catalog;
use common\models\CatalogGoods;
use common\models\CatalogBaseGoods;
use common\models\Currency;
use common\models\EdiOrder;
use common\models\EdiOrderContent;
use common\models\EdiOrganization;
use common\models\Order;
use common\models\OrderContent;
use common\models\OrderStatus;
use common\models\Organization;
use common\models\RelationSuppRest;
use common\models\User;
use frontend\controllers\OrderController;
use yii\db\Expression;
use Yii;

abstract class AbstractProvider
{

    const STATUS_NEW = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_ERROR = 3;
    const STATUS_HANDLED = 4;

    /**
     * Подключение к провайдеру, подключать в __construct
     * @var
     */
    private $client;

    /**
     * Получение файла от провадера
     * @param $item
     */
    public function getFile($item, $orgId)
    {
        $this->realization->file = '';
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
    public function sendOrderInfo($order, $orgId, $done)
    {
        $file = $this->realization->getSendingOrderContent();
        //sending order
    }


    public function updateQueue(int $ediFilesQueueID, int $status, String $errorText): void
    {
        Yii::$app->db->createCommand()->update('edi_files_queue', ['updated_at' => new Expression('NOW()'), 'status' => $status, 'error_text' => $errorText], 'id=' . $ediFilesQueueID)->execute();
    }
}