<?php foreach ($ediOrganizations as $id => $name): ?>
    <?php
    if ($name == null) continue;
    ?>
    <div class="row">
        <div class="col-md-3">
            <div class="checkbox">
                <?php $checked = (isset($checkedOrganizations) && in_array($id, $checkedOrganizations)) ? true : false; ?>
                <?= \yii\helpers\Html::checkbox('organizations[]', $checked, [
                    'value' => $id,
                    'label' => $name,
                    'class' => 'checkbox',
                ]);
                ?>
            </div>
        </div>
    </div>
    <hr>
<?php endforeach; ?>