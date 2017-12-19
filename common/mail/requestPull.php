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
		<!-- Visually Hidden Preheader Text : END -->
		<!-- Email Header : BEGIN -->
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
									<a href=""><img src="https://mixcart.ru/img/request/logo-mail.png" alt="" /></a>
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
					<h1 style="margin-bottom: 0;font-size: 16px;line-height: 27px;font-weight: 500;padding-top: 27px">Уважаемый(ая) <?=$vendor['full_name']?>!</h1>					
				</td>
			</tr>

			<tr>
				<td style="padding: 10px 30px; text-align: center; font-family: 'Open Sans', sans-serif; font-size: 14px; mso-height-rule: exactly; line-height: 20px; color: #555;">
					<b>На <?=date("Y-m-d", strtotime("yesterday"));?> были созданы новые заявки:</b>
					<br><br>
					<table cellspacing="0" cellpadding="0" border="0" align="center" bgcolor="#ffffff" width="100%" style="margin: auto;" class="email-container">
					<?php
                                        $i = 0;
                                        foreach($requests as $request){
                                            if($i>=5){break;}
                                        ?>
                                        <tr style="margin-top: 5px;">
						<td style="text-align: left; padding: 10px">
							<b><?=$request->client->name;?>
							№<?=$request->id?> <?=$request->product?></b><br>
							<font style="font-family: 'Open Sans', sans-serif;color: #999999; font-size: 14px">Создана: <?=$request->created_at;?>, Дата завершения <?=$request->end;?></font>
						</td>
					</tr>
                                        <?php
                                        $i++;
                                        }
                                        ?>
					</table>
					
				</td>
			</tr>
			<tr>
				<td style="padding: 10px 30px; padding-bottom: 40px; text-align: center; font-family: 'Open Sans', sans-serif; font-size: 16px; mso-height-rule: exactly; line-height: 16px; color: #555;">
					<a href="https://mixcart.ru/request/list" style="background-color: #66BC75; padding: 10px 30px; border-radius: 30px; color: #fff; text-decoration: none; cursor: pointer;">Просмотреть все заявки</a>
					<br><br>
					
				</td>
			</tr>
			<!-- 1 Column Text : BEGIN -->
			<!-- Background Image with Text : BEGIN -->
			
		</table>
		<br />