<?php

use yii\helpers\Url;
use yii\web\View;
use yii\helpers\Html;

?>
<div class="modal-dialog nav-tabs-custom" style="border-radius: 3px;">
    <ul class="nav nav-tabs">
        <!--              <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Поставщик</a></li>-->
        <!--              <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">Контактное лицо</a></li>-->
        <!--              <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">Реквизиты</a></li>-->
    </ul>
    <div class="modal-content tab-content" style="box-shadow: 0 2px 3px rgba(0,0,0,0.125);">
        <div class="modal-header" style="background-color: #fff; border-bottom: 1px solid #e5e5e5; color: #33363b;">
            <h4 class="modal-title" style="text-align: left;"><?= $vendor->name ?></h4>

        </div>
        <div class="modal-body tab-pane active" id="tab_1">
            <div class="row">
                <!--<div class="col-md-4">
                    <img width="163" height="100" src="<?= $vendor->pictureUrl ?>">
                    <div class="btn-edite">
                      <? //= isset($catalog->id) ? Html::a('Базовый прайс-лист',['site/catalog','id'=>$catalog->id],['class'=>'btn btn-green btn-block']) : ''?>
                      <button type="button" class="btn btn-strip-green btn-block">Аналитика</button>
                    </div>
                  </div>-->
                <div class="col-md-12">
                    <div class="edite-place">
                        <div class="form-group">
                            <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.vendor_name', ['ru'=>'Название поставщика:']) ?></label>
                            <p><?= $vendor->name ?></p>
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.contact_phone_two', ['ru' => 'Телефон контактного лица:']) ?></label>
                            <p><?= $vendor->phone ?></p>
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.contact_email_two', ['ru' => 'E-mail контактного лица:']) ?></label>
                            <p><?= $vendor->email ?></p>
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.contact_fio_two', ['ru' => 'ФИО контактного лица:']) ?></label>
                            <p><?= $vendor->contact_name ?></p>
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.city_two', ['ru'=>'Город:']) ?></label>
                            <p><?= $vendor->city ?></p>
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.address_two', ['ru'=>'Адрес:']) ?></label>
                            <p><?= $vendor->address ?></p>
                        </div>

                        <!--<div class="form-group">
                            <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.short_info_two', ['ru'=>'Краткая информация:']) ?></label>
                            <p><?//= $vendor->about ?></p>
                        </div>-->

                    </div>
                </div>
            </div>
        </div>
        <!--<div class="modal-body tab-pane" id="tab_2">
                <div class="row">
                  <div class="col-md-4">
                    <img width="163" height="100" src="<?= $vendor->pictureUrl ?>">
                    <div class="btn-edite">
                        <? //= isset($catalog->id) ? Html::a('Базовый прайс-лист',['site/catalog','id'=>$catalog->id],['class'=>'btn btn-green btn-block']) : ''?>
                      <a href="" class="btn btn-strip-green btn-block">Аналитика</a>
                    </div>
                  </div>
                  <div class="col-md-8">
                    <div class="edite-place">
                      <div class="form-group">
                        <label for="exampleInputEmail1">ФИО контактного лица:</label>
                        <p><?= $vendor->contact_name ?></p>
                      </div>
                      
                      <div class="form-group">
                        <label for="exampleInputEmail1">E-mail контактного лица:</label>
                        <p><?= $vendor->email ?></p>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Телефон контактного лица:</label>
                        <p><?= $vendor->phone ?></p>
                      </div>
                      
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-body tab-pane" id="tab_3">
                  <label>Процент с оборота: </label>
                  <p><?= $vendor->buisinessInfo->reward ?></p>
                  <label>Подписант: </label>
                  <p><?= $vendor->buisinessInfo->signed ?></p>
                  <label>Юридическое название: </label>
                  <p><?= $vendor->buisinessInfo->legal_entity ?></p>
                  <label>Юридический адрес: </label>
                  <p><?= $vendor->buisinessInfo->legal_address ?></p>
                  <label>Официальный email: </label>
                  <p><?= $vendor->buisinessInfo->legal_email ?></p>
                  <label>ИНН: </label>
                  <p><?= $vendor->buisinessInfo->inn ?></p>
                  <label>КПП: </label>
                  <p><?= $vendor->buisinessInfo->kpp ?></p>
                  <label>ОГРН: </label>
                  <p><?= $vendor->buisinessInfo->ogrn ?></p>
                  <label>Банк: </label>
                  <p><?= $vendor->buisinessInfo->bank_name ?></p>
                  <label>БИК: </label>
                  <p><?= $vendor->buisinessInfo->bik ?></p>
                  <label>Телефон: </label>
                  <p><?= $vendor->buisinessInfo->phone ?></p>
                  <label>р/с: </label>
                  <p><?= $vendor->buisinessInfo->correspondent_account ?></p>
                  <label>к/с: </label>
                  <p><?= $vendor->buisinessInfo->checking_account ?></p>
                  <label>Поле для заметок: </label>
                  <p><?= $vendor->buisinessInfo->info ?></p>
              </div>-->
        <div class="modal-footer" style="background-color: #fff; border-top: 1px solid #e5e5e5;">
            <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i
                        class="fa fa-fw fa-close"></i> <?= Yii::t('app', 'franchise.views.organization.close_two', ['ru'=>'Закрыть']) ?>
            </button>
            <?= Html::a('<i class="fa fa-fw fa-mail-forward"></i> ' . Yii::t('app', 'franchise.views.organization.watch_two', ['ru'=>'Просмотр']) . ' ', ['organization/show-vendor', 'id' => $vendor->id], ['class' => 'btn btn-info']) ?>
            <?php if ($showEditButton): ?>
                <?= Html::a('<i class="fa fa-fw fa-pencil"></i> ' . Yii::t('app', 'franchise.views.organization.edit_two', ['ru'=>'Редактировать']) . ' ', ['organization/update-vendor', 'id' => $vendor->id], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- /.modal-content -->
</div>