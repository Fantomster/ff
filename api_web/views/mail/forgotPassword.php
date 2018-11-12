<?php
use yii\helpers\Url;
if (empty($toFrontEnd)) {
    $toFrontEnd = false;
}
?>
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;"><?= Yii::t('app', 'common.mail.forgot_password.pass', ['ru'=>'Ваш новый паролль:']) ?></p>
<br style="margin: 0; padding: 0;" />
<div style="text-align: center; width: 100%; margin: 0; padding: 0;" align="center">
   <b><?=$user->newPassword?></b>
</div>