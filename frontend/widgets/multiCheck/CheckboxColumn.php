<?php

namespace frontend\widgets\multiCheck;

use yii\helpers\Json;

class CheckboxColumn extends \yii\grid\CheckboxColumn
{
    public function registerClientScript()
    {
        $id = $this->grid->options['id'];
        $options = Json::encode([
            'name' => $this->name,
            'class' => $this->cssClass,
            'multiple' => $this->multiple,
            'checkAll' => $this->grid->showHeader ? $this->getHeaderCheckBoxName() : null,
        ]);

        if(!$this->grid->showHeader){
        $this->grid->getView()->registerJs("
        jQuery('#$id').yiiGridView('setSelectionColumn', $options);
        ");
        }
        else {
            $this->grid->getView()->registerJs("
            jQuery('#$id').yiiGridView('setSelectionColumn', $options);
            countSelected = $('#$id').yiiGridView('getSelectedRows');
            $('input[type=\"checkbox\"][name=\"" . $this->getHeaderCheckBoxName() . "\"]')").prop("checked", false).change();
        }
    }
}