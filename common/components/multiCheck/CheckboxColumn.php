<?php

namespace common\components\multiCheck;

use yii\helpers\Json;
use yii\web\JsExpression;

class CheckboxColumn extends \kartik\grid\CheckboxColumn

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
            $header = '$(document)';
            foreach ($this->onChangeEvents as $event => $handler) {
                if(!empty($handler)) {
                    if(!$this->multiple && $event == 'changeAll')
                        continue;
                    $id = ($event == 'changeAll') ? "'input[type=\"checkbox\"][name=\"" . $this->getHeaderCheckBoxName() . "\"]'"
                        : "'input[type=\"checkbox\"][name=\"selection[]\"]'";
                    $function = new JsExpression($handler);
                    $js[] = "{$header}.on('change', {$id}, {$function});";
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
            $js = " //function initSelectedAll(){
            jQuery('#$id').yiiGridView('setSelectionColumn', $options);
            countSelected = $('#$id').yiiGridView('getSelectedRows');
            checked = (countSelected.length == $pageCount);
            console.log(countSelected.length);
            console.log($pageCount);
            $('input[type=\"checkbox\"][name=\"" . $this->getHeaderCheckBoxName() . "\"]').attr(\"disabled\", true);
            $('input[type=\"checkbox\"][name=\"" . $this->getHeaderCheckBoxName() . "\"]').prop(\"checked\", checked);
            $('input[type=\"checkbox\"][name=\"" . $this->getHeaderCheckBoxName() . "\"]').removeAttr(\"disabled\"); 
           // }
           // initSelectedAll();
            ";
            $this->grid->getView()->registerJs($js);
            $this->_clientScript .= "\n".$js;
        }

        $this->registerOnChangeEvents($this->grid->getView());
    }
}