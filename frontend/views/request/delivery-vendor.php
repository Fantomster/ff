<?php
use yii\helpers\Url;
use yii\helpers\Html;
$user = Yii::$app->user->identity;

$franchiseeManager = $user->organization->getFranchiseeManagerInfo();
if ($franchiseeManager && $franchiseeManager->phone_manager) {
    if ($franchiseeManager->additional_number_manager) {
        $phoneUrl = $franchiseeManager->phone_manager . "p" . $franchiseeManager->additional_number_manager;
        $phone = $franchiseeManager->phone_manager . " доб. " . $franchiseeManager->additional_number_manager;
    } else {
        $phoneUrl = $franchiseeManager->phone_manager;
        $phone = $franchiseeManager->phone_manager;
    }
} else {
    $phoneUrl = "+7-499-404-10-18p202";
    $phone = "+7-499-404-10-18 доб. 202";
}
?>
<section class="content">
  <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-12 text-center">
          <h3>Регионы доставки не указаны. <br>
              <small>Проставьте их самостоятельно, в разделе <?=Html::a( 'Доставка', ['vendor/delivery'], ["style"=>"text-decoration:underline"] )?><br>
                  или свяжитесь с нами для уточнения Ваших регионов доставки <a href="tel:<?= $phoneUrl ?>"><?= $phone ?></a></small>
          </h3>         
      </div>
  </div>
</section>
