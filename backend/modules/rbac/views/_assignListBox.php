<?php

use yii\helpers\Html;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $assignUrl array */
/* @var $removeUrl array */
/* @var $opts string */
/* @var $orgId int */
/* @var $orgList array */
/* @var $rolesByOrg string */

$this->registerJs("var _opts = {$opts};", View::POS_BEGIN);
?>
    <div class="row">
        <div class="col-lg-5">
            <select id="orgList" class="form-control list">
                <option value="">Показать роли по организации</option>
                <?php foreach ($orgList as $key => $org) { ?>
                    <option value="<?= $key ?>" <?= $key == $orgId ? 'selected' : '' ?>><?= $org ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-lg-2">
        </div>
        <div class="col-lg-5">
            <input class="form-control" type="text" placeholder="ID Организации" id="org" value="<?= $orgId ?>">
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-lg-5">
            <input class="form-control search" data-target="available"
                   placeholder="<?php echo Yii::t('yii2mod.rbac', 'Search for available'); ?>">
            <br/>
            <select multiple size="20" class="form-control list" data-target="available"></select>
        </div>
        <div class="col-lg-2">
            <div class="move-buttons">
                <br><br>
                <?php echo Html::a('&gt;&gt;', $assignUrl, [
                    'class'       => 'btn btn-success btn-assign assign',
                    'data-target' => 'available',
                    'title'       => Yii::t('yii2mod.rbac', 'Assign'),
                ]); ?>
                <br/><br/>
                <?php echo Html::a('&lt;&lt;', $removeUrl, [
                    'class'       => 'btn btn-danger btn-assign remove',
                    'data-target' => 'assigned',
                    'title'       => Yii::t('yii2mod.rbac', 'Remove'),
                ]); ?>
            </div>
        </div>
        <div class="col-lg-5">
            <input class="form-control search" data-target="assigned"
                   placeholder="<?php echo Yii::t('yii2mod.rbac', 'Search for assigned'); ?>">
            <br/>
            <select multiple size="20" class="form-control list" data-target="assigned"></select>
        </div>
    </div>

<?php
$js = <<<JS
        $(function(){
            let orgId = $('#org');
            let assign = $('.assign');
            let remove = $('.remove');
            let hrefAssign = assign.attr('href');
            let hrefRemove = remove.attr('href');
            
            assign.removeAttr('href');
            remove.removeAttr('href');
            
            checkOrgId();
            orgId.on('change', function(){
                checkOrgId();
            });
            
            function checkOrgId(){
                if (orgId.val() > 0){
                    hrefAssign += '&orgId=' + orgId.val();
                    hrefRemove += '&orgId=' + orgId.val();
                    assign.attr('href', hrefAssign);
                    remove.attr('href', hrefRemove);
                    orgId.removeAttr('style');
                } else {
                    assign.removeAttr('href');
                    remove.removeAttr('href');
                    orgId.css('border','1px solid #f00');
                }
            }
            
            $('#orgList').on('change', function() {
                location.href = "{$rolesByOrg}" +'&orgId=' + this.value
            });
        });
JS;

$this->registerJs($js, View::POS_END);
?>