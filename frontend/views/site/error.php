<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
?>
<main class="content content-inner" style="margin-bottom: 100px;">
    <div class="faq__inner">
        <div class="container-fluid">
            <div class="error-page">
                <h2 class="headline text-info"><i class="fa fa-warning text-yellow"></i></h2>

                <div class="error-content">
                    <h3><?= $name ?></h3>

                    <p>
                        <?= nl2br(Html::encode($message)) ?>
                    </p>

                    <p>
                        <?= Yii::t('error', 'frontend.views.site.error', ['ru'=>'Во время обработки вашего запроса произошла ошибка.']) ?>
                        <?= Yii::t('error', 'frontend.views.site.error_two', ['ru'=>'Если вы считаете, что это ошибка приложения, пожалуйста, свяжитесь с нами.']) ?>
                    </p><p>
                        <?= Yii::t('message', 'frontend.views.site.home', ['ru'=>'А пока можно <a href="{home_url}">перейти на главную страницу</a>.', 'home_url'=>Yii::$app->params['staticUrl'][Yii::$app->language]['home']]) ?>
                    </p>

                </div>
            </div>
        </div>
    </div>
</main><!-- .content -->
