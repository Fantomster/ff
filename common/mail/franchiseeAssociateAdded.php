<br style="margin: 0; padding: 0;"/>
<div style="width: 100%; margin: 0; padding: 20px;">
    <h2>Название организации: <?= $organization->name ?></h2>
    <ul>
        <li>
            Адрес: <?= $organization->address ?>
        </li>
        <li>
            Телефон: <?= $organization->phone ?>
        </li>
        <li>
            Тип организации: <?= $organization->type->name ?>
        </li>
        <li>
            Ссылка на просмотр: <a href="<?= $route ?>"
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
                Перейти
            </a>
        </li>
    </ul>
</div>