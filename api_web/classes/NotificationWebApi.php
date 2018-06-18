<?php

namespace api_web\classes;

use api_web\components\FireBase;
use api_web\components\WebApi;
use common\models\User;
use yii\web\BadRequestHttpException;

class NotificationWebApi extends WebApi
{

    public function get(array $post)
    {
        if (empty($post['id'])) {
            throw new BadRequestHttpException('Empty id');
        }

        $path = $this->getPath();
        $path['notifications'] = $post['id'];
        $r = FireBase::getInstance()->get($path);

        if (empty($r)) {
            throw new BadRequestHttpException('Notification not found');
        }

        return \GuzzleHttp\json_decode($r, 1);
    }

    public function push(array $post)
    {
        if (empty($post['body'])) {
            throw new BadRequestHttpException('Empty body');
        }

        $path = $this->getPath();
        $path['notifications'] = $this->generateId();
        FireBase::getInstance()->update($path, ['body' => $post['body']]);
        return ['result' => 1];
    }

    public function pushAnyUser(array $post)
    {
        if (empty($post['body'])) {
            throw new BadRequestHttpException('Empty body');
        }

        if (empty($post['user_id'])) {
            throw new BadRequestHttpException('Empty user_id');
        }

        if (!User::findOne($post['user_id'])) {
            throw new BadRequestHttpException('User not found!!!');
        }

        $path = [
            'user' => $post['user_id'],
            'notifications' => $this->generateId()
        ];

        FireBase::getInstance()->update($path, ['body' => $post['body']]);
        return ['result' => 1];
    }

    public function delete(array $post)
    {
        if (empty($post['id'])) {
            throw new BadRequestHttpException('Empty id');
        }

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

    private function getPath()
    {
        $path = [
            'user' => $this->user->id,
            'organization' => $this->user->organization->id
        ];

        return $path;
    }

    private function generateId()
    {
        return uniqid();
    }
}