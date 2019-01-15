<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/18/2018
 * Time: 10:56 AM
 */

namespace api_web\modules\integration\classes;

use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\modules\integration\classes\dictionaries\AbstractDictionary;
use common\models\OuterAgent;
use common\models\OuterAgentNameWaybill;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class Integration
{
    /** @var array */
    public static $service_map = [
        Registry::RK_SERVICE_ID       => 'Rkws',
        Registry::IIKO_SERVICE_ID     => 'Iiko',
        Registry::POSTER_SERVICE_ID   => 'Poster',
        Registry::TILLYPAD_SERVICE_ID => 'Tillypad',
    ];

    /**
     * Integration constructor.
     *
     * @param $serviceId
     * @throws BadRequestHttpException
     */
    public function __construct($serviceId)
    {
        if (empty($serviceId)) {
            throw new BadRequestHttpException('choose_integration_service');
        }
        $this->service_id = $serviceId;
        $this->serviceName = self::$service_map[$serviceId];
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getDict($type)
    {
        $_ = $this->getDictName($type);
        return new $_($this->service_id);
    }

    /**
     * @param $type
     * @return string
     */
    private function getDictName($type)
    {
        $class = "api_web\modules\integration\classes\dictionaries\\" . $this->serviceName . $type;
        if (!class_exists($class)) {
            $class = AbstractDictionary::class;
        }
        return $class;
    }

    /**
     * Check agent name
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public static function checkAgentNameExists($request)
    {
        if (!isset($request['name']) && !empty($request['name'])) {
            throw new BadRequestHttpException('empty_param|name');
        }

        $agents = (new Query())
            ->select('id')
            ->from(OuterAgent::tableName())
            ->where([
                'org_id'     => (new WebApi())->user->organization_id,
                'is_deleted' => 0
            ])
            ->createCommand(\Yii::$app->db_api)
            ->queryColumn();

        $result = OuterAgentNameWaybill::find()
            ->where([
                'name'     => $request['name'],
                'agent_id' => $agents
            ])
            ->exists();

        return ['result' => $result];
    }
}
