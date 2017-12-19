<?php
/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = Yii::t('message', 'frontend.views.site.about', ['ru'=>'О компании']);
?>
<main class="content content-inner ">
			<div class="faq__inner">
				<div class="container-fluid">
					<div class="inside__faq">
						<h2><?= Yii::t('message', 'frontend.views.site.about_two', ['ru'=>'О компании']) ?></h2>
						<span class="faq__inder_title"><?= Yii::t('message', 'frontend.views.site.one_goal', ['ru'=>'Мы руководствуемся одной целью: обеспечить вас современными и технологичными инструментами.']) ?></span>
					<!--	<p><b>Здравствуйте, наша компания работает на рынке информационных технологий около 10 лет. </b></p> -->
						<p><?= Yii::t('message', 'frontend.views.site.world_changed', ['ru'=>'Пришло время, когда работать без современной IT инфраструктуры просто невозможно. Мир изменился, и это стало неотъемлемой частью бизнеса.']) ?><br/>
						
						 <br/><?= Yii::t('message', 'frontend.views.site.we_are_young', ['ru'=>'Мы молодая и амбициозная команда профессионалов. Наши ключевые ценности: <b>действовать и создавать</b>.']) ?><br/>
						 <br/>
						 <?= Yii::t('message', 'frontend.views.site.experience', ['ru'=>'В основу площадки MixCart был вложен наш 10 летний опыт работы по автоматизации бизнеса, продажам и решению сложных бизнес задач.']) ?>
						 <br/>
						 <br/><?= Yii::t('message', 'frontend.views.site.our_goal', ['ru'=>'Цель, которую мы ставим перед собой, автоматизировать закупки во всех ресторанах, кафе, барах России и СНГ.']) ?><br/>
						 <br/><?= Yii::t('message', 'frontend.views.site.join_us', ['ru'=>'Присоединяйтесь, мы будем рады видеть вас в числе наших клиентов. Вы можете рассчитывать на нас.']) ?>
						 
						 
						 
					</p> 
					</div>
				</div>
			</div>
			<div class="clear"></div>
					
				
			</div>
			<div class="error__block">
				<p><?= Yii::t('message', 'frontend.views.site.wanna_work', ['ru'=>'Хотите работать у нас?']) ?> <a href="mailto:hr@mixcart.ru">hr@mixcart.ru</a></p>
			</div>
		</main><!-- .content -->
