<?php
 foreach ($vendors as $vendor) { ?>
<a href="#" class="list-group-item vendor<?=$vendor['selected'] ? ' active' : ''?>" data-id="<?=$vendor['id']?>" data-selected="<?=$vendor['selected']?>"><?=$vendor['name']?></a>
 <?php } ?>