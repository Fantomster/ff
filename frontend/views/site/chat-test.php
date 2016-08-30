<?php

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'My Yii Application';

$js = <<<JS

$( document ).ready(function() {

    var socket = io.connect('http://localhost:8890');

        
        
socket.on('connect', function(){
      socket.emit('authentication', {userid: "$user->id", token: "$user->access_token"});
        

});
            socket.on('notification', function (data) {

        var message = JSON.parse(data);

        $( "#notifications" ).prepend( "<p><strong>" + message.name + "</strong>: " + message.message + "</p>" );

    });
        
});   
        
$('#chat-form').submit(function() {

     var form = $(this);

     $.ajax({
          url: form.attr('action'),
          type: 'post',
          data: form.serialize(),
          success: function (response) {
               $("#message-field").val("");
          }
     });

     return false;
});
JS;
$this->registerJs($js, \yii\web\View::POS_READY)
?>
<div class="site-index">

    <div class="body-content">

        <div class="row">
            <div class="well col-lg-8 col-lg-offset-2">

                <?=
                Html::beginForm(['/site/chat-test'], 'POST', [
                    'id' => 'chat-form'
                ])
                ?>

                <div class="row">
                    <div class="col-xs-3">
                        <div class="form-group">
<?= Html::label($user->profile->full_name) ?>
<?= Html::hiddenInput('name', $user->profile->full_name); ?>
                        </div>
                    </div>
                    <div class="col-xs-7">
                        <div class="form-group">
                            <?=
                            Html::textInput('message', null, [
                                'id' => 'message-field',
                                'class' => 'form-control',
                                'placeholder' => 'Message'
                            ])
                            ?>
                        </div>
                    </div>
                    <div class="col-xs-2">
                        <div class="form-group">
<?=
Html::submitButton('Send', [
    'class' => 'btn btn-block btn-success'
])
?>
                        </div>
                    </div>
                </div>

<?= Html::endForm() ?>

                <div id="notifications" ></div>
            </div>
        </div>

    </div>
</div>