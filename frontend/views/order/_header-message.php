<li><!-- start message -->
    <a href="<?= yii\helpers\Url::to(['order/view', 'id' => $message->order_id]) ?>">
        <div class="pull-left">
            <img src="/images/no-avatar.jpg" class="img-circle"
                 alt="User Image"/>
        </div>
        <h4>
            <?= Yii::t('message', 'frontend.views.order.order_number_two', ['ru'=>'Заказ №']) ?><?= $message->order_id ?>
            <small><i class="fa fa-clock-o"></i> <?= Yii::$app->formatter->asDatetime($message->created_at, "php:H:i, j M") ?></small>
        </h4>
        <p><?= $message->message ?></p>
    </a>
</li>
<!-- end message -->
