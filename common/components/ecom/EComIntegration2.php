<?php

namespace common\components\ecom;

use common\models\CatalogBaseGoods;
use common\models\EcomIntegrationConfig;
use common\models\EdiOrder;
use common\models\EdiOrderContent;
use common\models\EdiOrganization;
use common\models\Order;
use common\models\OrderContent;
use common\models\Organization;
use Yii;
use yii\base\Component;
use yii\db\Exception;

/**
 * Class for E-COM integration methods
 * @author Silukov Konstantin
 */
class EComIntegration2 extends Component
{

//    const STATUS_NEW = 1;
//    const STATUS_PROCESSING = 2;
//    const STATUS_ERROR = 3;
//    const STATUS_HANDLED = 4;
    /**
     * @var
     */
    public $orgId;
    /**
     * @var array
     */
    public $obConf;
    /**@var ProviderInterface */
    public $provider;
    /**@var RealizationInterface*/
    public $realization;

    /**
     * EComIntegration2 constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [], $obConfig = [])
    {
        $this->obConf = $obConfig;
        parent::__construct($config);
    }

    /**
     *
     */
    public function init(){
        $conf = EcomIntegrationConfig::findOne(['org_id' => $this->orgId]);
        if (!$conf) {
            throw new BadRequestHttpException("Config not set for this vendor");
        }
        $this->setProvider($this->createClass('providers\\', $conf['provider']));
        $this->setRealization($this->createClass('realization\\', $conf['realization']));
    }

    /**
     * @param $dir
     * @param $className
     * @return mixed
     */
    private function createClass($dir, $className)
    {
        $strClassName = 'common\components\ecom\\'. $dir . $className;
        if (array_key_exists($className, $this->obConf)){
            return new $strClassName($this->obConf[$className]);
        }
        return new $strClassName();
    }

    /**
     * @param \common\components\ecom\ProviderInterface $provider
     */
    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param \common\components\ecom\RealizationInterface $realization
     */
    public function setRealization(RealizationInterface $realization)
    {
        $this->realization = $realization;
    }

    /**
     * get distinct organization
     *
     * @return \yii\db\ActiveRecord[]
     * */
    private function getOrganizations()
    {
        return EdiOrganization::find()->where(['and', ['not', ['gln_code' => null]], ['not', ['gln_code' => '']]])
            ->andWhere(['and', ['not', ['login' => null]], ['not', ['login' => '']]])
            ->andWhere(['and', ['not', ['pass' => null]], ['not', ['pass' => '']]])
            ->groupBy('login')->distinct()->all();
    }

    /**
     * Get files list from provider and insert to table
     */
    public function handleFilesList(): void
    {
        $ediOrganizations = $this->getOrganizations();

        if (is_iterable($ediOrganizations)) {
            foreach ($ediOrganizations as $ediOrganization) {
                $login = $ediOrganization['login'];
                $pass = $ediOrganization['pass'];

                try {
                    $objectList = $this->provider->getResponse($login, $pass);
                } catch (\Throwable $e) {
                    Yii::error($e->getMessage());
                    continue;
                }

                if (!empty($objectList)) {
                    $this->provider->insertFilesInQueue($objectList);
                }
            }
        }
    }

    /**
     * Get all files on edi_files_queue table and parse it
     */
    public function handleFilesListQueue(): void
    {
        $rows = $this->realization->getFileList();
        $ediOrganizations = $this->getOrganizations();

        foreach ($ediOrganizations as $ediOrganization) {
            foreach ($rows as $item) {
                $this->realization->getDoc($this->provider->client, $item['name'], $ediOrganization['login'], $ediOrganization['pass'], $item['id']);
            }
        }
    }

    /**
     * @param \common\models\Order        $order
     * @param \common\models\Organization $vendor
     * @param \common\models\Organization $client
     * @param String                      $login
     * @param String                      $pass
     * @param bool                        $done
     * @return bool
     * @throws \yii\base\InvalidArgumentException
     */
    public function sendOrderInfo(Order $order, Organization $vendor, Organization $client, String $login, String $pass, bool $done = false): bool
    {
        $transaction = Yii::$app->db_api->beginTransaction();
        $result = false;
        try {
            $ediOrder = EdiOrder::findOne(['order_id' => $order->id]);
            if (!$ediOrder) {
                Yii::$app->db->createCommand()->insert('edi_order', [
                    'order_id' => $order->id,
                    'lang'     => Yii::$app->language ?? 'ru'
                ])->execute();
            }
            $orderContent = OrderContent::findAll(['order_id' => $order->id]);
            foreach ($orderContent as $one) {
                $catGood = CatalogBaseGoods::findOne(['id' => $one->product_id]);
                if ($catGood) {
                    $ediOrderContent = EdiOrderContent::findOne(['order_content_id' => $one->id]);
                    if (!$ediOrderContent) {
                        Yii::$app->db->createCommand()->insert('edi_order_content', [
                            'order_content_id'     => $one->id,
                            'edi_supplier_article' => $catGood->edi_supplier_article ?? null,
                            'barcode'              => $catGood->barcode ?? null
                        ])->execute();
                    }
                }
            }
            $orderContent = OrderContent::findAll(['order_id' => $order->id]);
            $dateArray = $this->getDateData($order);
            if (!count($orderContent)) {
                Yii::error("Empty order content");
                $transaction->rollback();
                return $result;
            }
            $string = Yii::$app->controller->renderPartial($done ? '@common/views/e_com/order_done' : '@common/views/e_com/create_order', compact('order', 'vendor', 'client', 'dateArray', 'orderContent'));
            $currentDate = date("Ymdhis");
            $fileName = $done ? 'recadv_' : 'order_';
            $remoteFile = $fileName . $currentDate . '_' . $order->id . '.xml';
            $order->edi_order = $remoteFile;
            $order->save();
            foreach ($orderContent as $item) {
                $item->edi_recadv = $remoteFile;
                $item->save();
            }
            $result = $this->provider->sendDoc($string, $remoteFile, $login, $pass);
            $transaction->commit();
        } catch (Exception $e) {
            Yii::error($e);
            $transaction->rollback();
        }
        return $result;
    }


    /**
     * @param String $dateString
     * @return String
     */
    private function formatDate(String $dateString): String
    {
        $date = new \DateTime($dateString);
        return $date->format('Y-m-d');
    }


    /**
     * @param String $dateString
     * @return String
     */
    private function formatTime(String $dateString): String
    {
        $date = new \DateTime($dateString);
        return $date->format('H:i');
    }


    /**
     * @param \common\models\Order $order
     * @return array
     */
    private function getDateData(Order $order): array
    {
        $arr = [];
        $arr['created_at'] = $this->formatDate($order->created_at ?? '');
        $arr['requested_delivery_date'] = $this->formatDate($order->requested_delivery ?? '');
        $arr['requested_delivery_time'] = $this->formatTime($order->requested_delivery ?? '');
        $arr['actual_delivery_date'] = $this->formatDate($order->actual_delivery ?? '');
        $arr['actual_delivery_time'] = $this->formatTime($order->actual_delivery ?? '');
        return $arr;
    }


    /**
     * @param \common\models\Organization $vendor
     * @param String                      $string
     * @param String                      $remoteFile
     * @param String                      $login
     * @param String                      $pass
     * @return bool
     */
    private function sendDoc(Organization $vendor, String $string, String $remoteFile, String $login, String $pass): bool
    {
        $client = Yii::$app->siteApi;
        $obj = $client->sendDoc(['user' => ['login' => $login, 'pass' => $pass], 'fileName' => $remoteFile, 'content' => $string]);
        if (isset($obj) && isset($obj->result->errorCode) && $obj->result->errorCode == 0) {
            return true;
        } else {
            Yii::error("Ecom returns error code");
            return false;
        }
    }

    /**
     * @throws \yii\db\Exception
     */
    public function archiveFiles()
    {
        Yii::$app->db->createCommand()->delete('edi_files_queue', 'updated_at <= DATE_SUB(CURDATE(),INTERVAL 30 DAY) AND updated_at IS NOT NULL')->execute();
    }

}