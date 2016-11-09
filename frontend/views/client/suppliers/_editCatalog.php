<?php
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Редактирование каталога</h4>
</div>
<div class="modal-body">
    <div class="handsontable" id="editCatalogSupplier"></div> 
</div>
<div class="modal-footer">
    <?= Html::button('<i class="icon fa fa-save"></i> Сохранить', ['class' => 'btn btn-success','id'=>'save-catalog-supplier']) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> Закрыть</a>
</div>
<?php
$arr= json_encode($array, JSON_UNESCAPED_UNICODE);
$arr_count = count($array);
$customJs = <<< JS
var data = $arr;
var container = document.getElementById('editCatalogSupplier');
var save = document.getElementById('save-catalog-supplier'), hot, originalColWidths = [], colWidths = [];         
hot = new Handsontable(container, {
data: JSON.parse(JSON.stringify(data)),
colHeaders : ['Артикул', 'Наименование товара', 'Кратность', 'Цена (руб)', 'Ед. измерения', 'Комментарий'],
colWidths: [40, 60, 40, 30, 40, 60],
columns: [
    {
        data: 'article'
    },
    {
        data: 'product', wordWrap:true
    },
    {
        data: 'units', 
        type: 'numeric',
        format: '0.00',
        language: 'ru-RU'
    },
    {
        data: 'price', 
        type: 'numeric',
        format: '0.00',
        language: 'ru-RU'
    },
    {data: 'ed', allowEmpty: false},
    {data: 'note', wordWrap:true}
],
className : 'Handsontable_table',
rowHeaders : true,
renderAllRows: true,
stretchH : 'all',
minSpareRows: 1,
Controller: true,
tableClassName: ['table-hover']
});  
Handsontable.Dom.addEvent(save, 'click', function() {
  var dataTable = hot.getData(),i, item, dataItem, datas=[]; 
  var cleanedData = {};
  var cols = ['article','product','units','price','ed','note'];
    
    $.each(dataTable, function( rowKey, object) {
        if (!hot.isEmptyRow(rowKey)){
            cleanedData[rowKey] = object;
            dataItem = {};
            for(i = 0; i < cols.length; i+=1) {
              item = cleanedData[rowKey][i];
                dataItem[cols[i]] = item;
            }
            datas.push({dataItem});
        }    
    });
    $('#loader-show').showLoading();
    $.ajax({
          url: "index.php?r=client/edit-catalog&id=$id",
          type: 'POST',
          dataType: "json",
          data: $.param({'catalog':JSON.stringify(datas)}),
          cache: false,
          success: function (response) {
              if(response.success){ 
                $('#loader-show').hideLoading();
                bootbox.dialog({
                    message: response.alert.body,
                    title: response.alert.title,
                    buttons: {
                        success: {
                          label: "Закрыть!",
                          className: "btn-success btn-md",
                        },
                    },
                    className: response.alert.class
                });
              }else{
                $('#loader-show').hideLoading();
                bootbox.dialog({
                    message: response.alert.body,
                    title: response.alert.title,
                    buttons: {
                        success: {
                          label: "Окей!",
                          className: "btn-success btn-md",
                        },
                    },
                    className: response.alert.class
                });
              }
          },
          error: function(response) {
            $('#loader-show').hideLoading();
          console.log(response.message);
          }
    });
});
JS;
$this->registerJs($customJs, View::POS_READY);
?>
