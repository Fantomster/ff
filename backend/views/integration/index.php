<?php
    $this->title = 'Усправление лицензиями';
    $this->params['breadcrumbs'][] = $this->title;
?>

<div class="row" >
    <div class="sm-col-12">
        <h1><?= \yii\helpers\Html::encode($this->title) ?></h1>
    </div>
    <div class="col-sm-12">
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">R-Keeper</h3>
                </div>
                <div class="panel-body">
                    <a href="/rkws" class="btn btn-success">
                        Список лицензий
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">iiko</h3>
                </div>
                <div class="panel-body">
                    <a href="/iiko" class="btn btn-success">
                        Список лицензий
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
