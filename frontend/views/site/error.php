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
                        Во время обработки вашего запроса произошла ошибка. 
                        Если вы считаете, что это ошибка приложения, пожалуйста, свяжитесь с нами. 
                    </p><p>
                        А пока можно <a href='<?= Yii::$app->homeUrl ?>'>перейти на главную страницу</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main><!-- .content -->
