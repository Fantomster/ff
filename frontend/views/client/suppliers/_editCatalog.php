<?php
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Pjax;
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.client.supp.edit', ['ru'=>'Редактирование каталога']) ?></h4>
</div>
<div class="modal-body">
    <?php Pjax::begin(['enablePushState' => false,'timeout' => 10000, 'id' => 'pjax-edit-catalog'])?>
    <div class="handsontable" id="editCatalogSupplier"></div> 
    <?php Pjax::end(); ?>
</div>
<div class="modal-footer">
    <?= Html::button('<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.client.supp.save', ['ru'=>'Сохранить']), [
        'class' => 'btn btn-success',
        'id'=>'save-catalog-supplier',
        'data-loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.client.supp.saving', ['ru'=>'Сохраняем...']),
        ]) ?>
    <button class="btn btn-gray" data-dismiss="modal" id="btnClose"><span><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'frontend.views.client.supp.close', ['ru'=>'Закрыть']) ?></span></button>
</div>
<?php
$arr= $array;
$arr_count = count($array);

$editCatalogUrl = Url::to(['client/edit-catalog', 'id' => $id]);

$arr = [
    Yii::t('message', 'frontend.views.client.supp.var', ['ru'=>'Артикул']),
    Yii::t('message', 'frontend.views.client.supp.var1', ['ru'=>'Наименование товара']),
    Yii::t('message', 'frontend.views.client.supp.var2', ['ru'=>'Кратность']),
    Yii::t('message', 'frontend.views.client.supp.var3', ['ru'=>'Цена']),
    Yii::t('message', 'frontend.views.client.supp.var4', ['ru'=>'Ед. измерения']),
    Yii::t('message', 'frontend.views.client.supp.var5', ['ru'=>'Комментарий']),
    Yii::t('message', 'frontend.views.client.supp.var6', ['ru'=>'Закрыть!']),
    Yii::t('message', 'frontend.views.client.supp.var7', ['ru'=>'Окей!']),
];

$customJs = <<< JS
var data = $arr;
var container = document.getElementById('editCatalogSupplier');
var save = document.getElementById('save-catalog-supplier'), hot, originalColWidths = [], colWidths = [];         
hot = new Handsontable(container, {
removeRowPlugin: true,
data: JSON.parse(JSON.stringify(data)),
colHeaders : ['base_goods_id', 'goods_id', '$arr[0]', '$arr[1]', '$arr[2]', '$arr[3] (<span class="currency-symbol">{$catalogCurrency->symbol}</span>)', '$arr[4]', '$arr[5]'],
colWidths: [0, 0, 50, 60, 40, 30, 40, 60],
columns: [
        
    {
        data: 'base_goods_id',
        copyPaste: false
    },  
    {
        data: 'goods_id',
        copyPaste: false
    },
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
colWidths[0] = 0.1; colWidths[1] = 0.1;
        
hot.updateSettings({colWidths: colWidths});
function getRowsFromObjects(queryResult) {
    rows = [];
    for (var i = 0, l = queryResult.length; i < l; i++) {
      rows.push(queryResult[i].row);

    }
    console.log('rows', rows);
    return rows;
  }
Handsontable.Dom.addEvent(save, 'click', function() {
  var dataTable = hot.getData(),i, item, dataItem, datas=[]; 
  var cleanedData = {};
  var cols = ['base_goods_id','goods_id','article','product','units','price','ed','note'];
    
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
    $("#save-catalog-supplier").button("loading");
    $("#btnClose").prop( "disabled", true );
    $.ajax({
          url: "$editCatalogUrl",
          type: 'POST',
          dataType: "json",
          data: $.param({'catalog':JSON.stringify(datas)}),
          cache: false,
          success: function (response) {
              if(response.success){ 
                $("#save-catalog-supplier").button("reset");
                $("#btnClose").prop( "disabled", false );
                bootbox.dialog({
                    message: response.alert.body,
                    title: response.alert.title,
                    buttons: {
                        success: {
                          label: "$arr[6]",
                          className: "btn-success btn-md",
                          callback: function() {
                            
                          }
                        },
                    },
                    className: response.alert.class,
                });
              }else{
                $("#save-catalog-supplier").button("reset");
                $("#btnClose").prop( "disabled", false );
                bootbox.dialog({
                    message: response.alert.body,
                    title: response.alert.title,
                    buttons: {
                        success: {
                          label: "$arr[7]",
                          className: "btn-success btn-md",
                          callback: function() {
                            console.log("err");
                            }
                        },
                    },
                    className: response.alert.class,  
                });
              }
          },
          error: function(response) {
                $("#save-catalog-supplier").button("reset");
                $("#btnClose").prop( "disabled", false );
          console.log(response.message);
          }
    });
});
JS;
$this->registerJs($customJs, View::POS_READY);
?>
