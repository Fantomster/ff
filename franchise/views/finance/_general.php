<?php 

?>

<div class="">
    <div class="box-header with-border">
        <h4 class="box-title">
            <a class="collapse-href" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true">
                <span class="line-before"></span>Общий доход <span class="arrow-open"><i class="fa fa-fw fa-sort-desc pull-right"></i></span>
            </a>
        </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse in" aria-expanded="true">
        <div class="box-body">
            <div class="pay-chek">
                <table class="pay-table" width="100%">
                    <tbody>
                        <tr>
                            <td style="text-align: left;">Поставщики Мне:</td>
                            <td style="text-align: right; font-size: 18px;"><?= number_format($vendorsStats['turnoverCut'], 2, '.', ' ') ?> руб.</td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">MixCart Мне:</td>
                            <td style="text-align: right; font-size: 18px;"> 0 руб.</td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">Я MixCart'у:</td>
                            <td style="text-align: right; font-size: 18px;"><?= number_format($vendorsStats['turnoverCut'] * (100 - $franchiseeType->share) / 100, 2, '.', ' ') ?> руб.</td>
                        </tr>
<!--                        <tr>
                            <td style="text-align: left;">Кол-во месяцев:</td>
                            <td style="text-align: right; font-size: 18px;">6 месяцев</td>
                        </tr>-->
                        <tr style="border-top: 1px dotted rgba(51, 54, 59, 0.1);">
                            <td style="text-align: left; font-weight: bold;">Итого заработано:</td>
                            <td style="text-align: right; font-size: 22px;"><?= number_format($vendorsStats['turnoverCut'] - ($vendorsStats['turnoverCut'] * (100 - $franchiseeType->share) / 100), 2, '.', ' ') ?> руб.</td>
                        </tr>
                    </tbody></table>
            </div>
        </div>
    </div>
</div>
