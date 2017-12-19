<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
?>
<section class="content">

    <div class="error-page">
        <h2 class="headline text-info"><i class="fa fa-warning text-yellow"></i></h2>

        <div class="error-content">
            <h3><?= $name ?></h3>

            <p>
                <?= nl2br(Html::encode($message)) ?>
            </p>

            <p>
                <?= Yii::t('app', 'franchise.views.site.error', ['ru'=>'The above error occurred while the Web server was processing your request.
                Please contact us if you think this is a server error. Thank you.']) ?>
                <?= Yii::t('app', 'franchise.views.site.meanwhile', ['ru'=>'Meanwhile, you may']) ?> <a href='<?= Yii::$app->homeUrl ?>'><?= Yii::t('app', 'franchise.views.site.return', ['ru'=>'return to dashboard']) ?></a> <?= Yii::t('app', 'franchise.views.site.or_try', ['ru'=>'or try using the search form.']) ?>
            </p>

            <form class='search-form'>
                <div class='input-group'>
                    <input type="text" name="search" class='form-control' placeholder="<?= Yii::t('app', 'franchise.views.site.search_two', ['ru'=>'Search']) ?>"/>

                    <div class="input-group-btn">
                        <button type="submit" name="submit" class="btn btn-primary"><i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</section>
