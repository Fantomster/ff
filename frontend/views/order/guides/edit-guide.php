<?php
use yii\helpers\Url;
use yii\widgets\ListView;
use yii\widgets\Pjax;
?>
<section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="<?= Url::to(['order/create']) ?>">Все продукты</a></li>
            <li class="active">
                <a href="#">
                    Гайды заказов <small class="label bg-yellow">new</small>
                </a>
            </li>
            <li>
                <a href="<?= Url::to(['order/favorites']) ?>">
                    Избранные <small class="label bg-yellow">new</small>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <div class="guid_table_block">
                                <div class="guid_table_block_title">
                                    <div class="guid_block_title_r pull-left">Выберите поставщика</div>
                                    <div class="guid_block_title_l pull-right">ШАГ 1</div>
                                </div>
                                <table class="table table-hover">
                                    <tbody>
                                        <tr>
                                            <th colspan="2">
                                                <div class="form-group">
                                                    <div class="icon-addon addon-md">
                                                        <input type="text" placeholder="Поиск среди ваших поставщиков" class="form-control" id="email">
                                                        <label for="email" class="glyphicon glyphicon-search" rel="tooltip" title="email"></label>
                                                    </div>
                                                </div>  
                                            </th>
                                        </tr>
                                        <?php
                                        Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'id' => 'vendorsList', 'timeout' => 30000]);
                                        ?>
                                        <?=
                                        ListView::widget([
                                            'dataProvider' => $vendorDataProvider,
                                            'itemView' => '_vendor-view',
                                            'itemOptions' => [
                                                'tag' => 'tr',
                                            ],
                                            'pager' => [
                                                'maxButtonCount' => 5,
                                                'options' => [
                                                    'class' => 'pagination col-md-12  no-padding'
                                                ],
                                            ],
                                            'options' => [
                                                'class' => 'col-lg-12 list-wrapper inline no-padding'
                                            ],
                                            'layout' => "\n{items}\n<div class='pull-left'>{pager}</div><div class='pull-right summary-pages'>{summary}</div>",
                                            'summary' => '',
                                            'emptyText' => 'Список пуст',
                                        ])
                                        ?>
                                        <?php Pjax::end(); ?>
                                    </tbody>
                                </table>
<!--                                <ul class="pagination">
                                    <li class="prev disabled"><span>«</span></li>
                                    <li class="active"><a href="#">1</a></li>
                                    <li><a href="#">2</a></li>
                                    <li><a href="#">3</a></li>
                                </ul>-->
                            </div>   
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="guid_table_block">
                                <div class="guid_table_block_title">
                                    <div class="guid_block_title_r pull-left">Выберите его продукт</div>
                                    <div class="guid_block_title_l pull-right">ШАГ 2</div>
                                </div>
                                <table class="table table-hover">
                                    <tbody><tr>
                                            <th colspan="2">
                                                <div class="form-group">
                                                    <div class="icon-addon addon-md">
                                                        <input type="text" placeholder="Поиск по продуктам выбранного поставщика" class="form-control" id="email">
                                                        <label for="email" class="glyphicon glyphicon-search" rel="tooltip" title="email"></label>
                                                    </div>
                                                </div>  
                                            </th>
                                        </tr>
                                        <tr class="active">
                                            <td>
                                                <div class="guid_block_create_title">
                                                    <p>Какой-то продукт</p>
                                                </div>	
                                                <div class="guid_block_create_counts">
                                                    <p>Ед. измерения: <span>кг</span></p> 
                                                </div>     
                                            </td>
                                            <td>
                                                <button class="btn btn-md btn-gray pull-right"><i class="fa fa-thumbs-o-up"></i> Продукт добавлен</button>        
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="guid_block_create_title">
                                                    <p>Какой-то продукт</p>
                                                </div>	
                                                <div class="guid_block_create_counts">
                                                    <p>Ед. измерения: <span>кг</span></p> 
                                                </div>     
                                            </td>
                                            <td>
                                                <button class="btn btn-md btn-success pull-right"><i class="fa fa-plus"></i> Добавить в гид</button>        
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="guid_block_create_title">
                                                    <p>Какой-то продукт</p>
                                                </div>	
                                                <div class="guid_block_create_counts">
                                                    <p>Ед. измерения: <span>кг</span></p> 
                                                </div>     
                                            </td>
                                            <td>
                                                <button class="btn btn-md btn-success pull-right"><i class="fa fa-plus"></i> Добавить в гид</button>        
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="guid_block_create_title">
                                                    <p>Какой-то продукт</p>
                                                </div>	
                                                <div class="guid_block_create_counts">
                                                    <p>Ед. измерения: <span>кг</span></p> 
                                                </div>     
                                            </td>
                                            <td>
                                                <button class="btn btn-md btn-success pull-right"><i class="fa fa-plus"></i> Добавить в гид</button>        
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="guid_block_create_title">
                                                    <p>Какой-то продукт</p>
                                                </div>	
                                                <div class="guid_block_create_counts">
                                                    <p>Ед. измерения: <span>кг</span></p> 
                                                </div>     
                                            </td>
                                            <td>
                                                <button class="btn btn-md btn-success pull-right"><i class="fa fa-plus"></i> Добавить в гид</button>        
                                            </td>
                                        </tr>
                                    </tbody></table>
                                <ul class="pagination">
                                    <li class="prev disabled"><span>«</span></li>
                                    <li class="active"><a href="#">1</a></li>
                                    <li><a href="#">2</a></li>
                                    <li><a href="#">3</a></li>
                                    <li><a href="#">4</a></li>
                                    <li class="next"><a href="#">»</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-4">
                            <div class="guid_table_block">
                                <div class="guid_table_block_title">
                                    <div class="guid_block_title_r pull-left">Гид: Помидорки и укропчег</div>
                                    <div class="guid_block_title_l pull-right">ШАГ 3</div>
                                </div> 
                                <table class="table table-hover">
                                    <tbody><tr>
                                            <th colspan="2">
                                                <div class="form-group">
                                                    <div class="icon-addon addon-md">
                                                        <input type="text" placeholder="Поиск по набранному гиду" class="form-control" id="gids">
                                                        <label for="gids" class="glyphicon glyphicon-search" rel="tooltip" title="email"></label>
                                                    </div>
                                                </div>  
                                            </th>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="guid_block_create_title">
                                                    <p>Какой-то продукт</p>
                                                </div>	
                                                <div class="guid_block_create_counts">
                                                    <p>bcpostavshik | Bcrestaran</p>  
                                                </div>     
                                            </td>
                                            <td>
                                                <a class="btn btn-md btn-outline-danger pull-right"><i class="fa fa-trash"></i></a>        
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="guid_block_create_title">
                                                    <p>Какой-то продукт</p>
                                                </div>	
                                                <div class="guid_block_create_counts">
                                                    <p>bcpostavshik | Bcrestaran</p> 
                                                </div>     
                                            </td>
                                            <td>
                                                <a class="btn btn-md btn-outline-danger pull-right"><i class="fa fa-trash"></i></a>        
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="guid_block_create_title">
                                                    <p>Какой-то продукт</p>
                                                </div>	
                                                <div class="guid_block_create_counts">
                                                    <p>bcpostavshik | Bcrestaran</p> 
                                                </div>     
                                            </td>
                                            <td>
                                                <a class="btn btn-md btn-outline-danger pull-right"><i class="fa fa-trash"></i></a>        
                                            </td>
                                        </tr>
                                    </tbody></table>
                                <button class="btn btn-md btn-success guid-save"><i class="fa fa-save"></i> Сохранить</button>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>            
        </div>
    </div>
</section>