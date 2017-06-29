<?php
namespace api\modules\v1\modules\supp;

use Yii;
use yii\base\BootstrapInterface;
use yii\web\GroupUrlRule;

/**
 * Bootstrap class registers module and user application component. It also creates some url rules which will be applied
 * when UrlManager.enablePrettyUrl is enabled.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        /** @var Module $module */
        /** @var \yii\db\ActiveRecord $modelName */
        if ($app->hasModule('v1') && ($module = $app->getModule('v1')) instanceof Module) {

            $configUrlRule = [
                   'prefix' => $module->urlPrefix,
                    'rules'  => $module->urlRules,
                ];

                if ($module->urlPrefix != '') {
                    $configUrlRule['routePrefix'] = '';
                }

                $app->urlManager->addRules([new GroupUrlRule($configUrlRule)], false);
                
                echo "hello";

            /*if (!isset($app->get('i18n')->translations['user*'])) {
                $app->get('i18n')->translations['user*'] = [
                    'class'    => PhpMessageSource::className(),
                    'basePath' => __DIR__ . '/messages',
                ];
            }*/
        }
       // echo "hello2";
    }
}
