<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */


if (Yii::$app->controller->action->id === 'login') {
    /**
     * Do not use this code in your template. Remove it. 
     * Instead, use the code  $this->layout = '//main-login'; in your controller.
     */
    echo $this->render(
            'main-login', ['content' => $content]
    );
} else {

    dmstr\web\AdminLteAsset::register($this);
    franchise\assets\AppAsset::register($this);

    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
        <head>
            <meta charset="<?= Yii::$app->charset ?>"/>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <?= Html::csrfMetaTags() ?>
            <title><?= Html::encode($this->title) ?></title>
            <?php $this->head() ?>
        </head>
        <body class="hold-transition skin-blue sidebar-mini">
            <?php $this->beginBody() ?>
            <div class="wrapper">

                <?php
                switch (Yii::$app->user->identity->role_id){
                    case common\models\Role::ROLE_FRANCHISEE_AGENT:
                        $arr = ['header'=>'header-agent', 'left'=>'left-agent'];
                        break;
                    case common\models\Role::ROLE_FRANCHISEE_OPERATOR:
                        $arr = ['header'=>'header', 'left'=>'left-operator'];
                        break;
                    case common\models\Role::ROLE_FRANCHISEE_ACCOUNTANT:
                        $arr = ['header'=>'header-agent', 'left'=>'left-buh'];
                        break;
                    case common\models\Role::ROLE_FRANCHISEE_MANAGER:
                        $arr = ['header'=>'header', 'left'=>'left-manager'];
                        break;
                    case common\models\Role::ROLE_FRANCHISEE_LEADER:
                        $arr = ['header'=>'header', 'left'=>'left-leader'];
                        break;
                    default:
                        $arr = ['header'=>'header', 'left'=>'left'];
                        break;
                }
                echo $this->render(
                    $arr['header'].'.php', ['directoryAsset' => $directoryAsset]
                );
                echo $this->render(
                    $arr['left'].'.php', ['directoryAsset' => $directoryAsset]
                );
                ?>

                <?=
                $this->render(
                        'content.php', ['content' => $content, 'directoryAsset' => $directoryAsset]
                )
                ?>

            </div>

            <?php $this->endBody() ?>
        </body>
    </html>
    <?php $this->endPage() ?>
<?php } ?>
