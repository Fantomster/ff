<?php

namespace common\components\edi;

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
use yii\db\Expression;

/**
 * Class for E-COM integration methods
 * @author Silukov Konstantin
 */
class EDIIntegration extends Component
{

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
    /**@var RealizationInterface */
    public $realization;

    /**
     * EDIIntegration constructor.
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
    public function init()
    {
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
        $strClassName = 'common\components\edi\\' . $dir . $className;
        if (array_key_exists($className, $this->obConf)) {
            return new $strClassName($this->obConf[$className]);
        }
        return new $strClassName();
    }

    /**
     * @param \common\components\edi\ProviderInterface $provider
     */
    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param \common\components\edi\RealizationInterface $realization
     */
    public function setRealization(RealizationInterface $realization)
    {
        $this->provider->realization = $realization;
    }


    public function handleFilesList()
    {
        $this->provider->handleFilesList($this->orgId);
    }

    public function handleFilesListQueue()
    {
        $items = $this->provider->getFilesList($this->orgId);
        foreach ($items as $item) {
            $content = $this->provider->getFile($item, $this->orgId);
            $this->provider->parseFile($content);
        }
    }


    public function sendOrderInfo($order, $isNewOrder)
    {
        $this->provider->sendOrderInfo($order, $this->orgId, $isNewOrder);
    }
}