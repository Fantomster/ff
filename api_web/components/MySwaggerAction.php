<?php

namespace api_web\components;

use Yii;
use yii\web\Response;
use light\swagger\SwaggerAction;

class MySwaggerAction extends SwaggerAction
{
    /**
     * Переопределии метод для Swagger, чтобы показать свою view
     * @return string
     */
    public function run()
    {
        Yii::$app->getResponse()->format = Response::FORMAT_HTML;
        $this->controller->layout = false;
        $view = $this->controller->getView();

        if (empty($this->oauthConfiguration)) {
            $this->oauthConfiguration = [
                'clientId' => 'your-client-id',
                'clientSecret' => 'your-client-secret-if-required',
                'realm' => 'your-realms',
                'appName' => 'your-app-name',
                'scopeSeparator' => ' ',
                'additionalQueryStringParams' => [],
            ];
        }

        return $view->render('swagger', [
            'rest_url' => $this->restUrl,
            'oauthConfig' => $this->oauthConfiguration,
        ], $this->controller);
    }
}
