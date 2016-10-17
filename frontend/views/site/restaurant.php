<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\OrganizationType;

$this->title = 'Ресторанам';
?>
<header class="header inner-bg-block" style="background-image: url(images/restoran-banner.jpg)">
			<div class="inside__block">
				<div class="site__title"> 
					<h1>Революция в работе с поставщиками<br/>Закупка в 2 клика.</h1>
					<h2>Никогда закупка не была проще, чем сейчас</h2>
				</div>
			</div>
			<div class="overlay"></div>
		</header><!-- .header-->


		<main class="content">
			
			<div class="restoran__content">
				
				<div class="inside__block">
					<div class="container-fluid">
						<h2>возможности f-keeper</h2>
						<span class="for__who">Для ресторанов</span>
						
						<div class="row">
							<div class="col-md-4">
								<div class="rest__item">
									<span>Все поставщики в одном месте</span>
									<img src="images/rest-1.png" alt="" />
									<p>В f-keeper, вы можете видеть всех поставщиков, и выбрать для себя тех, кто соответствует вашим критериям.</p> 
								</div>
							</div>
							
							<div class="col-md-4">
								<div class="rest__item">
									<span>Прозрачность</span>
									<img src="images/rest-2.png" alt="" />
									<p>Вас не устраивает сервис поставщика или качество продуктов? Оставьте отзыв, поставьте оценку.</p> 
								</div>
							</div>
							
							<div class="col-md-4">
								<div class="rest__item">
									<span>Закупка в 2 клика</span>
									<img src="images/rest-3.png" alt="" />
									<p>Больше никаких звонков, таблиц и прочих, устаревших инструментов. Создавайте заказы в несколько кликов.</p> 
								</div>
							</div>
							
							<div class="col-md-4">
								<div class="rest__item">
									<span>Подробная аналитика</span>
									<img src="images/rest-4.png" alt="" />
									<p>Подробная аналитика позволит знать, что, сколько и у кого вы закупаете. Формировать и выгружать подробные отчеты. </p> 
								</div>
							</div>
							
							<div class="col-md-4">
								<div class="rest__item">
									<span>История заказов</span>
									<img src="images/rest-5.png" alt="" />
									<p>Просматривайте историю заказов. Создавайте новые заказы или повторяйте предыдущие. Это сокращает время на закупку в несколько раз.</p> 
								</div>
							</div>
							
							<div class="col-md-4">
								<div class="rest__item">
									<span>Распродажи поставщиков</span>
									<img src="images/rest-6.png" alt="" />
									<p>Закупайте по акции. Все акции и распродажи поставщиков в одном месте. Выгодно вам, выгодно им.</p> 
								</div>
							</div>
							
							<div class="col-md-4">
								<div class="rest__item">
									<span>Выставление лимитов</span>
									<img src="images/rest-7.png" alt="" />
									<p>Вы можете ограничить объем закупок определенным лимитом. Закупщик не сможет сделать закупку больше лимита.</p> 
								</div>
							</div>
							
							<div class="col-md-4">
								<div class="rest__item">
									<span>Размещение тендеров</span>
									<img src="images/rest-8.png" alt="" />
									<p>Вы можете разместить тендер на закупку тех или иных продуктов, и поставщики сами предложат вам максимально выгодные для вас цены.</p> 
								</div>
							</div>
							
							<div class="col-md-4">
								<div class="rest__item">
									<span>Коммуникации в одном месте</span>
									<img src="images/rest-9.png" alt="" />
									<p>Никакого человеческого фактора. Все коммуникации по каждому заказу в одном месте. Никакой потери информации больше не будет.</p> 
								</div>
							</div>
						
						</div>
						
					</div>
				</div>
				
			
			
			
			
			</div>
			
			
			
			<div class="white__bottom_block">
				<div class="inside__wh_block">
					<div class="container-fluid">
					
						<div class="inside__re">
							<div class="col-md-4 col-sm-4 block1">
								<img src="images/icon-1.jpg" alt=""/>
								<span>Регистрируйтесь</span>
							</div>
							<div class="col-md-4 col-sm-4 block2">
								<img src="images/icon-2.jpg" alt=""/>
								<span>Подключайте поставщиков</span>
							</div>
							<div class="col-md-4 col-sm-4 block3">
								<img src="images/icon-3.jpg" alt=""/>
								<span>Заказывайте!</span>
							</div>
						</div>
						
						<a class="btn__nav" href="#">начать сейчас</a>
					
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