<br style="margin: 0; padding: 0;"/>
<div style="width: 100%; margin: 0; padding: 20px;">
    <h2><?= Yii::t('app', 'common.mail.franchisee_associate_added.org_name', ['ru'=>'Название организации:']) ?> <?= $organization->name ?></h2>
    <ul>
        <li>
            <?= Yii::t('app', 'common.mail.franchisee_associate_added.address', ['ru'=>'Адрес:']) ?> <?= $organization->address ?>
        </li>
        <li>
            <?= Yii::t('app', 'common.mail.franchisee_associate_added.phone', ['ru'=>'Телефон:']) ?> <?= $organization->phone ?>
        </li>
        <li>
            <?= Yii::t('app', 'common.mail.franchisee_associate_added.org_type', ['ru'=>'Тип организации:']) ?> <?= $organization->type->name ?>
        </li>
        <li>
            <?= Yii::t('app', 'common.mail.franchisee_associate_added.link', ['ru'=>'Ссылка на просмотр:']) ?> <a href="<?= $route ?>"
                                   style="text-decoration: none;
                                            color: #FFF;
                                            background-color: #84bf76;
                                            padding: 10px 16px;
                                            font-weight: bold;
                                            margin-right: 10px;
                                            text-align: center;
                                            cursor: pointer;
                                            display: inline-block;
                                            border-radius: 4px;
                                            width: 80%;">
                <?= Yii::t('app', 'common.mail.franchisee_associate_added.go', ['ru'=>'Перейти']) ?>
            </a>
        </li>
    </ul>
</div>