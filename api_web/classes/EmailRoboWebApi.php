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
        $models = IntegrationSettingFromEmail::find()->select(['organization_id', 'user', 'is_active', 'integration_setting_from_email.updated_at'])
            ->joinWith('organization')
            ->where(['organization_id' => $this->user->organization_id])->all();

        return ['result' => $models];
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
        $model = IntegrationSettingFromEmail::findOne($post['id']);
        if (!$model) {
            return ['result' => $model];
        }
        $model->password = str_pad('', strlen($model->password), '*');
        return ['result' => $model];
    }

    /**
     * Изменение настроек робота
     *
     * @param array $post
     * @return array
     * @throws \yii\web\BadRequestHttpException|ValidationException
     */
    public function update(array $post): array
    {
        $this->validateRequest($post, ['id']);
        $model = IntegrationSettingFromEmail::findOne($post['id']);
        try {
            foreach ($post as $key => $field) {
                if ($key != 'id') {
                    $model->setAttribute($key, $field);
                }
            }
        } catch (\Throwable $t) {
            return ['error' => $t->getMessage()];
        }

        if (!$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }

        return ['result' => $model];
    }

    /**
     * Добавление робота
     *
     * @param array $post
     * @return array
     * @throws ValidationException
     */
    public function add(array $post): array
    {
        $model = new IntegrationSettingFromEmail();
        try {
            foreach ($post as $key => $field) {
                if ($key != 'id') {
                    $model->{$key} = $field ?? null;
                }
            }
        } catch (\Throwable $t) {
            return ['error' => $t->getMessage()];
        }

        if (!$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }

        return ['result' => $model];
    }

    /**
     * Удаление робота
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function delete(array $post): array
    {
        $this->validateRequest($post, ['id']);
        $model = IntegrationSettingFromEmail::findOne($post['id']);
        try {
            $model->delete();
        } catch (\Throwable $t) {
            return ['error' => $t->getMessage()];
        }

        return ['result' => true];
    }
}