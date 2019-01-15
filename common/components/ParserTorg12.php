<?php

namespace common\components;

use golovchanskiy\parseTorg12\models as models;
use golovchanskiy\parseTorg12\exceptions\ParseTorg12Exception;
use yii\db\Query;
use PHPExcel_Shared_Date;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use yii\swiftmailer\Mailer;
use yii\swiftmailer\Message;
use yii\web\Application;
use Yii;

//use PHPExcel;

class ParserTorg12
{

    /**
     * Путь к файлу
     *
     * @var string
     */
    private $filePath;

    /**
     * Допустимые значения ставки НДС
     * По умолчанию доступны: 0, 10, 18, 20
     *
     * @var string
     */
    private $taxRateList;

    /**
     * Ставка НДС по-умолчанию (устанавливается, если не удалось определить ставку)
     * По умолчанию: 20
     *
     * @var string
     */
    private $defaultTaxRate;

    /**
     * Товарная накладная
     *
     * @var Torg12Invoice
     */
    public $invoice;

    /**
     * Атрибуты заголовка накладной
     *
     * @var array
     */
    private $settingsHeader = [
        'document_number'   => [
            'label'     => ['номер документа'],
            'shift_row' => 1,
        ],
        'document_date'     => [
            'label'     => ['дата составления'],
            'shift_row' => 1,
        ],
        //   'document_upd_info' => [
        //       'label' => ['(1)'],
        //       'shift_row' => 1,
        //   ],
        'document_headinfo' => [
            'label'     => ['счет-фактура №'],
            'shift_row' => 1,
        ]
    ];

    /**
     * Синонимы для заголовков столбцов накладной
     *
     * @var array
     */
    /*    private $settingsRow = [
      'num' => '(№|№№|№ п/п|номер по порядку|Номер по порядку|но.*мер.*по.*по.*ряд.*ку)',
      'name' => ['название', 'наименование', 'наименование, характеристика, сорт, артикул товара'],
      'ed' => ['наименование', 'Единица измерения', 'ед. изм.', 'наиме-нование'],
      'code' => ['код', 'isbn', 'ean', 'артикул', 'артикул поставщика', 'код товара поставщика', 'код (артикул)', 'штрих-код'],
      'cnt' => ['кол-во', 'количество', 'кол-во экз.', 'общее кол-во', 'количество (масса нетто)', 'коли-чество (масса нетто)'],
      'cnt_place' => ['мест, штук'],
      'not_cnt' => ['в одном месте'],
      'price_without_tax' => ['Цена', 'цена', 'цена без ндс', 'цена без ндс, руб.', 'цена без ндс руб.', 'цена без учета ндс', 'цена без учета ндс, руб.', 'цена без учета ндс руб.', 'цена, руб. коп.', 'цена руб. коп.'],
      'price_with_tax' => ['цена с ндс, руб.', 'цена с ндс руб.', 'цена, руб.', 'цена руб.', 'сумма с учетом ндс, руб. коп.'],
      'sum_with_tax' => ['сумма.*с.*ндс.*','стоимость.*товаров.*с налогом.*всего'], // regexp
      'sum_without_tax' => ['сумма.*без.*ндс.*','стоимость.*товаров.*без налога.*всего'], // regexp
      'tax_rate' => ['ндс, %', 'ндс %', 'ставка ндс, %', 'ставка ндс %', 'ставка ндс', 'ставка, %', 'ставка %', 'ндс'],
      'total' => ['всего по накладной'],
      ]; */

    /**
     * Активный лист документа
     *
     * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    /*
     * Активный лист документа
     *
     * @var \PHPExcel_Worksheet
     */
    private $worksheet;
    private $firstRow = 0; // номер строки, которую считаем началом заголовка
    private $startRow = null; // номер строки, которую считаем началом строк накладной
    private $highestRow; // номер последней строки
    private $highestColumn; // номер последнего столбца
    private $columnList = []; // координаты заголовков значащих столбцов
    private $rowsToProcess = []; // номера строк с нужными данными по накладной
    private $tip_ooo_short = []; //массив кратких названий типов организаций
    private $tip_ooo_long = []; //массив полных названий типов организаций
    private $kodirov = true; // необходимость ручной раскодировки
    private $sumWithoutTaxExcel = 0; //Сумма без НДС, указанная в накладной
    private $sumWithTaxExcel = 0; //Сумма c НДС, указанная в накладной
    public $sumNotEqual = false; //совпадения общих сумм в накладной с суммами всех строк

    /**
     * @param string $filePath       Путь к файлу
     * @param array  $taxRateList    Список доступных ставкок НДС
     * @param int    $defaultTaxRate Ставка НДС по умолчанию
     */

    public function __construct($filePath, array $taxRateList = [0, 10, 18, 20], $defaultTaxRate = 20)
    {
        $this->filePath = $filePath;
        $this->taxRateList = $taxRateList;
        $this->defaultTaxRate = $defaultTaxRate;
        $this->getColumnSql();
    }

    private function getColumnSql()
    {
        $this->settingsRow = [];
        $result = (new Query())->select('*')->from('integration_torg12_columns')->all();

        foreach ($result as $row) {
            if ($row['regular_expression'] == 1) {
                $this->settingsRow[$row['name']] = trim($row['value']);
            } elseif ($row['regular_expression'] == 3) {
                $this->settingsRow[$row['name']] = ['reg' => [explode('|', trim($row['value']))]];
            } else {
                $this->settingsRow[$row['name']] = explode('|', trim($row['value']));
            }
        }
    }

    /**
     * Разобрать накладную
     *
     * @throws ParseTorg12Exception
     */
    public function parse()
    {

        if (!file_exists($this->filePath)) {
            throw new ParseTorg12Exception('Указан некорректный путь к файлу накладной');
        }

        // читаем файл в формате Excel по форме ТОРГ12
        try {
            $objPHPExcel = /* \PHPExcel_IOFactory */
                IOFactory::load($this->filePath);
            //$objPHPExcel = \PHPExcel_IOFactory::load($this->filePath);
        } catch (\Error $e) {
            $errorMsg = 'Невозможно прочитать загруженный файл: ' . $e->getMessage();
            throw new ParseTorg12Exception($errorMsg);
        }

        // создаем накладную
        $this->invoice = new Torg12Invoice();

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $this->setWorksheet($worksheet);

            // очищаем список критических ошибок, т.к. накладная может быть не на первом листе
            $this->invoice->errors = [];

            // определяем последнюю строку документа
            $this->highestRow = $this->worksheet->getHighestRow();
            // определяем последний столбец документа
            $this->highestColumn = \PHPExcel_Cell::columnIndexFromString($this->worksheet->getHighestColumn());

            if ($this->checkEncoding() == 0)
                $this->kodirov = false;

            // разбираем заголовок накладной
            $this->parseHeader();

            // разбираем заголовок для получения реквизитов поставщика (наименования, ИНН, КПП)
            $this->parseHeaderForRekviz();

            // разбираем заголовок строк накладной
            $this->parseRowsHeader();

            // разбираем строки накладной, выкидываем дубли заголовка и т.п.
            $this->parseRows();

            // обрабатываем строки накладной
            $this->processRows();

            // если в накладной есть строки, то не обрабатываем остальные листы
            if (count($this->invoice->rows)) {

                // проверяем, что обработаны все строки накладной
                $lastRow = end($this->invoice->rows);
                if ($lastRow->num != count($this->invoice->rows)) {
                    $this->invoice->errors['count_rows'] = 'Порядковый номер последней строки накладной не совпадает с количеством обработанных строк';
                }

                break;
            }

            // если в накладной несовпадения общей суммы с суммами всех строк, то отправляем письмо
            $this->processRows();
        }
    }

    /**
     * Изменить активный лист
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     */
    /*
     * Изменить активный лист
     *
     * @param \PHPExcel_Worksheet $worksheet
     */
    private function setWorksheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet)
        //private function setWorksheet(\PHPExcel_Worksheet $worksheet)
    {
        $this->worksheet = $worksheet;
        $this->rowsToProcess = [];
        $this->columnList = [];
    }

    /**
     * Нормализуем содержимое ячейки
     *  - удаляем лишние пробелы
     *  - удаляем переносы строк
     *
     * @param string $cellValue Содержимое ячейки
     * @param bool   $toLower   Перевести все символы в нижний регистр
     * @return string
     */
    private function normalizeHeaderCellValue($cellValue, $toLower = true)
    {
        $cellValue = trim($cellValue);

        if ($toLower) {
            $cellValue = mb_strtolower($cellValue, 'UTF-8');
        }

        // удаляем странные пробелы, состоящие из 2 символов
        $cellValue = str_replace(chr(194) . chr(160), " ", $cellValue);
        // удаляем переносы строк
        $cellValue = str_replace("\n", " ", $cellValue);
        // удаляем ручные переносы строк "- "
        $cellValue = str_replace("- ", "", $cellValue);
        $cellValue = str_replace("-\n", "", $cellValue);
        $cellValue = str_replace("  ", " ", $cellValue);

        if ($this->kodirov === true) { //если кодировка не читается, содержимое ячейки раскодируем вручную
            $cellValue = $this->conver($cellValue);
        }

        return $cellValue;
    }

    /**
     * Нормализуем содержимое ячейки
     *  - удаляем лишние пробелы
     *  - удаляем переносы строк
     *  - заменяем "," на ".", если в ячейке должно быть число
     *
     * @param string $cellValue Содержимое ячейки
     * @param bool   $isNumber  Число
     * @return string
     */
    private function normalizeCellValue($cellValue, $isNumber = false)
    {
        $cellValue = trim($cellValue);

        if ($isNumber) {
            $cellValue = str_replace(",", ".", $cellValue);
        }

        // удаляем странные пробелы, состоящие из 2 символов
        $cellValue = str_replace(chr(194) . chr(160), " ", $cellValue);
        // удаляем переносы строк
        $cellValue = str_replace("\n", " ", $cellValue);
        $cellValue = str_replace("  ", " ", $cellValue);

        if ($this->kodirov === true) {
            $cellValue = $this->conver($cellValue);
        }

        return $cellValue;
    }

    /**
     * Получить атрибуты заголовка накладной
     *  - номер накладной
     *  - дата составления
     *
     * @return string
     * @throws ParseTorg12Exception
     */
    private function parseHeader()
    {
        $checkSell = function ($col, $row, $attribute) { //функция для поиска атрибута в заданной столццом и строкой ячейке
            $cellValue = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row)->getValue()); //получаем значение ячейки

            if (strpos($cellValue, 'счет-фактура №') !== false) {

                $attributeValue = "";

                $temp = 0;
                for ($i = $col; $i < $this->highestColumn; $i++) {
                    if (!empty($this->normalizeCellValue($this->worksheet->getCellByColumnAndRow($i, $row)->getValue()))) {
                        $temp++;
                        if ($temp == 3) {
                            if ($this->normalizeCellValue($this->worksheet->getCellByColumnAndRow($i, $row)->getValue()) != 'от') {
                                $attributeValue .= ' от';
                            }
                        }
                        $attributeValue .= ' ' . $this->normalizeCellValue($this->worksheet->getCellByColumnAndRow($i, $row)->getValue());
                    }
                }
                $attributeValue = str_replace(",", ".", $attributeValue);
                $leftSide = /* trim */
                    (preg_replace("/.от.*/", "", $attributeValue));
                $rightSide = trim(str_replace($leftSide . " от", "", $attributeValue));
                $leftSide = trim(preg_replace("/.*№/", "", $leftSide));
                $check = substr($rightSide, 0, strpos($rightSide, " "));
                if (is_numeric($check) && (int)$check > 30000) {
                    $rightSide = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP/* Date::excelToTimestamp */
                    ($check));
                } else {
                    // года для распознавания даты документа (только предыдущий, нынешний и следующий))
                    $years = [date('Y', strtotime('-1 year')), date('Y'), date('Y', strtotime('+1 year'))];

                    foreach ($years as $year) {
                        $rightSide = trim(preg_replace("/." . $year . ".*/", "." . $year, $rightSide));
                    }
                    $monthArr = [
                        ' января.'   => '.01.',
                        ' февраля.'  => '.02.',
                        ' марта.'    => '.03.',
                        ' апреля.'   => '.04.',
                        ' мая.'      => '.05.',
                        ' июня.'     => '.06.',
                        ' июля.'     => '.07.',
                        ' августа.'  => '.08.',
                        ' сентября.' => '.09.',
                        ' октября.'  => '.10.',
                        ' ноября.'   => '.11.',
                        ' декабря.'  => '.12.',
                    ];

                    foreach ($monthArr as $key => $value) {
                        if (strpos($rightSide, $key) !== false) {
                            $rightSide = str_replace($key, $value, $rightSide);
                        }
                    }
                    $rightSide = str_replace("-", ".", $rightSide);
                    $rightSide = str_replace(" ", ".", $rightSide);
                    $rightSide = date('Y-m-d', strtotime($rightSide));
                }

                $attributeValue = $leftSide . "%%%%" . $rightSide;

                return $attributeValue;
            }

            if (in_array($cellValue, $attribute['label'])) {
                // заголовок атрибута в одной ячейке
                $attributeValue = $this->normalizeCellValue($this->worksheet->getCellByColumnAndRow($col, $row + $attribute['shift_row'])->getValue());
                $value_is_string = 0;
                if (strpos($attributeValue, ',') !== false)
                    $value_is_string = 1;
                if (strpos($attributeValue, ' ') !== false)
                    $value_is_string = 1;
                if (strpos($attributeValue, '-') !== false)
                    $value_is_string = 1;
                if (strpos($attributeValue, '.') !== false)
                    $value_is_string = 1;
                if ($value_is_string == 0) {
                    if ($cellValue == 'дата составления' && (int)$attributeValue) {
                        $attributeValue = date('Y-m-d', /* Date::excelToTimestamp */
                            PHPExcel_Shared_Date::ExcelToPHP($attributeValue));
                    }
                } else {
                    if ($cellValue == 'дата составления') {
                        $attributeValue = str_replace(",", ".", $attributeValue);
                        $attributeValue = str_replace("-", ".", $attributeValue);
                        $attributeValue = str_replace(" ", ".", $attributeValue);
                        if (strlen($attributeValue) < 10) {
                            $temp = explode('.', $attributeValue);
                            $temp[2] = '20' . $temp[2];
                            $attributeValue = $temp[0] . '.' . $temp[1] . '.' . $temp[2];
                        }
                        $attributeValue = date('Y-m-d', strtotime($attributeValue));
                    }
                }
                $this->firstRow = $row;
                return $attributeValue;
            } else {
                // заголовок атрибута разбит на две строки
                $nextValue = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row + 1)->getValue());
                // считаем что два слова в заголовке всегда, если есть переносы - не распознается
                foreach ($attribute['label'] as $val) {
                    $multiRowHeader = explode(' ', $val);
                    if ($cellValue == $multiRowHeader[0] && $nextValue == $multiRowHeader[1]) {
                        $attributeValue = $this->normalizeCellValue($this->worksheet->getCellByColumnAndRow($col, $row + $attribute['shift_row'] + 1)->getValue());
                        $this->firstRow = $row;
                        return $attributeValue;
                    }
                }
            }

            return null;
        };

        // запоминаем координаты номера накладной
        for ($row = 0; $row <= $this->highestRow; $row++) {
            for ($col = 0; $col <= $this->highestColumn; $col++) {

                if (!empty($documentNumber) && !empty($documentDate)) {
                    break;
                }

                // номер
                if (empty($documentNumber)) {
                    $documentNumber = $checkSell($col, $row, $this->settingsHeader['document_number']);
                    if (!empty($documentNumber)) {

                        $docArr = explode('%%%%', $documentNumber);
                        $documentNumber = $docArr[0];
                    }
                    /*    if (empty($documentNumber)) {
                      $documentNumber = $checkSell($col, $row, $this->settingsHeader['document_upd_info']);
                      // if(!empty($documentNumber)) {
                      var_dump("s".$documentNumber);
                      //     die();
                      //}
                     */
                    //   $documentNumber = preg_replace('/.от.*/', "", $documentNumber);
                    //   var_dump($documentNumber);
                    //    }
                }

                // дата составления
                if (empty($documentDate)) {
                    $documentDate = $checkSell($col, $row, $this->settingsHeader['document_date']);
                    if (!empty($documentDate)) {

                        $docArr = explode('%%%%', $documentDate);

                        if (sizeof($docArr) > 1)
                            $documentDate = $docArr[1];
                    }
                }
            }
        }

        if ($documentNumber) {
            $this->invoice->number = $documentNumber;
        } else {
            $this->invoice->errors['invoice_number'] = 'Не найден номер накладной';
        }

        if (isset($documentDate)) {
            //     $documentTime = strtotime($documentDate); // TODO Проверить формат даты
            //     $this->invoice->date = date('Y-m-d', $documentTime);
            $this->invoice->date = $documentDate;
            //   var_dump("date ".$documentDate);
        } else {
            $this->invoice->errors['invoice_date'] = 'Не найдена дата накладной';
        }
    }

    /**
     * Получить реквизиты поставщика из накладной
     *  - наименование поставщика
     *  - ИНН и КПП (если есть)
     *
     * @return string
     * @throws ParseTorg12Exception
     */
    private function parseHeaderForRekviz()
    {

        $checkSellRekviz = function ($col, $row, $attribute) { //функция для нахождения единственной ячейки, где хранится правильное наименование поставщика
            $cellValue = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row)->getValue());
            $has = 0;
            for ($i = 0; $i < count($attribute); $i++) {
                $str = mb_strtolower($attribute[$i]);
                if (strpos($cellValue, $str) !== false) {
                    $cellValue = ltrim($cellValue);
                    if (strpos($cellValue, $str) == 0)
                        $has = 1;
                }
            }
            return $has;
        };
        /* $checkSellConsignee = function ($col, $row, $attribute) { //функция для нахождения единственной ячейки, где хранится правильное наименование грузополучателя
          $cellValue = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row)->getValue());
          $has=0;
          for ($i=0;$i<count($attribute);$i++){
          $str=mb_strtolower($attribute[$i]);
          if (strpos($cellValue,$str)!==false) {
          $cellValue=ltrim($cellValue);
          if (strpos($cellValue,$str)==0) $has=1;
          }
          }
          return $has;
          }; */
        $checkSellRekvizInn = function ($col, $row, $attribute) { //функция для нахождения всех ячеек, в которых присутствует слово ИНН
            $cellValue = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row)->getValue());
            $has = 0;
            //if (($col) and ($row)) {
            //if (($col<11) and ($row<11)) {print $col.' '$row.' '.$cellValue.PHP_EOL;}}
            for ($i = 0; $i < count($attribute); $i++) {
                $str = mb_strtolower($attribute[$i]);
                if (strpos($cellValue, $str) !== false)
                    $has = 1;
            }
            return $has;
        };
        $checkSellRekvizTest = function ($col, $row) { //функция тестовая
            $cellValue = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row)->getValue());
            return $cellValue;
        };

        $this->invoice->namePostav = null; //свойствам придаём нулевые изначальные значения
        $this->invoice->nameConsignee = null;
        $this->invoice->innPostav = null;
        $this->invoice->kppPostav = null;
        $this->invoice->tmpMassivsInns = [];
        $this->invoice->tmpMassivsNames = [];
        $this->invoice->tmpMassivsConsignees = [];

        foreach ($this->settingsRow['name_postav'] as $row) { //первыми в массивы загоняем "Продавец" и "Поставщик"
            $this->tip_ooo_long[] = mb_strtolower($row);
            $this->tip_ooo_short[] = '';
            $this->tip_ooo_long[] = mb_strtolower($row) . ' ';
            $this->tip_ooo_short[] = '';
        }

        $result = (new Query())->select('*')->from('organization_forms')->all(); //получаем все значения коротких и длинных названий типов организаций

        foreach ($result as $row) { //загоняем эти названия в массив
            $this->tip_ooo_long[] = mb_strtolower($row['name_long']);
            $this->tip_ooo_short[] = mb_strtolower($row['name_short']);
        }

        // запоминаем координаты ячеек
        for ($row = 0; $row <= $this->highestRow; $row++) {
            for ($col = 0; $col <= $this->highestColumn; $col++) {
                $cellValue = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row)->getValue());
                // Закоммеченное НЕ УДАЛЯТЬ!!! Понадобится для будущих багов!
                //print $cellValue.PHP_EOL;
                //$qw = 'ò';
                //$qw=$checkSellRekvizTest($col,$row);
                //$qw2=mb_convert_encoding($qw, 'UTF-8', 'ISO-8859-1');
                //print $row.' '.$col.' '.$qwstr.' '.$qw;
                //print PHP_EOL;
                //$qw2=iconv("Windows-1252", "Windows-1251", $qw);
                //$qw3=iconv("Windows-1252", "UTF-8", $qw2);
                //$qw3=iconv("ISO-8859-9", "UTF-8", $qw);
                //$qw3 = utf8_encode($qw);
                //print $col.' '.$row.' '.$qw3.PHP_EOL;
                //if (($col<11) and ($row<11)) {print $col.' '.$row.' '.$qw3.PHP_EOL;}

                $inn = $checkSellRekvizInn($col, $row, $this->settingsRow['inn']);

                if ($inn == 1) {
                    $this->invoice->tmpMassivsInns[] = [$row, $col];
                    $inn = 0;
                }

                $name_postav = $checkSellRekviz($col, $row, $this->settingsRow['name_postav']);

                if ($name_postav == 1) {
                    $this->invoice->tmpMassivsNames[] = [$row, $col];
                    $name_postav = 0;
                }
                $name_consig = $checkSellRekviz($col, $row, $this->settingsRow['consignee']);

                if ($name_consig == 1) {
                    $this->invoice->tmpMassivsConsignees[] = [$row, $col];
                    $name_consig = 0;
                }
            }
        }
        // Закоммеченное НЕ УДАЛЯТЬ!!! Понадобится для будущих багов!
        //$this->invoice->tmpMassivsInns[] = [0, 0];
        //$encoding = Spreadsheet::ParseExcel::Cell->encoding();
        //$qw = ' ¹ ';
        //$qwstr = strlen($qw);
        //for($i=0;$i<$qwstr;$i++) {print ord($qw[$i]).' ';}
        //$qw = 'óíèôèöèðîâàííàÿ ôîðìà ¹ òîðã-12 óòâåðæäåíà ïîñòàíîâëåíèåì ãîñêîìñòàòà ðîññèè îò 25.12.98 ¹ 132';
        //$qw = ' ¹ ';
        //$qw2 = $this->conver($qw);
        //print $qw2;
        //print PHP_EOL;
        //print "<pre>";
        //$a=$this->settingsRow['inn'][0].'|';
        //print_r($this->invoice->tmpMassivsInns);
        //print $this->kodirov.PHP_EOL;
        //print "</pre>";
        //die();

        $inn_kpp_prodav = mb_strtolower($this->settingsRow['inn_kpp_prodav'][0]); //для получения ИНН пару значений переводим в строки
        $postav = mb_strtolower($this->settingsRow['postav'][0]);
        foreach ($this->invoice->tmpMassivsInns as $tmp) { //цикл по всем значениям массива ячеек, где встречалось слово ИНН
            $row = $tmp[0];
            $col = $tmp[1];
            if ($col != 0) {
                $cellValuePrev = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col - 1, $row)->getValue()); //ячейка слева
                $col_pr = $col;
                while ($cellValuePrev == '') {
                    $col_pr--;
                    if ($col_pr == 0) {
                        $cellValuePrev = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row)->getValue());
                        break;
                    }
                    $cellValuePrev = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col_pr, $row)->getValue());
                }
            } else {
                $cellValuePrev = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row)->getValue()); //ячейка слева
            }
            $cellValue = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row)->getValue()); //ячейка из массива
            $cellValueNext = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col + 1, $row)->getValue()); //ячейка справа
            $cellValue = trim($cellValue); //убираем пробелы
            $cellValuePrev = trim($cellValuePrev);
            $cellValueNext = trim($cellValueNext);
            if ((strpos($cellValue, $inn_kpp_prodav) === false) and ($cellValuePrev != $postav))
                continue;
            else {//если ячейка не содержит "ИНН/КПП продавца" и ячейка слева не содержит "Поставщик", переходим к следующей итерации цикла
                if ($cellValuePrev == $postav) { //если ячейка слева содержит "Поставщик", то работаем с текущей ячейкой
                    if (strpos($cellValue, 'инн/кпп') === false) { //если строка не содержит сочетание "ИНН/КПП", то
                        $temp1 = explode(',', $cellValue); //разбиваем значение ячейки по запятой, ИНН всегда указывается вторым реквизитом
                        $temp3 = trim($temp1[1]);
                        $temp2 = explode(' ', $temp3); //разбиваем по пробелу - отделяем цифры от самого слова "ИНН"
                        $this->invoice->innPostav = $temp2[1];
                        $this->invoice->kppPostav = null;
                    } else { //если строка содержит сочетание "ИНН/КПП", то
                        $temp1 = explode(',', $cellValue); //разбиваем значение ячейки по запятой, ИНН всегда указывается вторым реквизитом
                        $temp3 = trim($temp1[1]);
                        $temp2 = explode(' ', $temp3); //разбиваем по пробелу - отделяем цифры от самого слова "ИНН/КПП:"
                        $temp4 = trim($temp2[1]);
                        $temp3 = explode('/', $temp4); //разбиваем значения текущей ячейки по слэшу, отделяя цифры ИНН от КПП
                        $this->invoice->innPostav = $temp3[0];
                        $this->invoice->kppPostav = $temp3[1];
                    }
                } else {
                    if ($cellValue == $inn_kpp_prodav) { //если ячейка содержит только слова "ИНН/КПП продавца:", то работать будем с ячейкой справа
                        $i = 1;
                        while ($cellValueNext == '') { //если ячейка справа пустая, то в цикле находим непустую ячейку
                            $i++;
                            $cellValueNext = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col + $i, $row)->getValue());
                        }
                        $temp1 = explode('/', $cellValueNext); //разбиваем значения ячейки справа по слэшу, отделяя цифры ИНН от КПП
                        $this->invoice->innPostav = $temp1[0];
                        $this->invoice->kppPostav = $temp1[1];
                    } else { //если ячейка содержит не только слова "ИНН/КПП продавца:", то работаем с текущей ячейкой
                        $temp1 = explode($inn_kpp_prodav, $cellValue); //убираем из значения текущей ячейки "ИНН/КПП продавца:"
                        $temp1[1] = trim($temp1[1]); //убираем пробелы
                        $temp2 = explode('/', $temp1[1]); //разбиваем значения текущей ячейки по слэшу, отделяя цифры ИНН от КПП
                        $this->invoice->innPostav = $temp2[0];
                        if (count($temp2) > 1)
                            $this->invoice->kppPostav = $temp2[1];
                    }
                }
            }
        }
        foreach ($this->invoice->tmpMassivsNames as $tmp) { //цикл по всем значениям массива ячеек, где встречались слова "Поставщик" или "Продавец"
            $row = $tmp[0];
            $col = $tmp[1];
            //$cellValuePrev = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col-1, $row)->getValue());
            $cellValue = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row)->getValue()); //ячейка из массива
            $cellValueNext = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col + 1, $row)->getValue()); //ячейка справа
            $sovpad_poln = 0; //временная переменная, отвечающая за факт полного совпадения значения ячейки и маркеров Поставщика
            foreach ($this->settingsRow['name_postav'] as $nam) {
                $str = mb_strtolower($nam);
                if ($str == $cellValue)
                    $sovpad_poln = 1; //если значение ячейки содержит только слово "Поставщик" или только слово "Продавец", то переменная равна 1
            }
            if ($sovpad_poln == 1) { //если совпадение полное, то
                $i = 1;
                while ($cellValueNext == '') { //если ячейка справа пустая, то в цикле находим непустую ячейку
                    $i++;
                    $cellValueNext = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col + $i, $row)->getValue());
                }
            }
            if ($sovpad_poln == 1)
                $valueFromCell = $cellValueNext;
            else
                $valueFromCell = $cellValue; //если совпадение полное, то работаем с ячейкой справа иначе работаем с текущей ячейкой
            $temp1 = explode(',', $valueFromCell); //разбиваем значение ячейки по запятой, наименование поставщика всегда идёт первым реквизитом
            $this->invoice->realVendorName = $valueFromCell;
            $temp0 = $temp1[0]; //переменная, которая по идее должна хранить наименование поставщика

            if ($this->invoice->innPostav == '') {
                $temp_inn = trim($temp1[1]); //если поставщики настолько ленивые, что не указали само слово ИНН, то вытаскиваем его как второй параметр из строки с реквизитами
                $this->invoice->innPostav = trim($temp1[1]); //если в качестве второго параметра указан только ИНН
                if (strpos($temp_inn, '/') !== false) { //если в качестве второго параметра указаны ИНН и КПП, разделённые символом /
                    $temp2 = explode('/', $temp_inn);
                    $this->invoice->innPostav = $temp2[0];
                    $this->invoice->kppPostav = $temp2[1];
                }
                if (strpos($temp_inn, '\\') !== false) { //если в качестве второго параметра указаны ИНН и КПП, разделённые символом \
                    $temp2 = explode('\\', $temp_inn);
                    $this->invoice->innPostav = $temp2[0];
                    $this->invoice->kppPostav = $temp2[1];
                }
            }

            $sovpad = 0; //временная переменная, отвечающая за нахождение названия юр. лица в строке, по умолчанию 0 (не найдено)
            foreach ($this->tip_ooo_long as $nazv) { //цикл, в котором проверяется, есть ли в предполагаемом названии поставщика полное наименование юр. лица
                if (mb_strpos($temp0, $nazv) !== false)
                    $sovpad = 1; //если да, то временная переменная равна 1
            }
            foreach ($this->tip_ooo_short as $nazv) { //цикл, в котором проверяется, есть ли в предполагаемом названии поставщика краткое наименование юр. лица
                if ($nazv == '')
                    continue; //пропускаем пустые ячейки массива, соответствующие полным названиям "Продавец", "Поставщик"
                if (strpos($temp0, $nazv) !== false)
                    $sovpad = 1; //если да, то временная переменная равна 1
            }
            if ($sovpad == 0) { //если в предполагаемом названии поставщика нет ни полного, ни краткого наименования юр. лица, значит, настоящее название поставщика в другой ячейке (выше справа)
                $cellValueUp = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col + $i, $row - 1)->getValue()); //получаем значение ячейки справа вверху
                $temp1 = explode(',', $cellValueUp); //разбиваем значение ячейки по запятой, наименование поставщика всегда идёт первым реквизитом
                $temp0 = $temp1[0]; //переменная, которая по идее должна хранить наименование поставщика
            }
            $valueFromCell = str_replace($this->tip_ooo_long, $this->tip_ooo_short, $temp0); //заменяем в наименовании поставщика полное название типа организации кратким названием
            $this->invoice->namePostav = ltrim(mb_strtoupper($valueFromCell)); //убираем пробелы и переводим наименование поставщика в верхний регистр
        }

        //цикл по всем значениям массива ячеек, где встречались слова "Грузополучатель" или "Грузополучатель и его адрес:"
        $row = $this->invoice->tmpMassivsConsignees[0][0];
        $col = $this->invoice->tmpMassivsConsignees[0][1];
        $cellValue = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row)->getValue()); //ячейка из массива
        $cellValueNext = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col + 1, $row)->getValue()); //ячейка справа
        $sovpad_poln = 0; //временная переменная, отвечающая за факт полного совпадения значения ячейки и маркеров Грузополучателя
        foreach ($this->settingsRow['consignee'] as $nam) {
            $str = mb_strtolower($nam);
            if ($str == $cellValue)
                $sovpad_poln = 1; //если значение ячейки содержит только слово "Грузополучатель" или только слово "Грузополучатель и его адрес:", то переменная равна 1
        }
        if ($sovpad_poln == 1) { //если совпадение полное, то
            $i = 1;
            while ($cellValueNext == '') { //если ячейка справа пустая, то в цикле находим непустую ячейку
                $i++;
                $cellValueNext = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col + $i, $row)->getValue());
            }
            if (strlen($cellValueNext) < 20) { //если в ячейке справа короткое значение, то это значит, что нужное значение нужно искать в строке выше
                $col_up = 0;
                $i = 0;
                $cellValueUp = '';
                while ($cellValueUp == '') { //если ячейка справа пустая, то в цикле находим непустую ячейку
                    $i++;
                    $cellValueUp = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col_up + $i, $row - 1)->getValue());
                }
                $cellValueNext = $cellValueUp;
            }
        }

        if ($sovpad_poln == 1)
            $valueFromCell = $cellValueNext;
        else
            $valueFromCell = $cellValue; //если совпадение полное, то работаем с ячейкой справа иначе работаем с текущей ячейкой
        $as = $valueFromCell;
        rsort($this->settingsRow['consignee']); //Производим сортировку по убыванию массива маркеров Грузополучателей
        if ($sovpad_poln == 0) { //Если совпадение с маркером неполное, то
            $otrez = 0; //временная переменная, показывающая, удалялся ли из строки маркер грузополучателя
            foreach ($this->settingsRow['consignee'] as $nam) {
                $nam = mb_strtolower($nam);
                if ($otrez == 1)
                    continue;
                if (mb_strpos($as, $nam) !== false) {
                    $temp = explode($nam, $as); //разбиваем строку по маркеру грузополучателя, удаляя его из строки
                    $otrez = 1;
                    $as = trim($temp[1]);
                }
            }
        }
        $temp = explode(',', $as); //Разбиваем строку по запятой, выделяя наименование грузополучателя
        $as = $temp[0]; //наименование грузополучателя всегда идёт первым
        $temp = explode('(', $as); //разбиваем строку по открывающей скобке, удаляя из наименования грузополучателя указание подразделения, если оно указано в скобках
        $as = trim($temp[0]);
        $asstrlen = strlen($as);
        if (($as[0] == '"') and ($as[$asstrlen - 1] == '"'))
            $as = substr($as, 1, $asstrlen - 2); //если наименование грузополучателя заключено в кавычках, избавляемся от них
        if (($as[0] == '`') and ($as[$asstrlen - 1] == '`'))
            $as = substr($as, 1, $asstrlen - 2);
        if (($as[0] == '«') and ($as[$asstrlen - 1] == '»'))
            $as = substr($as, 1, $asstrlen - 2);
        if (($as[0] == "'") and ($as[$asstrlen - 1] == "'"))
            $as = substr($as, 1, $asstrlen - 2);
        $forma = ''; //временная переменная, которая будет содержать наименование формы собственности
        $ooo_has = 0; //временная переменная, сигнализирующая, найдена ли форма собственности
        $long = 0; //временная переменная, сигнализирующая, является ли найденная форма собственности полным названием формы собственности
        $pos = 0; //временная переменная, отвечающая за позицию вхождения формы собственности в наименовании грузополучателя
        foreach ($this->tip_ooo_long as $nazv) { //цикл по полным названиям формы собственности
            if ($ooo_has == 1)
                continue; //если форма собственности уже найдена, то ничего в этой итерации не делаем
            if (mb_strpos($as, $nazv) !== false) { //если в наименовании грузополучателя есть форма собственности, то
                $forma = $nazv; //запоминаем данную форму собственности,
                $ooo_has = 1; //указываем, что форма собственности найдена,
                $long = 1; //указываем, что найденная форма собственности - полное название формы собственности,
                $pos = mb_strpos($as, $nazv); //запоминаем позицию вхождения формы собственности в наименовании грузополучателя.
            }
        }
        if ($ooo_has != 1) { //если в наименовании грузополучателя нет полного названия формы собственности, то
            foreach ($this->tip_ooo_short as $nazv) { //цикл по кратким названиям формы собственности
                if ($ooo_has == 1)
                    continue; //если форма собственности уже найдена, то ничего в этой итерации не делаем
                if ($nazv == '')
                    continue; //если форма собственности пустая, то ничего в этой итерации не делаем
                if (mb_strpos($as, $nazv) !== false) { //если в наименовании грузополучателя есть форма собственности, то
                    $forma = $nazv; //запоминаем данную форму собственности,
                    $ooo_has = 1; //указываем, что форма собственности найдена,
                    $pos = mb_strpos($as, $nazv); //запоминаем позицию вхождения формы собственности в наименовании грузополучателя.
                    $pos_end = strlen($as) - strlen($forma); //вычисляем позицию вхождения формы собственности в наименовании грузополучателя, если её поставить в конце строки
                    if ($pos != 0) { //если позиция вхождения формы собственности в наименовании грузополучателя не в начале строки, то проверяем, находится ли она в конце строки
                        if ($pos != $pos_end) { //если позиция вхождения формы собственности в наименовании грузополучателя не в конце строки, то совпадение случайно и
                            $forma = ''; //форма собственности "забывается"
                            $ooo_has = 0; //указываем, что форма собственности не найдена
                        }
                    }
                }
            }
        }
        $dlina = strlen($forma); //вычисляем длину строки, содержащей название формы собственности
        $dlin2 = strlen($as) - $dlina; //вычисляем длину строки, содержащей наименование грузополучателя без названия формы собственности
        if ($pos > 0)
            $as = substr($as, 0, $dlin2);
        else
            $as = substr($as, $dlina); //из строки наименования грузополучателя удаляем название формы собственности
        $as = trim($as); //удаляем начальные и концевые пробелы
        $as = $forma . ' ' . $as; //заново собираем наименование грузополучателя, где название формы собственности идёт в обязательном порядке в начале строки
        $as = str_replace($this->tip_ooo_long, $this->tip_ooo_short, $as); //заменяем в наименовании грузополучателя полное название типа организации кратким названием
        $as = ltrim(mb_strtoupper($as)); //убираем пробелы и переводим наименование грузополучателя в верхний регистр
        $this->invoice->nameConsignee = $as;

        if (!isset($this->invoice->namePostav))
            $this->invoice->error['namePostav.'] = 'Не найдено наименование поставщика'; //записываем в массив ошибок случаи,
        if (!isset($this->invoice->innPostav))
            $this->invoice->error['innPostav.'] = 'Не найден ИНН поставщика'; //когда реквизиты не нашлись
        if (!isset($this->invoice->kppPostav))
            $this->invoice->error['kppPostav.'] = 'Не найден КПП поставщика';
        if (!isset($this->invoice->nameConsignee))
            $this->invoice->error['nameConsignee.'] = 'Не найдено наименование грузополучателя';
    }

    /**
     * Разобрать заголовок
     *
     * @throws ParseTorg12Exception
     */
    private function parseRowsHeader()
    {

        $match = function ($cellValue, $setting) {
            if (is_array($setting)) {

                if (isset($setting['reg'])) {
                    $isTrue = false;

                    foreach ($setting['reg'][0] as $is) {
                        $isTrue = (bool)preg_match('#' . $is . '#siu', $cellValue);
                        if ($isTrue === true)
                            break;
                    }
                    return $isTrue;
                } else
                    return in_array($cellValue, $setting);
            } elseif (is_string($setting)) {
                return (bool)preg_match('#' . $setting . '#siu', $cellValue);
            } else {
                return false;
            }
        };

        /**
         * Запоминаем координаты первого заголовка
         */
        for ($row = $this->firstRow; $row <= $this->highestRow; $row++) {
            for ($col = 0; $col <= $this->highestColumn; $col++) {


                $cellValue = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row)->getValue());

                // нужна дополнительная проверка ячейки из следующей строки, т.к. заголовки дублируются
                $nextRowCellValue = $this->normalizeHeaderCellValue($this->worksheet->getCellByColumnAndRow($col, $row + 1)->getValue());

                if (!isset($this->columnList['num']) && $match($cellValue, $this->settingsRow['num'])) {

                    $this->columnList['num']['col'] = $col;
                    $this->columnList['num']['row'] = $row;
                } elseif (!isset($this->columnList['name']) && $match($cellValue, $this->settingsRow['name'])) {

                    $this->columnList['name']['col'] = $col;
                    $this->columnList['name']['row'] = $row;
                } elseif (!isset($this->columnList['code']) && $match($cellValue, $this->settingsRow['code'])) {

                    $this->columnList['code']['col'] = $col;
                    $this->columnList['code']['row'] = $row;
                } elseif (!isset($this->columnList['cnt']) && $match($cellValue, $this->settingsRow['cnt'])) {
                    // специальная обработка для количества, т.к. могут быть два одинаковых заголовка
                    if (!$match($nextRowCellValue, $this->settingsRow['not_cnt'])) {
                        $this->columnList['cnt']['col'] = $col;
                        $this->columnList['cnt']['row'] = $row;
                    }
                } elseif (!isset($this->columnList['cnt_place']) && $match($cellValue, $this->settingsRow['cnt_place'])) {

                    $this->columnList['cnt_place']['col'] = $col;
                    $this->columnList['cnt_place']['row'] = $row;
                } elseif (!isset($this->columnList['price_without_tax']) && $match($cellValue, $this->settingsRow['price_without_tax'])) {

                    $this->columnList['price_without_tax']['col'] = $col;
                    $this->columnList['price_without_tax']['row'] = $row;

                } elseif (!isset($this->columnList['price_with_tax']) && $match($cellValue, $this->settingsRow['price_with_tax'])) {

                    $this->columnList['price_with_tax']['col'] = $col;
                    $this->columnList['price_with_tax']['row'] = $row;

                } elseif (!isset($this->columnList['sum_with_tax']) && $match($cellValue, $this->settingsRow['sum_with_tax'])) {

                    $this->columnList['sum_with_tax']['col'] = $col;
                    $this->columnList['sum_with_tax']['row'] = $row;
                } elseif (!isset($this->columnList['sum_without_tax']) && $match($cellValue, $this->settingsRow['sum_without_tax'])) {

                    $this->columnList['sum_without_tax']['col'] = $col;
                    $this->columnList['sum_without_tax']['row'] = $row;
                } elseif (!isset($this->columnList['tax_rate']) && $match($cellValue, $this->settingsRow['tax_rate'])) {

                    $this->columnList['tax_rate']['col'] = $col;
                    $this->columnList['tax_rate']['row'] = $row;
                } elseif (!isset($this->columnList['ed']) && $match($cellValue, $this->settingsRow['ed'])) {

                    $this->columnList['ed']['col'] = $col;
                    $this->columnList['ed']['row'] = $row;
                }
            }
        }

        // проверяем корректность заголовка
        $this->checkRowsHeader();

        $this->startRow = $this->getMaxRowFromComplexHeader($this->columnList);
        // var_dump($this->startRow);
        // var_dump($this->columnList);
    }

    /**
     * Проверить корректность заголовка
     *
     * @throws ParseTorg12Exception
     */
    private function checkRowsHeader()
    {
        $headErrors = [];

        // проверяем наличие обязательных колонок
        if (empty($this->columnList)) {

            $headErrors[] = 'Необходимо указать названия столбцов';
        } elseif (!isset($this->columnList['num'])) {

            $msg = 'Необходимо добавить столбец, содержащий порядковый номер строки ("%s")';
            $headErrors[] = sprintf($msg, implode('"; "', $this->settingsRow['num']));
        } elseif (!isset($this->columnList['code'])) {

            $msg = 'Необходимо добавить столбец, содержащий код товара ("%s")';
            $headErrors[] = sprintf($msg, implode('"; "', $this->settingsRow['code']));
        } elseif (!isset($this->columnList['name'])) {

            $msg = 'Необходимо добавить столбец, содержащий название товара ("%s")';
            $headErrors[] = sprintf($msg, implode('"; "', $this->settingsRow['name']));
        } elseif (!isset($this->columnList['cnt']) && !isset($this->columnList['cnt_place'])) {

            $msg = 'Необходимо добавить столбец, содержащий количество товара ("%s")';
            $headErrors[] = sprintf($msg, implode('"; "', array_merge($this->settingsRow['cnt'], (array)$this->settingsRow['cnt_place'])));
        } elseif (!isset($this->columnList['price_without_tax'])) {

            $msg = 'Необходимо добавить столбец, содержащий цену товара без НДС ("%s")';
            $headErrors[] = sprintf($msg, implode('"; "', $this->settingsRow['price_without_tax']));
        } elseif (!isset($this->columnList['sum_without_tax'])) {

            $msg = 'Необходимо добавить столбец, содержащий сумму товара без НДС ("%s")';
            if (!is_array($this->settingsRow['sum_without_tax'])) {
                $headErrors[] = sprintf($msg, implode('"; "', $this->settingsRow['sum_without_tax']));
            } else {
                $headErrors[] = sprintf($msg, implode('"; "', $this->settingsRow['sum_without_tax']['reg'][0]));
            }

            /*  } elseif (!isset($this->columnList['price_with_tax']) && !isset($this->columnList['sum_with_tax'])) {

              $msg = 'Необходимо добавить столбец, содержащий цену товара c НДС ("%s")';
              $headErrors[] = sprintf($msg, implode('"; "', array_merge($this->settingsRow['price_with_tax'], (array)$this->settingsRow['sum_with_tax'])));
             */
        } elseif (!isset($this->columnList['tax_rate'])) {

            $msg = 'Необходимо добавить столбец, содержащий ставку НДС ("%s")';
            $headErrors[] = sprintf($msg, implode('"; "', $this->settingsRow['tax_rate']));
        }
    }

    /**
     * Разбираем накладную, определяем номера строк с позициями накладной
     */
    private function parseRows()
    {
        $ws = $this->worksheet;

        for ($row = ($this->startRow + 1); $row <= $this->highestRow; $row++) {

            // прекращаем обработку, если попали в подвал накладной
            for ($col = 0; $col <= $this->highestColumn; $col++) {
                if (in_array($this->normalizeHeaderCellValue($ws->getCellByColumnAndRow($col, $row)->getValue()), $this->settingsRow['total'])) {
                    $col_sum = $this->columnList['sum_without_tax']['col'];
                    $this->sumWithoutTaxExcel = $this->normalizeHeaderCellValue($ws->getCellByColumnAndRow($col_sum, $row)->getValue());
                    $col_sum = $this->columnList['sum_with_tax']['col'];
                    $this->sumWithTaxExcel = $this->normalizeHeaderCellValue($ws->getCellByColumnAndRow($col_sum, $row)->getValue());
                    $this->highestRow = $row - 1;
                    return;
                }
            }
            $currentRow = [];
            //$currentRow['num'] = $this->normalizeHeaderCellValue($ws->getCellByColumnAndRow($this->columnList['num']['col'], $row)->getValue());
            $currentRow['code'] = $this->normalizeHeaderCellValue($ws->getCellByColumnAndRow($this->columnList['code']['col'], $row)->getValue());
            // добавляем строку в обработку
            if ($this->validateRow($row, $currentRow)) {
                $this->rowsToProcess[] = $row;
            }
        }
    }

    /**
     * Проверить, не является ли строка заголовком, т.к. ТОРГ12 может содержать несколько заголовков
     *
     * @param int   $rowNumber  Номер строки
     * @param array $currentRow Содержимое строки
     * @return bool
     */
    private function validateRow($rowNumber, $currentRow)
    {
        $row = [];
        $key = 1;

        if (isset($this->columnList['num'])) {
            $begin = $this->columnList['num']['col'];
        } else {
            $begin = $this->columnList['name']['col'];
        }
        for ($col = $begin; $col <= $this->highestColumn; $col++) {//
            $currentCell = $this->normalizeCellValue($this->worksheet->getCellByColumnAndRow($col, $rowNumber)->getValue());
            // запишем непустые значения в массив для текущей строки
            if ($currentCell) {
                $row[$key++] = $currentCell;
            }
        }

        // пропускаем строку с номерами столбцов
        if (
            count($row) > 2 && ($row[1] == 1 && $row[2] == 2 && $row[3] == 3)
        ) {
            return false;
        }

        if (empty($row[1])) {
            return false;
        }

        if ($row[1] == 'А' and $row[2] == 'Б') {
            return false;
        }

        if ($row[1] == '1' and $row[2] == '2') {
            return false;
        }

        if ($row[1] == '1' and $row[2] == '1а') {
            return false;
        }

        // пропускаем строку без порядкового номера
        /* if (!intval($row[1])) {
          return false;
          } */

        // пропускаем повторные заголовки (достаточно, если в одном столбце будет заголовок кода товара)
        if (in_array($currentRow['code'], $this->settingsRow['code'])) {
            return false;
        }

        return true;
    }

    /**
     * Обработать валидные строки накладной
     *  - добавить строки в накладную
     *  - определить ошибки в строках накладной
     */
    private function processRows()
    {
        $ws = $this->worksheet;
        $numberRow = 0;
        for ($row = $this->startRow; $row <= $this->highestRow; ++$row) {

            // пропускаем строки, которые не надо обрабатывать
            if (!in_array($row, $this->rowsToProcess)) {
                continue;
            }

            $invoiceRow = new InvoiceRow();

            // порядковый номер
            if (isset($this->columnList['num'])) {
                $invoiceRow->num = (int)$this->normalizeCellValue($ws->getCellByColumnAndRow($this->columnList['num']['col'], $row)->getValue());
            } else {
                $numberRow++;
                $invoiceRow->num = $numberRow;
            }

            // код товара
            $invoiceRow->code = $this->normalizeCellValue($ws->getCellByColumnAndRow($this->columnList['code']['col'], $row)->getValue());
            if (!$invoiceRow->code) {
                $invoiceRow->errors['code'] = 'Не указан код товара';
            }

            // название товара
            $invoiceRow->name = $this->normalizeCellValue($ws->getCellByColumnAndRow($this->columnList['name']['col'], $row)->getValue());
            // количество
            if (isset($this->columnList['cnt'])) {
                $invoiceRow->cnt = (double)$this->normalizeCellValue($ws->getCellByColumnAndRow($this->columnList['cnt']['col'], $row)->getValue(), true);
            }

            // еденицы измерения
            if (isset($this->columnList['ed'])) {
                $invoiceRow->ed = $this->normalizeCellValue($ws->getCellByColumnAndRow($this->columnList['ed']['col'], $row)->getValue(), true);
            }

            if (!$invoiceRow->cnt && isset($this->columnList['cnt_place'])) {
                $invoiceRow->cnt = (int)$this->normalizeCellValue($ws->getCellByColumnAndRow($this->columnList['cnt_place']['col'], $row)->getValue(), true);
            }

            if (($invoiceRow->name != '') and ($invoiceRow->num != 0)) {
                // сумма без НДС
                if (isset($this->columnList['sum_without_tax']))
                    $invoiceRow->sum_without_tax = (double)$this->normalizeCellValue($ws->getCellByColumnAndRow($this->columnList['sum_without_tax']['col'], $row)->getValue(), true);
                if ($invoiceRow->sum_without_tax) {
                    $this->invoice->price_without_tax_sum += $invoiceRow->sum_without_tax;
                    $this->invoice->price_without_tax_sum = round($this->invoice->price_without_tax_sum, 2);
                }

                // сумма  c НДС
                if (isset($this->columnList['sum_with_tax']))
                    $invoiceRow->sum_with_tax = (double)$this->normalizeCellValue($ws->getCellByColumnAndRow($this->columnList['sum_with_tax']['col'], $row)->getValue(), true);
                if ($invoiceRow->sum_with_tax) {
                    $this->invoice->price_with_tax_sum += $invoiceRow->sum_with_tax;
                    $this->invoice->price_with_tax_sum = round($this->invoice->price_with_tax_sum, 2);
                }
            }
            /*
              if (isset($this->columnList['price_with_tax'])) {

              $invoiceRow->price_with_tax = (float)$this->normalizeCellValue($ws->getCellByColumnAndRow($this->columnList['price_with_tax']['col'], $row)->getValue(), true);
              $this->invoice->price_without_tax_sum += $invoiceRow->sum_without_tax;

              } elseif (isset($this->columnList['sum_with_tax'])) {

              $sumWithTax = $this->normalizeCellValue($ws->getCellByColumnAndRow($this->columnList['sum_with_tax']['col'], $row)->getValue(), true);
              if ($sumWithTax) {
              if ((int)$invoiceRow->cnt > 0) {
              $invoiceRow->price_with_tax = round($sumWithTax / $invoiceRow->cnt, 4);
              }
              $this->invoice->price_without_tax_sum += $sumWithTax;
              }

              }
             */
            // цена без НДС
            $invoiceRow->price_without_tax = (float)$this->normalizeCellValue($ws->getCellByColumnAndRow($this->columnList['price_without_tax']['col'], $row)->getValue(), true);

            /* if (!$invoiceRow->price_with_tax) {
              $invoiceRow->errors['price_with_tax'] = 'Не указана цена с учетом НДС';
              }
             */
            // НДС
            $taxRate = $this->normalizeCellValue($ws->getCellByColumnAndRow($this->columnList['tax_rate']['col'], $row)->getValue(), true);
            $taxRate = str_replace('%', '', $taxRate);
            $taxRate = str_replace('без ндс', '0', strtolower($taxRate));
            $taxRate = intval($taxRate);
            if (in_array($taxRate, $this->taxRateList)) {
                $invoiceRow->tax_rate = $taxRate;
            } elseif (isset($this->defaultTaxRate)) {
                $invoiceRow->tax_rate = $this->defaultTaxRate;
                $invoiceRow->errors['tax_rate'] = sprintf('Установлено значение НДС по умолчанию: %d', $this->defaultTaxRate);
            } else {
                $invoiceRow->errors['tax_rate'] = sprintf('Значение НДС "%s" отсутствует в списке доступных', $taxRate);
                $this->invoice->errors['tax_rate'] = 'В накладной присутствует товар с некорректной ставкой НДС';
            }
            /*
              // проверка корректности указанной ставки НДС
              $calcPriceWithTax = round($invoiceRow->price_without_tax * (1 + $invoiceRow->tax_rate / 100), 2);
              $priceWithTax = round($invoiceRow->price_with_tax, 2);
              $diffPriceWithTax = abs($calcPriceWithTax - $priceWithTax);
              // погрешность 1 руб.
              if ($diffPriceWithTax > 1) {
              $invoiceRow->errors['diff_price_with_tax'] = sprintf('Некорректно указана ставка НДС (Цена с учётом НДС: %s, Рассчитанная цена с учетом НДС: %s', $priceWithTax, $calcPriceWithTax);
              $this->invoice->errors['diff_price_with_tax'] = 'В накладной присутсвует товар, по которому указана некорректная цена или ставка НДС';
              }
             */
            // Проверка на корректность расчета цены единицы

            /*     if ($invoiceRow->price_without_tax == $invoiceRow->sum_without_tax) {
              $invoiceRow->price_without_tax = round($invoiceRow->price_without_tax / $invoiceRow->cnt,2);
              }
             */
            if (($invoiceRow->name != '') and ($invoiceRow->num != 0)) {
                if ($invoiceRow->cnt > 0)
                    $invoiceRow->price_with_tax = round($invoiceRow->sum_with_tax / $invoiceRow->cnt, 2);

                // добавляем обработанную строку в накладную
                $this->invoice->rows[$invoiceRow->num] = $invoiceRow;
            }
        }
        if ($this->invoice->price_without_tax_sum != $this->sumWithoutTaxExcel)
            $this->sumNotEqual = true;
        if ($this->invoice->price_with_tax_sum != $this->sumWithTaxExcel)
            $this->sumNotEqual = true;
    }

    /**
     * Получить номер последней строки многострочного заголовка
     *
     * @return int
     */
    private function getMaxRowFromComplexHeader()
    {
        $maxRow = 0;

        foreach ($this->columnList as $val) {
            $maxRow = ($val['row'] > $maxRow) ? $val['row'] : $maxRow;
        }

        // пропускаем строку с номерами столбцов
        $maxRow++;

        return $maxRow;
    }

    private function conver($str)
    {
        $ret = '';
        $number = strlen($str);
        for ($i = 0; $i < $number; $i++) {
            $sim = ord($str[$i]);
            if ($sim < 128) {
                $ret .= $str[$i];
                continue;
            }
            if (($sim > 127) and ($sim < 194)) {
                $ret .= $str[$i];
                $i++;
                $ret .= $str[$i];
                continue;
            }
            if ($sim > 195) {
                $ret .= $str[$i];
                $i++;
                $ret .= $str[$i];
                continue;
            }
            if (($sim == 194) and (ord($str[$i + 1]) == 185)) {
                $i++;
                $ret .= '№';
                continue;
            }
            if (($sim == 194) and (ord($str[$i + 1]) != 185)) {
                $ret .= $str[$i];
                $i++;
                $ret .= $str[$i];
                continue;
            }
            if (($sim == 195) and (ord($str[$i + 1]) > 191)) {
                $ret .= $str[$i];
                $i++;
                $ret .= $str[$i];
                continue;
            }
            if (($sim == 195) and (ord($str[$i + 1]) < 192)) {
                $i++;
                $sim = ord($str[$i]);
                switch ($sim) {
                    case 128:
                        $ret .= 'а';
                        break;
                    case 129:
                        $ret .= 'б';
                        break;
                    case 130:
                        $ret .= 'в';
                        break;
                    case 131:
                        $ret .= 'г';
                        break;
                    case 132:
                        $ret .= 'д';
                        break;
                    case 133:
                        $ret .= 'е';
                        break;
                    case 134:
                        $ret .= 'ж';
                        break;
                    case 135:
                        $ret .= 'з';
                        break;
                    case 136:
                        $ret .= 'и';
                        break;
                    case 137:
                        $ret .= 'й';
                        break;
                    case 138:
                        $ret .= 'к';
                        break;
                    case 139:
                        $ret .= 'л';
                        break;
                    case 140:
                        $ret .= 'м';
                        break;
                    case 141:
                        $ret .= 'н';
                        break;
                    case 142:
                        $ret .= 'о';
                        break;
                    case 143:
                        $ret .= 'п';
                        break;
                    case 144:
                        $ret .= 'р';
                        break;
                    case 145:
                        $ret .= 'с';
                        break;
                    case 146:
                        $ret .= 'т';
                        break;
                    case 147:
                        $ret .= 'у';
                        break;
                    case 148:
                        $ret .= 'ф';
                        break;
                    case 149:
                        $ret .= 'х';
                        break;
                    case 150:
                        $ret .= 'ц';
                        break;
                    case 151:
                        $ret .= 'ч';
                        break;
                    case 152:
                        $ret .= 'ш';
                        break;
                    case 153:
                        $ret .= 'щ';
                        break;
                    case 154:
                        $ret .= 'ъ';
                        break;
                    case 155:
                        $ret .= 'ы';
                        break;
                    case 156:
                        $ret .= 'ь';
                        break;
                    case 157:
                        $ret .= 'э';
                        break;
                    case 158:
                        $ret .= 'ю';
                        break;
                    case 159:
                        $ret .= 'я';
                        break;
                    case 160:
                        $ret .= 'а';
                        break;
                    case 161:
                        $ret .= 'б';
                        break;
                    case 162:
                        $ret .= 'в';
                        break;
                    case 163:
                        $ret .= 'г';
                        break;
                    case 164:
                        $ret .= 'д';
                        break;
                    case 165:
                        $ret .= 'е';
                        break;
                    case 166:
                        $ret .= 'ж';
                        break;
                    case 167:
                        $ret .= 'з';
                        break;
                    case 168:
                        $ret .= 'и';
                        break;
                    case 169:
                        $ret .= 'й';
                        break;
                    case 170:
                        $ret .= 'к';
                        break;
                    case 171:
                        $ret .= 'л';
                        break;
                    case 172:
                        $ret .= 'м';
                        break;
                    case 173:
                        $ret .= 'н';
                        break;
                    case 174:
                        $ret .= 'о';
                        break;
                    case 175:
                        $ret .= 'п';
                        break;
                    case 176:
                        $ret .= 'р';
                        break;
                    case 177:
                        $ret .= 'с';
                        break;
                    case 178:
                        $ret .= 'т';
                        break;
                    case 179:
                        $ret .= 'у';
                        break;
                    case 180:
                        $ret .= 'ф';
                        break;
                    case 181:
                        $ret .= 'х';
                        break;
                    case 182:
                        $ret .= 'ц';
                        break;
                    case 183:
                        $ret .= 'ч';
                        break;
                    case 184:
                        $ret .= 'ш';
                        break;
                    case 185:
                        $ret .= 'щ';
                        break;
                    case 186:
                        $ret .= 'ъ';
                        break;
                    case 187:
                        $ret .= 'ы';
                        break;
                    case 188:
                        $ret .= 'ь';
                        break;
                    case 189:
                        $ret .= 'э';
                        break;
                    case 190:
                        $ret .= 'ю';
                        break;
                    case 191:
                        $ret .= 'я';
                        break;
                }
            }
        }
        return $ret;
    }

    private function checkEncoding()
    {
        $word1 = 'инн';
        $word2 = 'сумма';
        $has = 1;
        for ($row = 0; $row <= $this->highestRow; $row++) {
            if ($has == 0)
                continue;
            for ($col = 0; $col <= $this->highestColumn; $col++) {
                if ($has == 0)
                    continue;
                $cellValue = $this->worksheet->getCellByColumnAndRow($col, $row)->getValue();
                if (mb_strpos($cellValue, $word1) !== false) {
                    $has = 0;
                }
                if (mb_strpos($cellValue, $word2) !== false) {
                    $has = 0;
                }
            }
        }
        return $has;
    }

    public function sendMailNotEqualSum($email, $name_file, $language)
    {
        /** @var \yii\swiftmailer\Mailer $mailer */
        /** @var \yii\swiftmailer\Message $message */
        /* $mailer = Yii::$app->mailer;
          Yii::$app->language = $language;
          $subject = Yii::t('app', 'common.mail.error.subject', ['ru' => 'В вашей накладной ошибка!']); */
        $mailer = Yii::$app->mailer;
        $mailer->htmlLayout = '@common/mail/layouts/empty';
        Yii::$app->language = $language;
        $subject = Yii::t('app', 'common.mail.error.subject', ['ru' => 'В вашей накладной ошибка!']);

        if (!empty($email)) {
            $mailer->compose('torg12', [
                'invoice'            => $this->invoice,
                'name_file'          => $name_file,
                'sumWithoutTaxExcel' => $sumWithoutTaxExcel,
                'sumWithTaxExcel'    => $sumWithTaxExcel,
            ])
                ->setTo($email)
                ->setSubject($subject)
                ->send();
        }

        /* if (!empty($email)) {
          $viev = 'Возможно, в Вашей накладной ошибка. Просим проверить. ';
          $viev .= 'В вашем письме, отправленном  ' . $this->invoice->nameConsignee . ' во вложенном файле накладной ';
          $viev .= $name_file . ' есть ошибки. Суммы, указанные в итоге накладной, не совпадают с подсчитанной суммой всех строк накладной. ';
          $viev .= 'Сумма накладной без НДС - ' . $this->sumWithoutTaxExcel . ' а сумма без НДС всех строк накладной равна ' . $this->invoice->price_without_tax_sum;
          $viev .= ' Сумма накладной c НДС - ' . $this->sumWithTaxExcel . ' а сумма c НДС всех строк накладной равна ' . $this->invoice->price_with_tax_sum;
          $viev .= ' Просим обратить внимание на ошибку и подтвердить достоверность передаваемых данных.';
          $mailer->compose()
          ->setTo($email)
          ->setFrom(['noreply@mixcart.ru' => 'noreply@mixcart.ru'])
          ->setSubject($subject)
          ->setHtmlBody($viev)
          ->send();
          } */
    }

}

class InvoiceRow extends models\InvoiceRow
{

    public $ed;

}
