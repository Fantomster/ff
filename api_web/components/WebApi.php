<?php

namespace api_web\components;

use api_web\classes\UserWebApi;
use yii\web\BadRequestHttpException;

/**
 * Class WebApi
 *
 * @package api_web\components
 */
class WebApi
{
    /**
     * @var \common\models\User
     */
    public $user;

    function __construct()
    {
        $this->getUser();
    }

    /**
     * @return \common\models\User
     */
    public function getUser()
    {
        if (empty($this->user)) {
            $userId = \Yii::$app->user->getId();
            if (!empty($userId)) {
                $this->user = \common\models\User::findOne($userId);
            }
        }
        return $this->user;
    }

    /**
     * Check of array $params in $request if not set or empty throw BadRequestHttpException
     * Usage: $this->validateRequest($request, ['service_id', 'id', 'value']);
     *
     * @param       $request
     * @param array $params
     * @throws BadRequestHttpException
     */
    protected function validateRequest($request, $params = [])
    {
        foreach ($params as $param) {
            if (!isset($request[$param]) || empty($request[$param])) {
                throw new BadRequestHttpException('empty_param|' . $param);
            }
        }
    }

    /**
     * @param $orgId
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function validateOrgId($orgId)
    {
        $availableBusinesses = (new UserWebApi())->getUserOrganizationBusinessList('id');
        if (!in_array($orgId, array_keys($availableBusinesses['result']))) {
            throw new BadRequestHttpException('integration.email.bad_organization_id');
        }

        return $orgId;
    }
}
