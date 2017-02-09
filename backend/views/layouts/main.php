<?php
/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use nirvana\showloading\ShowLoadingAsset;

ShowLoadingAsset::register($this);
$this->registerCss('#loader-show {position:absolute;width:100%;display:none;}');

AppAsset::register($this);

$customJs = <<< JS
$('#loader-show').css('height',$(window).height());
$(window).on('resize',function() {
    $('#loader-show').css('height',$(window).height());
});
JS;
$this->registerJs($customJs, yii\web\View::POS_READY);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
<?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
<?php $this->beginBody() ?>
        <div id="loader-show"></div>
        <div class="wrap">
<?php
NavBar::begin([
    'brandLabel' => 'f-keeper',
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'navbar-inverse navbar-fixed-top',
    ],
]);
$menuItems = [
    [
        'label' => 'Статистика',
        'items' => [
            [
                'label' => 'Зарегистрировано',
                'url' => ['/statistics/registered'],
            ],
            [
                'label' => 'Заказы',
                'url' => ['/statistics/orders'],
            ],
        ],
    ],
    ['label' => 'Пользователи', 'url' => ['/client/index']],
    [
        'label' => 'Организации',
        'items' => [
            [
                'label' => 'Общий список',
                'url' => ['/organization/index'],
            ],
            [
                'label' => 'Одобренные для f-market',
                'url' => ['/white-list/index'],
            ],
        ],
    ],
    ['label' => 'Заказы', 'url' => ['/order/index']],
    [
        'label' => 'Товары',
        'items' => [
            [
                'label' => 'Общий список',
                'url' => ['/goods/index'],
            ],
            [
                'label' => 'Загруженные каталоги',
                'url' => ['/goods/uploaded-catalogs'],
            ],
        ],
    ],
];
if (Yii::$app->user->isGuest) {
    $menuItems[] = ['label' => 'Login', 'url' => ['/user/login']];
} else {
    $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->email . ')', ['class' => 'btn btn-link']
            )
            . Html::endForm()
            . '</li>';
}
echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => $menuItems,
]);
NavBar::end();
?>

            <div class="container">
            <?=
            Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ])
            ?>
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <p class="pull-left">&copy; f-keeper <?= date('Y') ?></p>

                <p class="pull-right">Работает, оно работает!</p>
            </div>
        </footer>
<div id="loader-show"></div>
<?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
