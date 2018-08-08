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

        $pageCount = $this->grid->dataProvider->getCount();

        if (!$this->grid->showHeader) {
            $this->grid->getView()->registerJs("
        jQuery('#$id').yiiGridView('setSelectionColumn', $options);
        ");
        } else {
            $this->grid->getView()->registerJs("
            jQuery('#$id').yiiGridView('setSelectionColumn', $options);
            countSelected = $('#$id').yiiGridView('getSelectedRows');
            checked = (countSelected == $pageCount);
            $('input[type=\"checkbox\"][name=\"" . $this->getHeaderCheckBoxName() . "\"]').prop(\"checked\", checked);
            ");
        }
    }
}