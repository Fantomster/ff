<?php

namespace common\components\edi;

use common\models\EcomIntegrationConfig;
use common\models\edi\EdiOrganization;
use yii\base\Component;
use yii\web\BadRequestHttpException;

/**
 * Class for E-COM integration methods
 *
 * @author Silukov Konstantin
 */
class EDIIntegration extends Component
{
    public $orgId;
    public $clientId;
    public $providerID;

    /**
     * @var array
     */
    public $obConf;

    /**@var AbstractProvider|ProviderInterface */
    public $provider;

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
        if ($this->clientId > 0) {
            $ediOrganization = EdiOrganization::findOne(['organization_id' => $this->clientId, 'provider_id' => $this->providerID]);
            if ($ediOrganization && strpos($ediOrganization->ediProvider->provider_class, 'eradata')) {
                $this->orgId = $this->clientId;
            } else {
                $ediOrganization = EdiOrganization::findOne(['organization_id' => $this->orgId, 'provider_id' => $this->providerID]);
            }
        } else {
            $ediOrganization = EdiOrganization::findOne(['organization_id' => $this->orgId, 'provider_id' => $this->providerID]);
        }
        if (!$ediOrganization) {
            throw new BadRequestHttpException("Config not set for this vendor");
        }

        $this->setProvider($this->createClass('providers\\', $ediOrganization->ediProvider->provider_class));
        $this->setRealization($this->createClass('realization\\', $ediOrganization->ediProvider->realization_class));
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

    /**
     * Обработка файлов
     */
    public function handleFilesList()
    {
        $this->provider->handleFilesList($this->orgId, $this->providerID);
    }

    /**
     * Массовая обработка
     */
    public function handleFilesListQueue()
    {
        $items = $this->provider->getFilesList($this->orgId);
        foreach ($items as $item) {
            $content = $this->provider->getFile($item, $this->orgId);
            $this->provider->parseFile($content);
        }
    }

    /**
     * Отправляем информацию о заказе
     *
     * @param $order
     * @param $isNewOrder
     */
    public function sendOrderInfo($order, $isNewOrder)
    {
        $this->provider->sendOrderInfo($order, $this->orgId, $isNewOrder, $this->providerID);
    }
}