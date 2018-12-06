<?php

namespace api_web\classes;

use api_web\components\FireBase;
use api_web\components\WebApi;
use common\models\User;
use yii\web\BadRequestHttpException;

class NotificationWebApi extends WebApi
{

    /**
     * @param array $post
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function get(array $post)
    {
        $this->validateRequest($post, ['id']);
        $path = $this->getPath();
        $path['notifications'] = $post['id'];
        /** @var string $r */
        $r = FireBase::getInstance()->get($path);

        if (empty($r)) {
            throw new BadRequestHttpException(\Yii::t('api_web', "Notification not found", ['ru'=>'Уведомление не найдено']));
        }

        return \GuzzleHttp\json_decode($r, 1);
    }

    /**
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function push(array $post)
    {
        $this->validateRequest($post, ['body']);
        $path = $this->getPath();
        $path['notifications'] = $this->generateId();
        FireBase::getInstance()->update($path, ['body' => $post['body']]);
        return ['result' => 1];
    }

    /**
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function pushAnyUser(array $post)
    {
        $this->validateRequest($post, ['body', 'user_id']);

        if (!User::findOne($post['user_id'])) {
            throw new BadRequestHttpException(\Yii::t('api_web', "user_not_found", ['ru'=>'Пользователь не найден']));
        }

        $path = [
            'user'          => $post['user_id'],
            'notifications' => $this->generateId()
        ];

        FireBase::getInstance()->update($path, ['body' => $post['body']]);
        return ['result' => 1];
    }

    /**
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\HttpException
     */
    public function delete(array $post)
    {
        $this->validateRequest($post, ['id']);

        $path = $this->getPath();

        foreach ($post['id'] as $id) {
            $path['notifications'] = $id;
            FireBase::getInstance()->delete($path);
        }

        unset($path['organization']);
        foreach ($post['id'] as $id) {
            $path['notifications'] = $id;
            FireBase::getInstance()->delete($path);
        }

        return ['result' => 1];
    }

    /**
     * @return array
     */
    private function getPath()
    {
        $path = [
            'user'         => $this->user->id,
            'organization' => $this->user->organization->id
        ];

        return $path;
    }

    /**
     * @return string
     */
    private function generateId()
    {
        return uniqid();
    }
}