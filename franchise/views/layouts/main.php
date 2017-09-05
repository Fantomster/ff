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
                if (Yii::$app->user->identity->role_id === common\models\Role::ROLE_FRANCHISEE_AGENT) {
                    echo $this->render(
                            'header-agent.php', ['directoryAsset' => $directoryAsset]
                    );
                    echo $this->render(
                            'left-agent.php', ['directoryAsset' => $directoryAsset]
                    );
                } elseif (Yii::$app->user->identity->role_id === common\models\Role::ROLE_FRANCHISEE_OPERATOR) {
                    echo $this->render(
                        'header.php', ['directoryAsset' => $directoryAsset]
                    );
                    echo $this->render(
                        'left-operator.php', ['directoryAsset' => $directoryAsset]
                    );
                } elseif (Yii::$app->user->identity->role_id === common\models\Role::ROLE_FRANCHISEE_ACCOUNTANT) {
                    echo $this->render(
                        'header-agent.php', ['directoryAsset' => $directoryAsset]
                    );
                    echo $this->render(
                        'left-buh.php', ['directoryAsset' => $directoryAsset]
                    );
                } else {
                    echo $this->render(
                            'header.php', ['directoryAsset' => $directoryAsset]
                    );
                    echo $this->render(
                            'left.php', ['directoryAsset' => $directoryAsset]
                    );
                }
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
