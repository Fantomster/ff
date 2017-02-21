<?php

$organizationType = \common\models\Organization::TYPE_SUPPLIER;
$organization = $vendor;
echo $this->render('_organization-form', compact('organization', 'user', 'profile', 'buisinessInfo', 'organizationType'));