<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<td>
    <div class="guid_block_create_title">
        <p><?= $model["product"] ?></p>
    </div>	
    <div class="guid_block_create_counts">
        <p>Ед. измерения: <span><?= $model["ed"] ?></span></p> 
    </div>     
</td>
<td>
    <?php if (in_array($model["id"], $guideProductList)) { ?>
        <button class="btn btn-md btn-gray pull-right"><i class="fa fa-thumbs-o-up"></i> Продукт добавлен</button>
    <?php } else { ?>
        <button class="btn btn-md btn-success pull-right"><i class="fa fa-plus"></i> Добавить в гид</button> 
    <?php } ?>
</td>
