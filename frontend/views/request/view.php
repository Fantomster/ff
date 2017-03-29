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
<section class="content">
    <div class="box box-info">
        <!-- /.box-header -->
        <div class="box-body">
            <div class="col-md-12 no-padding">
                <div class="row">
                    <div class="col-md-12">
                        <div class="r-title">
                            
                        </div> 
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                
                            <div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="r-title"></div> 
                    </div>
                    <div class="col-md-12">
                        <div class="r-title"></div> 
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>