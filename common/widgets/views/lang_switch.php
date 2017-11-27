<?php

use yii\helpers\Html, common\widgets\LangSwitch;

//Регистрируем стили виджета
$this->registerCSS("
    .lang_switch .dropdown-menu {
        width: 50px!important;
        left:auto!important;
        right:auto!important;
        min-width: 50px!important;
    }

    .lang_switch .dropdown-menu li > a {
        padding-left: 15px!important;
    }
");

//Текущая ссылка
$url = (Yii::$app->request->pathInfo == 'site/index' ? '/' : Yii::$app->request->pathInfo);

?>

<li class="dropdown lang_switch">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <?= LangSwitch::getFlag(Yii::$app->language) ?>
    </a>
    <ul class="dropdown-menu">
        <?php foreach (Yii::$app->urlManager->languages as $lang): ?>
            <li class="<?= ($lang === Yii::$app->language ? 'active' : '') ?>">
                <?= Html::a(
                    LangSwitch::getFlag($lang),
                    Yii::$app->urlManager->createUrl([$url, 'language' => $lang])
                ) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</li>