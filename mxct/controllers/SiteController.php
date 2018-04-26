<?php

namespace mxct\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\AccessRule;

/**
 * Site controller
 */
class SiteController extends Controller {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'error',
                        ],
                        'allow' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex($token =  null) {
        $shortenedUrl = "https://goo.gl/" . $token;
        $expandedUrl = Yii::$app->google->expandUrl($shortenedUrl);
        $parseUrl = parse_url($expandedUrl);
        $host = isset($parseUrl['host']) ? $parseUrl['host'] : '';
        if ($this->endsWith($host, "mixcart.ru") || $this->endsWith($host, "mix-cart.com")) {
            return $this->redirect($expandedUrl);
        } else {
            return $this->redirect(Yii::$app->params['staticUrl'][Yii::$app->language]['home']);
        }
    }

    private function endsWith($string, $test) {
        $strlen = strlen($string);
        $testlen = strlen($test);
        if ($testlen > $strlen)
            return false;
        return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
    }

}
