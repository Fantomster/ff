<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>MixCart</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700');
    </style>
    <!--[if mso]>
    <style type="text/css">
        p {
            margin-top: 10px;
            margin-bottom: 10px;
        }
    </style>
    <![end if]-->
</head>
<body>
<!-- HEADER -->
<table cellpadding="0" cellspacing="0" border="0" width="100%"
       style="background: #ffffff; min-width: 340px; font-size: 1px; line-height: normal;">
    <tr>
        <td align="center" valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width="680"
                   style="max-width: 680px; min-width: 320px; background: #ffffff;">
                <tr>
                    <td align="center" valign="top">
                <tr>
                    <td align="center" valign="middle" height="105" bgcolor="#2a2c2e" style="">
                        <img src="https://static.mixcart.ru/logo.png" alt="logo" width="101" height="48"
                             border="0"
                             style="border:0; outline:none; text-decoration:none; display:block;">
                    </td>
                </tr>
                </td>
                </tr>
            </table>
        </td>
    </tr>
</table>


<table cellpadding="0" cellspacing="0" border="0" width="100%"
       style="background: #ffffff; min-width: 340px; font-size: 1px; line-height: normal;">
    <tr>
        <td align="center" valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width="580"
                   style="max-width: 580px; min-width: 320px; min-height:300px; background: #ffffff;">
                <tr>
                    <td valign="middle" align="center" height="80" style="font-family: 'Open Sans', Arial, sans-serif;">
                            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 20px;">
                                Успешно отписаны от рассылки MixCart:<br><br>
                                <b><?= $user->email ?></b>
                            </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<?= $this->renderAjax('@common/mail/layouts/mail_footer', ['user' => $user ?? null]) ?>

</body>
</html>