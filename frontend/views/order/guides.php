<?php
use yii\helpers\Url;
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
                    <div class="guid-header">
                        <div class="pull-left">
                            <div class="form-group">
                                <div class="icon-addon addon-md">
                                    <input type="text" placeholder="Поиск по названию" class="form-control" id="email">
                                    <label for="email" class="glyphicon glyphicon-search" rel="tooltip" title="email"></label>
                                </div>
                            </div> 
                        </div>
                        <div class="pull-right">
                            <a class="btn btn-md btn-outline-success new-guid" href="create.html" data-toggle="tooltip" data-original-title="Создать гайд" data-url="#"><i class="fa fa-plus"></i> Создать гайд</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <hr>	
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 guid">
                    <div class="guid_block">
                        <div class="guid_block_title">
                            <p>Название гайда</p>
                        </div>	
                        <div class="guid_block_comment">
                            <p>Комментарий: <span>какой-то комментарий к гайду</span></p> 
                        </div>
                        <div class="guid_block_counts">
                            <p>Кол-во товаров: <span>125</span></p> 
                        </div>
                        <div class="guid_block_updated">
                            <p>Изменен: <span>25 июля 2017</span></p> 
                        </div>
                        <div class="guid_block_buttons">
                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> Удалить</button>
                            <button class="btn btn-sm btn-outline-default"><i class="fa fa-pencil"></i> Редактировать</button> 
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-to-cart"><i class="fa fa-shopping-cart"></i> В корзину</button>  
                        </div>
                    </div> 

                    <div class="guid_block">
                        <div class="guid_block_title">
                            <p>Название гайда</p>
                        </div>	
                        <div class="guid_block_comment">
                            <p>Комментарий: <span>какой-то комментарий к гайду</span></p> 
                        </div>
                        <div class="guid_block_counts">
                            <p>Кол-во товаров: <span>125</span></p> 
                        </div>
                        <div class="guid_block_updated">
                            <p>Изменен: <span>25 июля 2017</span></p> 
                        </div>
                        <div class="guid_block_buttons">
                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> Удалить</button>
                            <button class="btn btn-sm btn-outline-default"><i class="fa fa-pencil"></i> Редактировать</button> 
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-to-cart"><i class="fa fa-shopping-cart"></i> В корзину</button>  
                        </div>
                    </div>

                    <div class="guid_block">
                        <div class="guid_block_title">
                            <p>Название гайда</p>
                        </div>	
                        <div class="guid_block_comment">
                            <p>Комментарий: <span>какой-то комментарий к гайду</span></p> 
                        </div>
                        <div class="guid_block_counts">
                            <p>Кол-во товаров: <span>125</span></p> 
                        </div>
                        <div class="guid_block_updated">
                            <p>Изменен: <span>25 июля 2017</span></p> 
                        </div>
                        <div class="guid_block_buttons">
                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> Удалить</button>
                            <button class="btn btn-sm btn-outline-default"><i class="fa fa-pencil"></i> Редактировать</button> 
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-to-cart"><i class="fa fa-shopping-cart"></i> В корзину</button>  
                        </div>
                    </div>

                    <div class="guid_block">
                        <div class="guid_block_title">
                            <p>Название гайда</p>
                        </div>	
                        <div class="guid_block_comment">
                            <p>Комментарий: <span>какой-то комментарий к гайду</span></p> 
                        </div>
                        <div class="guid_block_counts">
                            <p>Кол-во товаров: <span>125</span></p> 
                        </div>
                        <div class="guid_block_updated">
                            <p>Изменен: <span>25 июля 2017</span></p> 
                        </div>
                        <div class="guid_block_buttons">
                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> Удалить</button>
                            <button class="btn btn-sm btn-outline-default"><i class="fa fa-pencil"></i> Редактировать</button> 
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-to-cart"><i class="fa fa-shopping-cart"></i> В корзину</button>  
                        </div>
                    </div>

                    <div class="guid_block">
                        <div class="guid_block_title">
                            <p>Название гайда</p>
                        </div>	
                        <div class="guid_block_comment">
                            <p>Комментарий: <span>какой-то комментарий к гайду</span></p> 
                        </div>
                        <div class="guid_block_counts">
                            <p>Кол-во товаров: <span>125</span></p> 
                        </div>
                        <div class="guid_block_updated">
                            <p>Изменен: <span>25 июля 2017</span></p> 
                        </div>
                        <div class="guid_block_buttons">
                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> Удалить</button>
                            <button class="btn btn-sm btn-outline-default"><i class="fa fa-pencil"></i> Редактировать</button> 
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-to-cart"><i class="fa fa-shopping-cart"></i> В корзину</button>  
                        </div>
                    </div>

                    <div class="guid_block">
                        <div class="guid_block_title">
                            <p>Название гайда</p>
                        </div>	
                        <div class="guid_block_comment">
                            <p>Комментарий: <span>какой-то комментарий к гайду</span></p> 
                        </div>
                        <div class="guid_block_counts">
                            <p>Кол-во товаров: <span>125</span></p> 
                        </div>
                        <div class="guid_block_updated">
                            <p>Изменен: <span>25 июля 2017</span></p> 
                        </div>
                        <div class="guid_block_buttons">
                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> Удалить</button>
                            <button class="btn btn-sm btn-outline-default"><i class="fa fa-pencil"></i> Редактировать</button> 
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-to-cart"><i class="fa fa-shopping-cart"></i> В корзину</button>  
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</section>