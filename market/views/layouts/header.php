<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

?>
<section>
    <nav class="navbar navbar-inverse navbar-static-top example6 shadow-bottom">
        <div class="container" style="padding: 9px 30px">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar6">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand text-hide" href="<?=Url::home();?>">f-keeper</a>
          </div>
          <div id="navbar6" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
              <li class="active"><a href="<?=Url::home();?>">ГЛАВНАЯ</a></li>
              <li><a href="#">О&nbsp;НАС</a></li>
              <li><a href="#">КОНТАКТЫ</a></li>
              <li><a class="btn-navbar" href="#">войти / регистрация</a></li>
            </ul>
          </div>
          <!--/.nav-collapse -->
        </div>
        <!--/.container -->
    </nav>
</section> 
