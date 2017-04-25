<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\widgets\ListView;
?>

<style>
.bg-default{background:#555} p{margin: 0;}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-paper-plane"></i> Заявка №<?=1?>
        <small>Следите за активностью заявки</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Список заявок',
                'url' => ['request/list'],
            ],
            'Заявка №' . 1,
        ],
    ])
    ?>
</section>
<section  class="content-header">
  <div class="row">
    <div class="col-md-12">
      <div class="box box-info">
        <div class="box-body">
	  <div class="col-md-6">
            <div class="row">
              <div class="col-md-12">
		<h3 class="text-success">Помидоры красные <span style="color:#d9534f"><i class="fa fa-fire" aria-hidden="true"></i> СРОЧНО</span></h3>
                <h4 >описание заявки описание заявки описание заявки описание заявки описание заявки описание заявки </h4>
              </div>
            </div>
            <h6><b>Объем закупки:</b> 44</h6>
            <h6><b>Периодичность заказа:</b> Ежедневно</h6>
            <h6><b>Способ оплаты:</b> Наличный расчет</h6>
            <div class="req-respons">Исполнитель: 
                    <span style="color:#ccc;">не назначен</span>
            </div>
            <p style="margin:0;margin-top:15px"><b>Создана</b> 20 Апреля, в 01:17</p>
            <p style="margin:0;margin-bottom:15px"><b>Будет снята</b> 27 Апреля, в 01:17</p>
            <a class="btn btn-success">предложить свои услуги</a>
            <a class="btn btn-gray">Снять с размещения</a>
            <div class="pull-right" style="margin-top: 9px">
                  <i class="fa fa-eye" style="font-size:19px !important" aria-hidden="true"></i> 3
                  <i class="fa fa-handshake-o" style="font-size:19px !important" aria-hidden="true"></i> 2
		</div>
	  </div>
          <div class="col-md-6">
              <h3 class="text-success">Ресторан суперПупер</h3>
              <h4>Привольная ул., 70, Москва, 109431 <small>Адрес можно изменить в разделе "Настройки"</small></h4>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2249.6110260682813!2d37.8512048160045!3d55.67836390519804!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x414ab66c483abb85%3A0x494f4e20cbd20469!2z0J_RgNC40LLQvtC70YzQvdCw0Y8g0YPQuy4sIDcwLCDQnNC-0YHQutCy0LAsIDEwOTQzMQ!5e0!3m2!1sru!2sru!4v1493018909595" width="100%" height="200" frameborder="0" style="border:0" allowfullscreen></iframe>	
          </div>
	  <div class="col-md-12">
	    <hr>
	    <h5>Отклики поставщиков</h5>
            <div class="col-md-12" style="padding:15px;border-radius:3px;background:#f7f7f7;margin-bottom:15px;">
              <div class="row">
                <div class="col-md-12">
                  <div class="media">
                    <div class="media-left">
                      <img src="http://testama.f-keeper.ru/images/vendor-noavatar.gif" class="media-object" style="width:160px">
                    </div>
                    <div class="media-body" style="line-height: 1.6;">
                      <div class="row">
                        <div class="col-md-12">
                          <h4 class="text-success">Супер Поставщик 
                              <a href="#" class="btn btn-success pull-right" style="font-size:16px;margin-top:-10px;">назначить исполнителем</a>
                              <a href="#" class="btn btn-warning pull-right" style="font-size:16px;margin-top:-10px;margin-right:10px"><i class="fa fa-comment"></i> 1</a>
                          </h4>
                          <h5>Цена: <span class="text-bold">500 руб</span></h5>
                          <p>Комментарий поставщика: В наличие более 200кг </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-12" style="padding:15px;border-radius:3px;background:#f7f7f7;margin-bottom:15px;">
              <div class="row">
                <div class="col-md-12">
		  <div class="media">
		    <div class="media-left">
		      <img src="http://testama.f-keeper.ru/images/vendor-noavatar.gif" class="media-object" style="width:160px">
		    </div>
		    <div class="media-body" style="line-height: 1.6;">
		      <div class="row">
		        <div class="col-md-12">
			  <h4 class="text-success">МамаЯвДубае    
                              <a href="#" class="btn btn-success pull-right" style="font-size:16px;margin-top:-10px">назначить исполнителем</a>
                              <a href="#" class="btn btn-gray pull-right" style="font-size:16px;margin-top:-10px;margin-right:10px"><i class="fa fa-comment"></i></a>
                          </h4>
                          <h5>Цена: <span class="text-bold">440 руб</span></h5>
			  <p>Комментарий поставщика: готов поставить </p>
                        </div>
                      </div>
		    </div>
		  </div>
                </div>
              </div>
	    </div>
	  </div>
        </div>
      </div>
    </div>
  </div>
</section>