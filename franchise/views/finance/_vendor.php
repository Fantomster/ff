<div class="">
    <div class="box-header with-border">
        <h4 class="box-title">
            <a class="collapse-href" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="true">
                <span class="line-before"></span><?= Yii::t('app', 'franchise.views.finance.total_income_four', ['ru'=>'Общий доход по поставщику']) ?> <span class="arrow-open"><i class="fa fa-fw fa-sort-desc pull-right"></i></span>
            </a>
        </h4>
    </div>
    <div id="collapseTwo" class="panel-collapse collapse in" aria-expanded="true">
        <div class="box-body">
            <div class="pay-chek">
                <table class="pay-table" width="100%">
                    <tbody><tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.mix_two', ['ru'=>'MixCart Мне']) ?>:</td>
                            <td style="text-align: right; font-size: 18px;">123000 <?= Yii::t('app', 'franchise.views.finance.rouble_five', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.me_mix_two', ['ru'=>'Я MixCart\'у:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_six', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.month_quan', ['ru'=>'Кол-во месяцев:']) ?></td>
                            <td style="text-align: right; font-size: 18px;"><?= Yii::t('app', 'franchise.views.finance.three', ['ru'=>'3 месяцев']) ?></td>
                        </tr>
                        <tr style="border-top: 1px dotted rgba(51, 54, 59, 0.1);">
                            <td style="text-align: left; font-weight: bold;"><?= Yii::t('app', 'franchise.views.finance.income_from_vendor', ['ru'=>'Доход за от поставщика:']) ?></td>
                            <td style="text-align: right; font-size: 22px;">328462389 <?= Yii::t('app', 'franchise.views.finance.rouble_seven', ['ru'=>'руб.']) ?></td>
                        </tr>
                    </tbody></table>
            </div>
        </div>
    </div>
</div>
<div class="">
    <div class="box-header with-border">
        <h4 class="box-title">
            <a class="collapse-href collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false">
                <span class="line-before"></span><?= Yii::t('app', 'franchise.views.finance.detail', ['ru'=>'Детализация']) ?> <span class="arrow-open"><i class="fa fa-fw fa-sort-desc pull-right"></i></span>
            </a>
        </h4>
    </div>
    <div id="collapseThree" class="panel-collapse collapse" aria-expanded="false">
        <div class="box-body">
            <div class="pay-chek">
                <table class="pay-table" width="100%">
                    <tbody><tr>
                            <th><?= Yii::t('app', 'franchise.views.finance.turnover', ['ru'=>'Оборот']) ?></th>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.vendors_turnover', ['ru'=>'Оборот поставщика:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">123000 <?= Yii::t('app', 'franchise.views.finance.rouble_eight', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.debet', ['ru'=>'Начислено:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_nine', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.me_to_mix', ['ru'=>'MixCart\'у:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_ten', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.my_turnover_income', ['ru'=>'Мой доход с оборота:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_eleven', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <th><?= Yii::t('app', 'franchise.views.finance.fix_part', ['ru'=>'Фиксированная часть']) ?></th>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.plug', ['ru'=>'Плата за подключение:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">123000 <?= Yii::t('app', 'franchise.views.finance.twelwe', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.monthly_payment', ['ru'=>'Ежемесячный платеж:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_twelve', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.your_percent', ['ru'=>'Ваш %:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_thirteen', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">MixCart %:</td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_fourteen', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.commercial', ['ru'=>'Реклама:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_fifteen', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.your_percent_two', ['ru'=>'Ваш %:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_sixteen', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">MixCart %:</td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.seventeen', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.requests', ['ru'=>'Заявки:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_eighteen', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.your_percent_three', ['ru'=>'Ваш %:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_nineteen', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">MixCart %:</td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_twenty', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <th><?= Yii::t('app', 'franchise.views.finance.debts', ['ru'=>'Долги']) ?></th>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.system_debt', ['ru'=>'Долг поставщика перед системой:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_twenty_one', ['ru'=>'руб.']) ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.finance.last_month', ['ru'=>'Долг за прошлый месяц:']) ?></td>
                            <td style="text-align: right; font-size: 18px;">180000 <?= Yii::t('app', 'franchise.views.finance.rouble_twenty_two', ['ru'=>'руб.']) ?></td>
                        </tr>
                    </tbody></table>
                <div class="download-by-m">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-block btn-default" id="daterange-btn">
                                <span>
                                    <i class="fa fa-calendar"></i> <?= Yii::t('app', 'franchise.views.finance.period', ['ru'=>'Период для выписки']) ?>
                                </span>
                                <i class="fa fa-caret-down"></i>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-block btn-primary btn-md"><span>
                                    <i class="fa fa-download"></i>
                                </span> <?= Yii::t('app', 'franchise.views.finance.download', ['ru'=>'Скачать выписку']) ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>