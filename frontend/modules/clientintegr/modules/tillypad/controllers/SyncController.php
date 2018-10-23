<?php

namespace frontend\modules\clientintegr\modules\tillypad\controllers;

use api_web\modules\integration\modules\tillypad\models\TillypadSync;

class SyncController extends \frontend\modules\clientintegr\modules\iiko\controllers\SyncController
{
    /**
     * Синхронизация всего, по типам
     * @return array
     */
    public function actionRun()
    {
        $id = \Yii::$app->request->post('id');
        try {
            return (new TillypadSync())->run($id);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile(), 'trace' => $e->getTraceAsString()];
        }
    }
}
