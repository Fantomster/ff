<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<td>
    <div class="guid_block_create_title">
        <p><?= $model->vendor->name ?></p>
    </div>	
    <div class="guid_block_create_counts">
        <p>Кол-во товаров: <span><?= $model->vendor->productsCount ?></span></p> 
    </div>     
</td>
<td>
    <?php if ($model->supp_org_id == $selectedVendor) { ?>
        <button class="btn btn-md btn-gray pull-right"><i class="fa fa-thumbs-o-up"></i> Выбран</button>
    <?php } else { ?>
        <button class="btn btn-md btn-success pull-right"><i class="fa fa-hand-pointer-o"></i> Выбрать</button>  
    <?php } ?>
</td>