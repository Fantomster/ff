<?php

namespace api_web\components;

use Yii;
use light\swagger\SwaggerApiAction;

class WebApiSwaggerAction extends SwaggerApiAction
{
    protected function clearCache()
    {
        $clearCache = Yii::$app->getRequest()->get('clear-cache', false);
        if ($clearCache !== false) {
            $this->getCache()->delete($this->cacheKey);
            Yii::$app->response->content = json_encode(['result' => 'Succeed clear swagger api cache.']);
            Yii::$app->end();
        }
    }

    protected function getSwagger()
    {
        try {
            return \Swagger\scan($this->scanDir, $this->scanOptions);
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }
}