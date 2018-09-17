<?php

use light\swagger\SwaggerUIAsset;

SwaggerUIAsset::register($this);
/** @var string $rest_url */
/** @var array $oauthConfig */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>MixCart Api Web Documentation</title>
    <?php $this->head() ?>
    <script type="text/javascript">
        $(function () {
            var url = window.location.search.match(/url=([^&]+)/);
            if (url && url.length > 1) {
                url = decodeURIComponent(url[1]);
            } else {
                url = "<?= $rest_url ?>";
            }

            hljs.configure({
                highlightSizeThreshold: 5000
            });

            if (window.SwaggerTranslator) {
                window.SwaggerTranslator.translate();
            }
            window.swaggerUi = new SwaggerUi({
                url: url,
                dom_id: "swagger-ui-container",
                supportedSubmitMethods: ['post'],
                onComplete: function (swaggerApi, swaggerUi) {
                    if (typeof initOAuth == "function") {
                        initOAuth(<?= json_encode($oauthConfig) ?>);
                    }

                    if (window.SwaggerTranslator) {
                        window.SwaggerTranslator.translate();
                    }
                },
                onFailure: function (data) {
                    log("Unable to Load SwaggerUI");
                },
                docExpansion: "list",
                jsonEditor: false,
                defaultModelRendering: 'schema',
                showRequestHeaders: true,
                showOperationIds: false
            });

            window.swaggerUi.load();

            function log() {
                if ('console' in window) {
                    console.log.apply(console, arguments);
                }
            }

            $('#clear_cache').click(function () {
                $('#message-bar').html('Сбрасываем...');
                $('#swagger-ui-container').html('');
                $.get('/site/api', {'clear-cache':1}, function() {
                    $('#message-bar').html('Проверяем валидность API...');
                    $.get('/site/api', function(data) {
                        if(data.error) {
                            $('#message-bar').html('Ошибка в описании документации:');
                            $('#swagger-ui-container').html('<b style="color:red">' + data.error + '</b>');
                        } else {
                            window.swaggerUi.load();
                            $('#message-bar').html('Строим доку... Ждем...');
                        }
                    });
                });
            });

        });
    </script>
</head>

<body class="swagger-section">
<?php $this->beginBody() ?>
<div id='header'>
    <div class="swagger-ui-wrap">
        <a id="logo" href="https://mixcart.ru"><span class="logo__title">MixCart API WEB</span></a>
    </div>
    <div style="position:fixed;left:10px;top:10px">
        <button id="clear_cache">Reload cache</button>
    </div>
</div>

<div id="message-bar" class="swagger-ui-wrap" data-sw-translate>&nbsp;</div>
<div id="swagger-ui-container" class="swagger-ui-wrap"></div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
