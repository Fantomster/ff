<div class="row checkout">
    <div class="col-md-9">
        <?php foreach ($orders as $order) { ?>
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Заказ у <?= $order->vendor->name ?></h3>
                    <div class="pull-right">
                        <a class="btn btn-outline btn-xs btn-danger" style="margin-right:10px;"><i class="fa fa-trash m-r-xxs" style="margin-top:-2px;"></i> Удалить все</a>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="panel-group">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="form-inline">
                                    <button class="btn btn-default btn-outline" data-toggle="collapse" data-target="#order<?= $order->id ?>">Содержимое заказа</button>
                                    <button class="btn btn-success pull-right"><i class="fa fa-paper-plane" style="margin-top:-3px;"></i> Оформить заказ</button>
                                </div>
                            </div>
                            <div id="order<?= $order->id ?>" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <?= $this->render('_checkoutContent', ['content' => $order->orderContent]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>