<?php
use yii\web\View;

$timestamp_now=time();
$sub0 = explode(' ',$lic->td);
$sub1 = explode('-',$sub0[0]);
$lic->td = $sub1[2].'.'.$sub1[1].'.'.$sub1[0];
if ($lic->status_id==0) $lic_merc=0;
if (($lic->status_id==1) and ($timestamp_now<=(strtotime($lic->td)))) $lic_merc=3;
if (($lic->status_id==1) and (($timestamp_now+14*86400)>(strtotime($lic->td)))) $lic_merc=2;
if (($lic->status_id==1) and ($timestamp_now>(strtotime($lic->td)))) $lic_merc=1;
    ?>
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding">
                    <?php
                    if ($lic_merc!=3) {
                        echo "<p>Состояние лицензии";
                        switch($lic_merc) {
                            case 0: print "Лицензия ВЕТИС Меркурий: ID ".$lic->id." <strong><span style=\"color:#dd4b39\">Не активна</span></strong>.</br>";
                                print "Пожалуйста, обратитесь к вашему менеджеру MixCart."; break;
                            case 1: print "Лицензия ВЕТИС Меркурий: ID ".$lic->id." <strong><span style=\"color:#dd4b39\">Не активна </span></strong>с ".$lic->td.".</br>";
                                print "Пожалуйста, обратитесь к вашему менеджеру MixCart."; break;
                            case 2: print "Лицензия ВЕТИС Меркурий: ID ".$lic->id." <strong><span style=\"color:#dd4b39\">Истекает срок </span></strong>(по ".$lic->td."). </br>";
                                print "Пожалуйста, обратитесь к вашему менеджеру MixCart."; break;
                        }
                        echo "</p>";
                    }
                    ?>
                    <p id="mercNotificationVsd"></p>
                    <?php if ($lic->code == \api\common\models\merc\mercService::EXTENDED_LICENSE_CODE) : ?>
                        <p id="mercNotificationStockEntry"></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php
try {
    $enterpriseGuid = \api\common\models\merc\mercDicconst::getSetting('enterprise_guid');
    $messageVSD = 'Время последнего обновления списка ВСД (Следующее обновление будет произведено в течении 15 минут): ';
    $messageStock = 'Время последнего обновления журнала входной продукции (Следующее обновление будет произведено в течении 15 минут): ';
    $customJs = <<< JS
        var refVSD = firebase.database().ref('/mercury/operation/MercVSDList_$lic->org');
        refVSD.on("value", (snapshot) => {
            if (typeof(snapshot.val()) == "undefined" || snapshot.val() == null || snapshot.val().length == 0)
             {
                $('#mercNotificationVsd').html('$messageVSD' + 'неизвестно');
            }
            else
             {
                var now = new Date();
                var timestamp = snapshot.val().update_date * 1000 - (now.getTimezoneOffset() * 60000);
                if(isNaN(timestamp)) {
                    $('#mercNotificationVsd').html('$messageVSD' + 'неизвестно');
                    return;
                }
                now = new Date(timestamp);
                var formatted =  ('0' + now.getDate()).substr(-2,2) + '.' + ('0' + (now.getMonth() + 1)).substr(-2,2) + '.' + now.getFullYear() + ' ' + ('0' + now.getHours()).substr(-2,2) + ":" + ('0' + now.getMinutes()).substr(-2,2) + ":" + ('0' + now.getSeconds()).substr(-2,2);
                $('#mercNotificationVsd').html('$messageVSD' + formatted);    
                //console.log(snapshot.val().update_date); //Вывод значения в консоль
        }
    });
        var refStock = firebase.database().ref('/mercury/operation/MercStockEntryList_$lic->org');
        refStock.on("value", (snapshot) => {
         if (typeof(snapshot.val()) == "undefined" || snapshot.val() == null || snapshot.val().length == 0 || isNaN(snapshot.val())) 
          {
                $('#mercNotificationStockEntry').html('$messageStock' + 'неизвестно');
        }
        else
         { 
            var now = new Date();
            var timestamp = snapshot.val().update_date * 1000 - (now.getTimezoneOffset() * 60000);
             if(isNaN(timestamp)) {
                    $('#mercNotificationVsd').html('$messageStock' + 'неизвестно');
                    return;
                }
            now = new Date(timestamp);
            var formatted =  ('0' + now.getDate()).substr(-2,2) + '.' + ('0' + (now.getMonth() + 1)).substr(-2,2) + '.' + now.getFullYear() + ' ' + ('0' + now.getHours()).substr(-2,2) + ":" + ('0' + now.getMinutes()).substr(-2,2) + ":" + ('0' + now.getSeconds()).substr(-2,2);
            $('#mercNotificationStockEntr').html('$messageStock' + formatted);    
            //console.log(snapshot.val().update_date); //Вывод значения в консоль
        }
    });
JS;
    $this->registerJs($customJs, View::POS_END);
}
catch (\Exception $e)
{
}
?>
