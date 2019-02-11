<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 2019-02-08
 * Time: 14:07
 */

namespace api_web\modules\integration\classes\dictionaries;

use api_web\components\WebApi;
use api_web\helpers\BaseHelper;
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\classes\Integration;
use api_web\modules\integration\interfaces\DictionaryInterface;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;
use common\models\vetis\VetisBusinessEntity;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;

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
     * @var BaseHelper
     */
    private $helper;

    /**
     * AbstractDictionary constructor.
     *
     * @param $serviceId
     */
    public function __construct($serviceId)
    {
        parent::__construct();
        $this->service_id = $serviceId;
        $this->helper = new BaseHelper();
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

    /**
     * @param $request
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function getBusinessEntityList($request)
    {
        $reqPag = $request['pagination'] ?? [];
        $page = $this->helper->isSetDef($reqPag['page'] ?? null, 1);
        $pageSize = $this->helper->isSetDef($reqPag['page_size'] ?? null, 12);

        $query = VetisBusinessEntity::find()->select(['fullname', 'uuid', 'guid', 'inn', 'addressView', 'active', 'name'])
            ->where(['active' => 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);
        $result = [];

        /**@var VetisBusinessEntity $model */
        foreach ($dataProvider->models as $model) {
            $result[] = [
                'fullname' => $model->fullname,
                'name'     => $model->name,
                'uuid'     => $model->uuid,
                'guid'     => $model->guid,
                'inn'      => $model->inn,
                'address'  => $model->addressView,
                'active'   => $model->active,
            ];
        }

        $return = [
            'result'     => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }
}
