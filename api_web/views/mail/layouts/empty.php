<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
        <title>MixCart</title>
        <!--[if mso]>
        <style type="text/css">
            p {
                margin-top: 10px;
                margin-bottom: 10px;
            }
        </style>
        <![end if]-->
        <?php $this->head() ?>
    </head>
    <body>
        <?php $this->beginBody() ?>
        <?= $content ?>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>