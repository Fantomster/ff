<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 2019-02-08
 * Time: 14:07
 */

namespace api_web\modules\integration\classes\dictionaries;

use api_web\components\WebApi;
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\classes\Integration;
use api_web\modules\integration\interfaces\DictionaryInterface;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;

/**
 * Class MercCommonDictionaries
 *
 * @package api_web\modules\integration\classes\dictionaries
 */
class MercDictionary extends WebApi implements DictionaryInterface
{
    /**
     * @var
     */
    public $service_id;

    /**
     * AbstractDictionary constructor.
     *
     * @param $serviceId
     */
    public function __construct($serviceId)
    {
        parent::__construct();
        $this->service_id = $serviceId;
    }

    /**
     * Список справочников
     *
     * @return array
     */
    public function getList()
    {
        $dictionary = OuterDictionary::find()
            ->select('id')
            ->where(['service_id' => (int)$this->service_id])
            ->asArray()
            ->column();

        $models = OrganizationDictionary::find()
            ->where([
                'outer_dic_id' => $dictionary,
                'org_id'       => [1, $this->user->organization_id]
            ])->all();

        $service = Integration::$service_map[$this->service_id] ?? '';

        $return = [];
        /**
         * Статус по умолчанию = "Синхронизация не проводилась"
         */
        $defaultStatusText = OrganizationDictionary::getStatusTextList()[OrganizationDictionary::STATUS_DISABLED];
        foreach ($models as $model) {
            /** @var \common\models\OrganizationDictionary $model */
            $return[] = [
                'id'          => $model->id,
                'name'        => $model->outerDic->name,
                'title'       => \Yii::t('api_web', 'dictionary.' . $model->outerDic->name),
                'count'       => $model->getCount(),
                'status_id'   => $model->status_id ?? 0,
                'status_text' => $model->statusText ?? $defaultStatusText,
                'upload'      => false,
                'prefix'      => Integration::$service_map[$this->service_id] ?? '',
                'created_at'  => WebApiHelper::asDatetime($model->created_at),
                'updated_at'  => WebApiHelper::asDatetime($model->updated_at),
            ];
        }

        return $return;
    }
}
