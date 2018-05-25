<aside class="main-sidebar">

    <section class="sidebar">
        
<?php if (!Yii::$app->user->isGuest) { ?>
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p><?php echo Yii::$app->user->identity->profile->full_name; ?></p>

                <a href="#"><i class="fa fa-circle t ext-success"></i> Online</a>
            </div>
        </div>

        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->
<?php } ?>
        
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => 'Login', 'url' => ['/login'], 'visible' => Yii::$app->user->isGuest],
                    
                    ['label' => 'F-keeper API v 1.0', 'options' => ['class' => 'header']],
                    ['label' => '1C-suppliers','icon' => 'dashboard', 'url' => '/v1/supp'],
                    ['label' => 'R-keeper WS','icon' => 'dashboard', 'url' => '/v1/restor'],
                    ['label' => 'iiko cloud','icon' => 'dashboard', 'url' => '#'],
                    ['label' => 'Other SOAP','icon' => 'dashboard', 'url' => '#'],
                    
                    ['label' => 'F-keeper API v 2.0', 'options' => ['class' => 'header']],
                    ['label' => '1C-suppliers','icon' => 'dashboard', 'url' => '#'],
                    ['label' => 'R-keeper WS','icon' => 'dashboard', 'url' => '#'],
                    ['label' => 'iiko cloud','icon' => 'dashboard', 'url' => '#'],
                    ['label' => 'Other SOAP','icon' => 'dashboard', 'url' => '#'],
                    
                    ['label' => 'Stats', 'options' => ['class' => 'header']],
                    ['label' => 'Reports','icon' => 'circle-o', 'url' => '#'],
                    
                    ['label' => 'Other', 'options' => ['class' => 'header']],
                    ['label' => 'Gii', 'icon' => 'file-code-o', 'url' => ['/gii']],
                    ['label' => 'Debug', 'icon' => 'dashboard', 'url' => ['/debug']],
                ],
            ]
        ) ?>

    </section>

</aside>
