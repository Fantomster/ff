<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use common\models\IntegrationSettingFromEmail;
use yii\web\BadRequestHttpException;

/**
 * Class IntegrationSettingsWebApi
 *
 * @package api_web\classes
 */
class EmailRoboWebApi extends WebApi
{
    /**
     * Список роботов
     *
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function list(array $post): array
    {
        $models = IntegrationSettingFromEmail::find()
            ->joinWith('organization')
            ->where(['organization_id' => $this->user->organization_id])->all();
        $arResult = [];

        foreach ($models as $model) {
            $arResult[] = [
                'name'       => $model->organization->name,
                'user'       => $model->user,
                'is_active'  => $model->is_active,
                'updated_at' => $model->updated_at,
            ];
        }

        return ['result' => $arResult];
    }

    /**
     * Получение настроек робота по Id
     *
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function getSetting(array $post): array
    {
        $this->validateRequest($post, ['id']);
        $model = IntegrationSettingFromEmail::findOne(['id' => $post['id'], 'organization_id' => $this->user->organization_id]);
        if (!$model) {
            throw new BadRequestHttpException('integration.email.setting_not_found');
        }
        $model->password = str_pad('', strlen($model->password), '*');
        return ['result' => $model];
    }

    /**
     * Изменение настроек робота
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function update(array $post): array
    {
        $this->validateRequest($post, ['id']);
        $model = IntegrationSettingFromEmail::findOne(['id' => $post['id'], 'organization_id' => $this->user->organization_id]);
        if (!$model) {
            throw new BadRequestHttpException('integration.email.setting_not_found');
        }
        try {
            foreach ($post as $key => $field) {
                if ($key != 'id') {
                    $model->setAttribute($key, $field);
                }
            }
            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
        } catch (\Throwable $t) {
            throw $t;
        }

        return ['result' => $model];
    }

    /**
     * Добавление робота
     *
     * @param array $post
     * @return array
     * @throws \Throwable
     */
    public function add(array $post): array
    {
        $model = new IntegrationSettingFromEmail();
        try {
            foreach ($post as $key => $field) {
                if ($key != 'id') {
                    $model->setAttribute($key, $field);
                }
            }
            $model->setAttribute('organization_id', $this->user->organization_id);
            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
        } catch (\Throwable $t) {
            throw $t;
        }

        return ['result' => $model];
    }

    /**
     * Удаление робота
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function delete(array $post): array
    {
        $this->validateRequest($post, ['id']);
        $model = IntegrationSettingFromEmail::findOne(['id' => $post['id'], 'organization_id' => $this->user->organization_id]);
        if (!$model) {
            throw new BadRequestHttpException('integration.email.setting_not_found');
        }
        try {
            if (!$model->delete()) {
                throw new ValidationException($model->getFirstErrors());
            }
        } catch (\Throwable $t) {
            throw $t;
        }

        return ['result' => true];
    }
}