<div class="modal-dialog nav-tabs-custom" style="border-radius: 3px;">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Ресторан</a></li>
              <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">Контактное лицо</a></li>
              <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">Реквизиты</a></li>
            </ul>
            <div class="modal-content tab-content" style="box-shadow: 0 2px 3px rgba(0,0,0,0.125);">
              <div class="modal-header" style="background-color: #fff; border-bottom: 1px solid #e5e5e5; color: #33363b;">
                <h4 class="modal-title" style="text-align: left;"><?= $client->name ?></h4>
                
              </div>
              <div class="modal-body tab-pane active" id="tab_1">
                <div class="row">
                  <div class="col-md-4">
                      <img width="163" height="100" src="<?= $client->pictureUrl ?>">
                    <div class="btn-edite">
                      <!-- <button type="button" class="btn btn-strip-green btn-block">Аналитика</button> -->
                    </div>
                  </div>
                  <div class="col-md-8">
                    <div class="edite-place">
                      <div class="form-group">
                        <label for="exampleInputEmail1">Название ресторана:</label>
                        <p><?= $client->name ?></p>
                      </div>
                      
                      <div class="form-group">
                        <label for="exampleInputEmail1">Название юр. лица:</label>
                        <p><?= $client->legal_entity ?></p>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Город:</label>
                        <p><?= $client->city ?></p>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Адрес:</label>
                        <p><?= $client->address ?></p>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Краткая информация:</label>
                        <p><?= $client->about ?></p>
                      </div>
                      
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-body tab-pane" id="tab_2">
                <div class="row">
                  <div class="col-md-4">
                    <img width="163" height="100" src="<?= $client->pictureUrl ?>">
                    <div class="btn-edite">
                      <button type="button" class="btn btn-strip-green btn-block">Аналитика</button>
                    </div>
                  </div>
                  <div class="col-md-8">
                    <div class="edite-place">
                      <div class="form-group">
                        <label for="exampleInputEmail1">ФИО контактного лица:</label>
                        <p><?= $client->contact_name ?></p>
                      </div>
                      
                      <div class="form-group">
                        <label for="exampleInputEmail1">E-mail контактного лица:</label>
                        <p><?= $client->email ?></p>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Телефон контактного лица:</label>
                        <p><?= $client->phone ?></p>
                      </div>
                      
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-body tab-pane" id="tab_3">
                <p>One fine body… 3 </p>
              </div>
              <div class="modal-footer" style="background-color: #fff; border-top: 1px solid #e5e5e5;">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-fw fa-close"></i> Закрыть</button>
                <button type="button" class="btn btn-primary"><i class="fa fa-fw fa-pencil"></i> Редактировать</button>
                
              </div>
            </div>
            <!-- /.modal-content -->
          </div>