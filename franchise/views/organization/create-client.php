<?php

$organizationType = \common\models\Organization::TYPE_RESTAURANT;
$organization = $client;
echo $this->render('_organization-form', compact('organization', 'user', 'profile', 'buisinessInfo', 'organizationType'));