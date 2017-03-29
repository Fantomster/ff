<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
?>
<style>
 
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-paper-plane"></i> Заявка №<?=$request->id?>
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
            'Заявка №' . $request->id,
        ],
    ])
    ?>
</section>
<style>
.req-name{color:#84bf76;font-size:22px;}
.req-fire{color:#d9534f;font-size:18px;}    
</style>
<section class="content">
    <div class="box box-info">
        <!-- /.box-header -->
        <div class="box-body no-padding">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="req-name">
                            <?=$request->product?>
                        </div> 
                    </div>
                    <div class="col-md-12 no-padding">
                        <hr>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="media">
                                    <div class="media-left">
                                      <img src="<?=$author->pictureUrl?>" class="media-object" style="width:160px">
                                    </div>
                                    <div class="media-body">
                                      <h4 class="media-heading"><?=$author->name?></h4>
                                      <div class="req-fire"><i class="fa fa-fire" aria-hidden="true"></i> СРОЧНО</div>
                                      <div class=""><?=$author->created_at?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">Объем закупки <span class=""><?=$request->amount?></span></div>
                                <div class="">Периодичность заказа <span class=""><?=$request->regular?></span></div>
                                <div class="">Способ оплаты <span class="">
                                    <?=$request->payment_method == \common\models\Request::NAL ? 
                                    'Наличный расчет':
                                    'Безналичный расчет';?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 no-padding">
                        <hr>
                    </div>
                    <div class="col-md-12">
                        <div class="">Подробное описание:</div>
                        <div class="">
                        <?=$request->comment?$request->comment:'<span style="color:#ccc">Нет подробного описания о товаре</span>' ?>
                        </div> 
                    </div>
                    <div class="col-md-12">
                        <div class="">
                            <div class="">Категория: <span class=""><?=$request->categoryName->name ?></span></div>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>