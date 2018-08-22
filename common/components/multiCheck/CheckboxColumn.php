<?php

namespace common\components\multiCheck;

use yii\helpers\Json;
use yii\web\JsExpression;

class CheckboxColumn extends \yii\grid\CheckboxColumn
{
    /**
     * @var array
     *  $onChangeEvents = [
            'changeAll' => 'function(e) { log("change"); }',
            'changeCell' => 'function(e) { log("change"); }',
            ];
     */
    public $onChangeEvents = [];

    /**
     * Registers check box events
     *
     * @param View $view The View object
     */
    protected function registerOnChangeEvents($view)
    {
        if (!empty($this->onChangeEvents)) {
            $js = [];
            foreach ($this->onChangeEvents as $event => $handler) {
                if(!empty($handler)) {
                    if(!$this->multiple && $event == 'changeAll')
                        continue;
                    $id = ($event == 'changeAll') ? "$('input[type=\"checkbox\"][name=\"" . $this->getHeaderCheckBoxName() . "\"]')"
                        : "$('input[type=\"checkbox\"][name=\"selection[]\"]')";
                    $function = new JsExpression($handler);
                    $js[] = "{$id}.on('change', {$function});";
                }
            }
            $js = implode("\n", $js);
            $view->registerJs($js);
        }
    }

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

        $this->registerOnChangeEvents($this->grid->getView());
    }
}