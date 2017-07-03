<?php

use yii\helpers\Html;
use yii\helpers\Url;
/**
 * @var \yii\web\View $this
 * @var \yii\mail\BaseMessage $content
 */
?>
<div style="display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;font-family: sans-serif;">
			Новый отклик на Вашу заявку
		</div>
<table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" height="65" style="margin: auto;" class="email-container">
    <tr>
            <td bgcolor="#fff" valign="top" style="text-align: center; background-position: top center !important;  background-repeat: no-repeat !important; width: 100%; max-width: 600px; height: 100%;">
                    <!--[if gte mso 9]>
                    <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;height:175px; background-position: top center !important;">
                    <v:fill type="tile" src="img/bg-top-mail.png" color="#fff" />
                    <v:textbox inset="0,0,0,0">
                    <![endif]-->

                    <!--[if gte mso 9]>
                    </v:textbox>
                    </v:rect>
                    <![endif]-->
            </td>
    </tr>
    <tr>
            <td>
                    <div>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" >
                                    <tr>
                                            <td valign="top" style="text-align: center;">
                                                    <a href=""><img src="http://f-keeper.ru/img/request/logo-mail.png" alt="" /></a>
                                            </td>
                                    </tr>
                            </table>
                    </div>
            </td>
    </tr>
</table>
<!-- Email Header : END -->
<br />
<!-- Email Body : BEGIN -->
<table cellspacing="0" cellpadding="0" border="0" align="center" bgcolor="#ffffff" width="600" style="margin: auto; border: 5px solid #66BC75;" class="email-container">

    <!-- Hero Image, Flush : BEGIN -->

    <!-- Hero Image, Flush : END -->
    <!-- 1 Column Text : BEGIN -->

    <tr>
            <td style="text-align: center; font-family: 'Open Sans', sans-serif;  mso-height-rule: exactly; color: #555555;">
                    <h1 style="margin-bottom: 0;font-size: 16px;line-height: 27px;font-weight: 500;">Уважаемый(ая) <?=$client->profile->full_name;?>!</h1>					
            </td>
    </tr>

    <tr>
            <td style="padding: 10px 30px; text-align: center; font-family: 'Open Sans', sans-serif; font-size: 14px; mso-height-rule: exactly; line-height: 20px; color: #555;padding-top: 25px;">
                    На Вашу заявку <b><?=$request->product;?></b> поступил отклик от поставщика <b>«<?=$vendor->organization->name;?>»</b>
                    <br><br>

            </td>
    </tr>
    <tr>
            <td style="text-align: center;padding: 15px 0; padding-top: 0;font-family: 'Open Sans', sans-serif;color: #b7b7b7; font-size: 14px">
                    <span>Дата отклика: <?=date('Y-m-d H:i');?></span>
            </td>
    </tr>
    <tr>
            <td style="padding: 10px 30px; padding-bottom: 40px; text-align: center; font-family: 'Open Sans', sans-serif; font-size: 16px; mso-height-rule: exactly; line-height: 16px; color: #555;">
                    <a href="<?= Url::toRoute(["/request/view", 'id' => $request->id], true) ?>" style="background-color: #66BC75; padding: 10px 30px; border-radius: 30px; color: #fff; text-decoration: none; cursor: pointer;">Перейти к заявке</a>
                    <br><br>

            </td>
    </tr>
    <!-- 1 Column Text : BEGIN -->
    <!-- Background Image with Text : BEGIN -->

</table>
<br />
<table cellspacing="0" cellpadding="0" border="0" align="center" bgcolor="#ffffff" width="600" style="margin: auto; border: 5px solid #b7b7b7;" class="email-container">

    <!-- Hero Image, Flush : BEGIN -->

    <!-- Hero Image, Flush : END -->
    <!-- 1 Column Text : BEGIN -->
    <tr>
            <td style="padding: 10px 30px; padding-top: 40px; text-align: center; font-family: 'Open Sans', sans-serif; font-size: 24px; mso-height-rule: exactly; line-height: 30px; color: #555;">
                     Ваш личный менеджер
            </td>
    </tr>
    <tr>
            <td style="padding: 10px 30px; text-align: center; font-family: 'Open Sans', sans-serif; font-size: 14px; mso-height-rule: exactly; line-height: 20px; color: #555;">
                    <img src="http://f-keeper.ru/img/request/welcome-zalina.png" alt="" />

            </td>
    </tr>
    <tr>
            <td style="padding: 10px 30px; text-align: center; font-family: 'Open Sans', sans-serif; font-size: 14px; mso-height-rule: exactly; line-height: 20px; color: #555;">
                    Залина

            </td>
    </tr>
    <tr>
            <td style="padding: 10px 30px; text-align: center; font-family: 'Open Sans', sans-serif; font-size: 14px; mso-height-rule: exactly; line-height: 20px; color: #555;">
                    <strong style="font-size: 20px;">+7 929 611 79 00</strong>
                    <br><br>

            </td>
    </tr>
    <!-- 1 Column Text : BEGIN -->
    <!-- Background Image with Text : BEGIN -->
</table>