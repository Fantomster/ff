<?php
use yii\helpers\Url;
?>
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;"><?= Yii::t('app', 'common.mail.forgot_password.link_for_pass', ['ru'=>'Пройдите по ссылке для установки нового пароля:']) ?></p>
<br style="margin: 0; padding: 0;" />
<div style="text-align: center; width: 100%; margin: 0; padding: 0;" align="center">
    <?php
    $sess = Yii::$app->session->get('new_pass_session');
    if(isset($sess)){
        $protocol = (Yii::$app->request->isSecureConnection) ? 'https:' : 'http:';
        $route = $protocol . Yii::$app->urlManagerFrontend->baseUrl . "/user/reset";
        $route.= "?token=".$userToken->token;
        Yii::$app->session->destroy('new_pass_session');
    }else{
        $route = Yii::$app->urlManagerFrontend->createAbsoluteUrl(["/user/reset", "token" => $userToken->token]);
    }
    ?>
    <a href="<?= $route ?>"
       style="text-decoration: none;
    color: #FFF;
    background-color: #84bf76;
    padding: 10px 16px;
    font-weight: bold;
    margin-right: 10px;
    text-align: center;
    cursor: pointer;
    display: inline-block;
    border-radius: 4px;
    width: 80%;"><?= Yii::t('app', 'common.mail.forgot_password.confirm', ['ru'=>'Подтвердить']) ?></a>
</div>