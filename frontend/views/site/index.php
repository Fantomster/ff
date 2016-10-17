<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\OrganizationType;

$this->title = 'F-keeper';
?>
<header class="header" style="background-image: url(images/header-banner.jpg)">
    <div class="inside__block">
        <div class="site__title"> 
            <h1>Автоматизация закупок<br/>между поставщиками и ресторанами</h1>
            <h2>Никогда закупка не была проще, чем сейчас</h2>
        </div>
        <div class="buttons__block">
            <?= Html::a('<span>для ресторанов</span>', ["/site/restaurant"], ['class' => 'for__restaurants']) ?>
            <?= Html::a('<span>для поставщиков</span>', ["/site/supplier"], ['class' => 'for__suppliers']) ?>
            <div class="clear"></div>
            <div class="watch_video">
                <a href="#" data-toggle="modal" data-target="#myModal" ><span class="glyphicon glyphicon-play-circle"></span><span class="watch__span">посмотреть видео</span></a>
            </div>


            <div id="myModal" class="modal fade">
                <div class="modal-dialog">
                    <div class="video__block">
                        <iframe width="100%" height="500px" src="https://www.youtube.com/embed/xkScMQHqORk" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <a href="#bottom" class="show__bottom"></a>
    <div class="overlay"></div>
</header><!-- .header-->

<main class="content">
    <div id="bottom" class="white__block">
        <div class="inside__block">
            <div class="container-fluid">
                <div class="col-md-5">
                    <div class="how_its_work">
                        <h3>как это работает</h3>
                        <p>F-keeper это инструмент для автоматизации процесса взаимодействия между поставщиком и рестораном. Рестораны создают заказы, в несколько кликов. Поставщики получают и обрабатывают заказы. Обработка всех заказов, происходит в одном месте. Минимум человеческого фактора. F-keeper, сокращает время на обработку заказов в несколько раз. Уменьшает количество возвратов и ошибок.</p>
                        <?= Html::a('Для ресторанов', ["/site/restaurant"]) ?> / <?= Html::a('Для поставщиков', ["/site/supplier"]) ?>
                    </div>
                </div>	
                <div class="col-md-7">
                    <img class="hows__banner" src="images/image-1.png" alt=""/>
                </div>	
            </div>
        </div>
    </div>

    <div class="number__block">
        <div class="inside__number-block">
            <div class="container-fluid">
                <div class="col-md-6 col-sm-6">
                    <span class="number">420</span>
                    <span class="plays__title">Ресторанов</span>	
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="alig__right">
                        <span class="number">200</span>
                        <span class="plays__title">Поставщиков</span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="logo__block_outside">
        <span><img src="images/logo-1.png" alt=""/></span>
        <span><img src="images/logo-2.png" alt=""/></span>
        <span><img src="images/logo-3.png" alt=""/></span>
        <span><img src="images/logo-4.png" alt=""/></span>
    </div>

    <div class="contact__block">

        <h4>Автоматизируйте свой бизнес сейчас</h4>
        <span>Вы в одном шаге, расскажите о себе</span>
        <div class="contact__form">
            <?php if ($flash = Yii::$app->session->getFlash("Register-success")): ?>

                <div class="alert alert-success">
                    <p><?= $flash ?></p>
                </div>

            <?php else: ?>
                <?php
                $form = ActiveForm::begin(['id' => 'login-form', 'action' => Url::toRoute('user/register')]);
                ?>
                <div class="form-group">
                    <?=
                            $form->field($organization, 'type_id')
                            ->label(false)
                            ->dropDownList(OrganizationType::getList(), [
                                'prompt' => 'ресторан / поставщик',
                                'class' => 'form-control'])
                    ?>
                    <?=
                            $form->field($organization, 'name')
                            ->label(false)
                            ->textInput(['class' => 'form-control', 'placeholder' => 'название организации'])
                    ?>
                    <?=
                            $form->field($user, 'email')
                            ->label(false)
                            ->textInput(['class' => 'form-control', 'placeholder' => 'email'])
                    ?>
                    <?=
                            $form->field($profile, 'full_name')
                            ->label(false)
                            ->textInput(['class' => 'form-control', 'placeholder' => 'фио'])
                    ?>
                    <?=
                            $form->field($user, 'newPassword')
                            ->label(false)
                            ->passwordInput(['class' => 'form-control', 'placeholder' => 'пароль'])
                    ?>
                </div>
                <?=
                Html::a('Зарегистрироваться', '#', [
                    'data' => [
                        'method' => 'post',
                    ],
                    'class' => 'send__btn',
                ])
                ?>
            <input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" />
                <?php ActiveForm::end(); ?>
            <?php endif; ?>
        </div>

    </div>
</main><!-- .content -->
