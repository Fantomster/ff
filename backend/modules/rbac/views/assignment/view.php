<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-05
 * Time: 13:29
 */

use yii\helpers\Html;
use yii\helpers\Json;
use yii2mod\rbac\RbacAsset;

RbacAsset::register($this);

/* @var $this yii\web\View */
/* @var $user */
/* @var $usernameField string */
/* @var $orgList array */

$userName = $user->{$usernameField};
$this->title = Yii::t('yii2mod.rbac', 'Assignment : {0}', $userName);
$this->params['breadcrumbs'][] = ['label' => Yii::t('yii2mod.rbac', 'Assignments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $userName;
$this->render('/layouts/_sidebar');
?>
<div class="assignment-index">

    <h1><?php echo Html::encode($this->title); ?></h1>

    <?php echo $this->render('../_assignListBox', [
        'opts'       => Json::htmlEncode([
            'items' => $items,
        ]),
        'assignUrl'  => ['assign', 'id' => $user->id],
        'removeUrl'  => ['remove', 'id' => $user->id],
        'rolesByOrg' => \yii\helpers\Url::to(['assignment/view', 'id' => $user->id]),
        'orgId'      => $orgId,
        'orgList'    => $orgList
    ]); ?>

</div>