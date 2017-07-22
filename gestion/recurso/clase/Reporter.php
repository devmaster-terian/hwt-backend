<?php
require_once('../recurso/clase/PHPExcel.php');
require_once('../recurso/clase/Logger.php');
date_default_timezone_set('America/Mexico_City');

setlocale(LC_TIME, 'es_MX','esp').': ';
error_reporting(E_ALL);
ini_set('display_errors', false);
ini_set('display_startup_errors', false);

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

set_include_path('../recurso/clase/');
require_once 'PHPExcel/IOFactory.php';
require_once 'PHPExcel.php';

$rendererName = PHPExcel_Settings::PDF_RENDERER_TCPDF;
$rendererLibrary = '';
$rendererLibraryPath = dirname(__FILE__). '/tcpdf/' . $rendererLibrary;

//  Here's the magic: you __tell__ PHPExcel what rendering engine to use
//     and where the library is located in your filesystem
if (!PHPExcel_Settings::setPdfRenderer(
    $rendererName,
    $rendererLibraryPath
)) {
    die('NOTICE: Please set the $rendererName and $rendererLibraryPath values' .
        '<br />' .
        'at the top of this script as appropriate for your directory structure' .
        $rendererLibraryPath
    );
}

class Reporter
{
    private static $objPHPExcel;
    private static $fileName;
    private static $arrayAlpha;
    private static $accessWorkbook;
    private static $numRow = 0;
    private static $maxColumn = '';
    private static $currentRow = 0;

    public static function setFontSize($pCellInitial,$pCellEnd,$pFontSize){
        $rangeCells = $pCellInitial. ':'. $pCellEnd;
        $styleArray = [
            'font' => [
                'size' => $pFontSize
            ]
        ];

        self::$objPHPExcel->getActiveSheet()
            ->getStyle($rangeCells)->applyFromArray($styleArray);
    }

    public static function mergeCells($pCellInitial,$pCellEnd){

        $rangeMerged = $pCellInitial. ':'. $pCellEnd;
        Logger::write('Mergear: ' . $rangeMerged);
        self::$objPHPExcel->getActiveSheet()
            ->mergeCells($rangeMerged);

        $styleCell = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP)
        );

        self::$objPHPExcel->getActiveSheet()
            ->getStyle($rangeMerged)->applyFromArray($styleCell);

        self::$objPHPExcel->getActiveSheet()
            ->getStyle($pCellInitial)->getAlignment()->setWrapText(true);
        return true;
    }

    public static function signatureBox($pObjSignature){

        $cellInitial = $pObjSignature->column . self::getCurrentRow();

        $customCellEnd = $pObjSignature->column;

        if(property_exists($pObjSignature,'columnEnd')){
            $customCellEnd = $pObjSignature->columnEnd;
        }

        $customRowEnd = (intval(self::getCurrentRow()) + 4);

        if(property_exists($pObjSignature,'rowEnd')){
            $customRowEnd = (intval(self::getCurrentRow()) + $pObjSignature->rowEnd);
        }

        $cellEnd = $customCellEnd . $customRowEnd;
        Logger::write('Celda Final: ' . $cellEnd);

        $rangeSeparator = $cellInitial. ':'. $cellEnd;

        Logger::write('rangeSeparator: ' . $rangeSeparator);
        self::$objPHPExcel->getActiveSheet()
            ->mergeCells($rangeSeparator);

        $signatureData = $pObjSignature->namePerson . PHP_EOL . $pObjSignature->titlePerson;

        self::$objPHPExcel->getActiveSheet()
            ->setCellValue($cellInitial, $signatureData);

        self::$objPHPExcel->getActiveSheet()
            ->getStyle($cellInitial)->getAlignment()->setWrapText(true);

        $styleCell = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_BOTTOM)
        );

        $styleBorder = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array('argb' => $pObjSignature->borderColor),
                ),
            ),
        );

        $styleCell = array_merge($styleCell,$styleBorder);

        self::$objPHPExcel->getActiveSheet()
            ->getStyle($cellInitial.':'.$cellEnd)->applyFromArray($styleCell);

        self::fillCellColor(
            self::$objPHPExcel->getActiveSheet(),
            $cellInitial,
            $pObjSignature->cellBackground);
    }

    public static function createSimpleData($pEtiqueta,$pValor,$pFormato = null){
        $objSeccion = new \stdClass();
        $objSeccion->etiqueta = $pEtiqueta;
        $objSeccion->valor    = $pValor;

        if($pFormato !== null){
            $objSeccion->formato  = json_encode($pFormato);
        }
        else{
            $objSeccion->formato  = null;
        }

        return $objSeccion;
    } // function createSimpleData    

    public static function printSectionData ($pArrayObjSeccion,$pObjFormato){

        $alfabeto = range('A', 'Z');

        $tipoPresentacion = 'titulo';
        if($pObjFormato !== null){
            $bgColor  = 'cddae1';
            $align    = 'right';

            if($pObjFormato->bgColor !== null){
                $bgColor = $pObjFormato->bgColor;
            }

            if($pObjFormato->fgColor !== null){
                $fgColor = $pObjFormato->fgColor;
            }
            if($pObjFormato->align !== null){
                $align = $pObjFormato->align;
            }

            $objCeldaTitulo = new \stdClass();
            $objCeldaTitulo->bgColor      = $bgColor;
            $objCeldaTitulo->fgColor      = $fgColor;
            $objCeldaTitulo->align        = $align;
            $objCeldaTitulo->wrapText     = $pObjFormato->wrapText;
            $objCeldaTitulo->fontBold     = $pObjFormato->fontBold;
            $objCeldaTitulo->borderActive = $pObjFormato->borderActive;
            $objCeldaTitulo->borderColor  = $pObjFormato->borderColor;

            $tipoPresentacion             = $pObjFormato->tipoPresentacion;
        }
        else{
            $objCeldaTitulo = new \stdClass();
            $objCeldaTitulo->bgColor  = 'cddae1';
            $objCeldaTitulo->align    = 'right';
            $objCeldaTitulo->fontBold = true;
        }

        switch ($tipoPresentacion){
            case 'titulo':
                $numIndice = 1;
                foreach($pArrayObjSeccion as $objSeccion){
                    switch ($numIndice){
                        case 1:
                            $celdaTitulo = 'A';
                            $celdaValor  = 'B';
                            break;
                        case 2:
                            $celdaTitulo = 'C';
                            $celdaValor  = 'D';
                            break;
                        case 3:
                            $celdaTitulo = 'E';
                            $celdaValor  = 'F';
                            break;
                        case 4:
                            $celdaTitulo = 'G';
                            $celdaValor  = 'H';
                            break;
                    }

                    self::writeCell($celdaTitulo . self::getCurrentRow(),utf8_encode($objSeccion->etiqueta),$objCeldaTitulo);
                    self::writeCell($celdaValor  . self::getCurrentRow(),$objSeccion->valor);

                    $numIndice = $numIndice + 1;

                    if($numIndice > 4){
                        $numIndice = 1;
                        self::increaseCurrentRow();
                    }
                } //foreach pArrayObjSeccion

                break;

            case 'medio':
                $numIndice = 1;
                foreach($pArrayObjSeccion as $objSeccion){
                    switch ($numIndice){
                        case 1:
                            $celdaTitulo = 'A';
                            $celdaValor  = 'B';
                            break;
                        case 2:
                            $celdaTitulo = 'E';
                            $celdaValor  = 'F';
                            break;
                    }

                    $objCeldaTitulo->fontBold = true;
                    self::writeCell($celdaTitulo . self::getCurrentRow(),utf8_encode($objSeccion->etiqueta),$objCeldaTitulo);
                    self::writeCell($celdaValor  . self::getCurrentRow(),$objSeccion->valor);

                    $numIndice = $numIndice + 1;

                    if($numIndice > 2){
                        $numIndice = 1;
                        self::increaseCurrentRow();
                    }
                } //foreach pArrayObjSeccion

                break;
            case 'simple':
                $numIndice = 0;
                foreach($pArrayObjSeccion as $objSeccion){
                    $celdaValor = $alfabeto[$numIndice];

                    $objFormatoAsignado = json_decode($objSeccion->formato);
                    Logger::write('$objFormatoCelda: ' . $objSeccion->formato);

                    if($objFormatoAsignado !== null){
                        self::writeCell($celdaValor  . self::getCurrentRow(),$objSeccion->valor,$objFormatoAsignado);
                    }
                    else{
                        self::writeCell($celdaValor  . self::getCurrentRow(),$objSeccion->valor,$objCeldaTitulo);
                    }

                    $numIndice = $numIndice + 1;

                    Logger::write('Escribiendo la CElda');

                    if(property_exists($pObjFormato,'celdaSaltoLinea')){
                        if($numIndice > $pObjFormato->celdaSaltoLinea){
                            $numIndice = 1;
                            self::increaseCurrentRow();
                        }
                    }
                    else{
                        if($numIndice > 8){
                            $numIndice = 1;
                            self::increaseCurrentRow();
                        }
                    }
                } //foreach pArrayObjSeccion
                break;

            case 'custom':
                $numIndice = 0;
                foreach($pArrayObjSeccion as $objSeccion){
                    $celdaValor = $alfabeto[$numIndice];
                    self::writeCell($celdaValor  . self::getCurrentRow(),utf8_encode($objSeccion->etiqueta),$objCeldaTitulo);
                    $numIndice = $numIndice + 1;

                    $celdaValor = $alfabeto[$numIndice];

                    Logger::write(json_encode($objSeccion));

                    if(isset($objSeccion->formato)){
                        $objFormatoValor = json_decode($objSeccion->formato);
                        Logger::write('va a poner formato a la celda');

                        self::writeCell($celdaValor  . self::getCurrentRow(),$objSeccion->valor,$objFormatoValor);
                    }
                    else{
                        self::writeCell($celdaValor  . self::getCurrentRow(),$objSeccion->valor);
                    }

                    $numIndice = $numIndice + 1;

                    if(property_exists($pObjFormato,'celdaSaltoLinea')){
                        if($numIndice > ($pObjFormato->celdaSaltoLinea - 1)){
                            $numIndice = 0;
                            self::increaseCurrentRow();
                        }
                    }
                    else{
                        if($numIndice > 8){
                            $numIndice = 1;
                            self::increaseCurrentRow();
                        }
                    }



                } //foreach pArrayObjSeccion
                break;
        }
        self::increaseCurrentRow();
    } // function printSectionData
        
    public static function setActiveSheet($pIndex){
        self::$objPHPExcel->setActiveSheetIndex($pIndex);
    }

    public static function createSheet($pSheetTitle){
        $objWorkSheet = self::$objPHPExcel->createSheet();
        $objWorkSheet->setTitle($pSheetTitle);

        return self::$objPHPExcel->getIndex($objWorkSheet);
    }

    public static function decreaseCurrentRow(){
        self::$currentRow = self::$currentRow - 1;
    }

    public static function increaseCurrentRow($pNumRows = null){
        if(isset($pNumRows)){
            self::$currentRow = self::$currentRow + $pNumRows;
        }
        else{
            self::$currentRow = self::$currentRow + 1;
        }
    }

    public static function setCurrenRow($pRow){
        self::$currentRow = $pRow;
    }

    public static function getCurrentRow(){
        return self::$currentRow;
    }

    public static function printTitle($cellInitial,$cellEnd,$value,$objFormatCell){
        $fontForeground = '000000';
        $fontSize       = 10;
        $fontBold       = false;
        $cellBackground = 'ffffff';

        if(isset($objFormatCell)){
            $fontForeground = $objFormatCell->fontForeground;
            $cellBackground = $objFormatCell->cellBackground;

            if(isset($objFormatCell->fontSize)){
                $fontSize       = $objFormatCell->fontSize;
            }

            if(isset($objFormatCell->fontBold)){
                $fontBold       = $objFormatCell->fontBold;
            }
        }

        $styleSeparator = array(
            'font'  => array(
                'bold'  => $fontBold,
                'color' => array('rgb' => $fontForeground),
                'size'  => $fontSize,
                'name'  => 'Verdana'),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $rangeSeparator = $cellInitial. ':'. $cellEnd;
        self::$objPHPExcel->getActiveSheet()
            ->mergeCells($rangeSeparator);

        self::$objPHPExcel->getActiveSheet()
            ->setCellValue($cellInitial, $value);

        self::$objPHPExcel->getActiveSheet()
            ->getStyle($cellInitial)->applyFromArray($styleSeparator);

        self::fillCellColor(
            self::$objPHPExcel->getActiveSheet(),
            $cellInitial,
            $cellBackground);
    }

    public static function printVerticalSeparator($valueSeparator, $pForeground, $pBackgroundColor, $pBold = null){

        $fontBold = true;
        if(isset($pBold)){
            $fontBold = $pBold;
        }

        $styleSeparator = array(
                'font'  => array(
                    'bold'  => $fontBold,
                    'color' => array('rgb' => $pForeground),
                    'size'  => 10,
                    'name'  => 'Verdana'),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );
        
        if(self::$maxColumn !== ''){
            $rangeSeparator = 'A'. self::$currentRow . ':'. self::$maxColumn . self::$currentRow;
        }
        else{
            $rangeSeparator = 'A' . self::$currentRow . ':P' .self::$currentRow;
        }
        
        Logger::enable(true,'printVerticalSeparator');
        Logger::write($rangeSeparator);

        $cellColumn = 'A' . self::$currentRow;

        self::$objPHPExcel->getActiveSheet()
            ->mergeCells($rangeSeparator);
        
        self::$objPHPExcel->getActiveSheet()
            ->getStyle($cellColumn)->applyFromArray($styleSeparator);

        self::fillCellColor(
            self::$objPHPExcel->getActiveSheet(),
            $cellColumn,
            $pBackgroundColor);

        /*
        self::$objPHPExcel->getActiveSheet()
            ->setCellValue($cellColumn, utf8_encode($valueSeparator));
        */

        self::$objPHPExcel->getActiveSheet()
            ->setCellValue($cellColumn, $valueSeparator);

        self::$currentRow = self::$currentRow + 1;
    }

    public static function getMaxColumn(){
        return self::$maxColumn;
    }

    public static function setMaxColumn($pLetterCell){
        self::$maxColumn = $pLetterCell;
    }

    public static function openFile($pFileName){
        self::$objPHPExcel = new PHPExcel();

        $developerCenter = "Terian SRL Report Library";

        self::$objPHPExcel->getProperties()
            ->setCreator($developerCenter)
            ->setLastModifiedBy($developerCenter)
            ->setTitle($pFileName)
            ->setSubject($pFileName)
            ->setDescription($developerCenter)
            ->setKeywords($developerCenter)
            ->setCategory($developerCenter);

        $currentDate = date('YmdHis');

        self::$fileName       = $pFileName . $currentDate;
        self::$accessWorkbook = 'hwt'.$currentDate;
        self::$arrayAlpha     = array_merge(range('A', 'Z'));

        return self::$fileName;
    }

    public static function drawImage($pObjImage){
        Logger::enable(true,'drawImage');

        $objDrawing = new PHPExcel_Worksheet_Drawing();         //create object for Worksheet drawing
        $objDrawing->setName('Imagen');             //set name to image
        $objDrawing->setDescription('Imagen');      //set description to image

        Logger::write('Asi llego la imagen ' . $pObjImage->url);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // WINDOWS
            $imageFile = str_replace('gestion\recurso\clase','',dirname(__FILE__));
            $imageFile = $imageFile.$pObjImage->url;
            $imageFile = str_replace('../../../','',$imageFile);
        } else {
            // LINUX
            $imageFile = str_replace('gestion/recurso/clase','',dirname(__FILE__));
            $imageFile = $imageFile.$pObjImage->url;
            $imageFile = str_replace('../../../','',$imageFile);
        }

        if($pObjImage->absoluto){
            $imageFile = $pObjImage->url;
        }

        Logger::write('Imagen que se va a desplegar:');
        Logger::write($imageFile);

        try{
            $objDrawing->setPath($imageFile);
            $objDrawing->setOffsetX($pObjImage->offsetX);                            //setOffsetX works properly
            $objDrawing->setOffsetY($pObjImage->offsetY);                            //setOffsetY works properly
            $objDrawing->setCoordinates($pObjImage->cell);                     //set image to cell
            $objDrawing->setWidth($pObjImage->width);                            //set width, height
            $objDrawing->setHeight($pObjImage->height);
            $objDrawing->setWorksheet(self::$objPHPExcel->getActiveSheet());  //save
        }catch (Exception $e){
            Logger::write('self::drawImage: Can not print the image:');
            Logger::write($e);
        }
    }

    public static function prepareHeader($pReportName,$pObjConfig = null){
        Logger::enable(true,'prepareHeader');

        $objDrawing = new PHPExcel_Worksheet_Drawing();         //create object for Worksheet drawing
        $objDrawing->setName('Customer Signature');             //set name to image
        $objDrawing->setDescription('Customer Signature');      //set description to image
        //$signature = '../recurso/imagen/hwt_logo_gestion.png';  //Path to signature .jpg file

        $signature = str_replace('clase','imagen',dirname(__FILE__));
        $signature = $signature.'/hwt_logo_gestion.png';  //Path to signature .jpg file

        if(isset($pObjConfig)){
            if(property_exists($pObjConfig,'rowTitle')){
                $cellImage = 'A' . $pObjConfig->rowTitle;
                $rowTitleA = intval($pObjConfig->rowTitle);
                $rowTitleB = $rowTitleA + 2;
                $rowTitleC = $rowTitleA + 4;
            }
        }
        else{
            $cellImage = 'A1';
            $rowTitleA = 1;
            $rowTitleB = 3;
            $rowTitleC = 5;
        }

        $objDrawing->setPath($signature);
        $objDrawing->setOffsetX(5);                            //setOffsetX works properly
        $objDrawing->setOffsetY(5);                            //setOffsetY works properly
        $objDrawing->setCoordinates($cellImage);                     //set image to cell
        $objDrawing->setWidth(90);                            //set width, height
        $objDrawing->setHeight(90);
        $objDrawing->setWorksheet(self::$objPHPExcel->getActiveSheet());  //save

        $dts = new DateTime();
        $strFecha = strftime("%A",$dts->getTimestamp()) . ', '
            . strftime("%d",$dts->getTimestamp()) . ' de '
            . strftime("%B",$dts->getTimestamp()) . ' del '
            . strftime("%Y",$dts->getTimestamp()) . ' '
            . '[' . date('H:i:s') . ']';

        $styleTitle = array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => '054F7D'),
                'size'  => 15,
                'name'  => 'Verdana'
            ));

        $estiloReporte = array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => '054F7D'),
                'size'  => 12,
                'name'  => 'Verdana'
            ));

        $estiloFecha = array(
            'font'  => array(
                'bold'  => false,
                'color' => array('rgb' => '054F7D'),
                'size'  => 10,
                'name'  => 'Verdana'
            ));

        if(self::$maxColumn !== ''){
            $rangeTitleA = 'B' . $rowTitleA . ':'. self::$maxColumn . ($rowTitleA + 1);
            $rangeTitleB = 'B' . $rowTitleB . ':'. self::$maxColumn . $rowTitleB;
            $rangeTitleC = 'B' . $rowTitleC . ':'. self::$maxColumn . $rowTitleC;
        }
        else{
            $rangeTitleA = 'B' . $rowTitleA . ':P' . ($rowTitleA + 1) ;
            $rangeTitleB = 'B' . $rowTitleB . ':P' . $rowTitleB ;
            $rangeTitleC = 'B' . $rowTitleC . ':P' . $rowTitleC ;
        }

        self::$objPHPExcel->getActiveSheet()
            ->mergeCells($rangeTitleA)
            ->mergeCells($rangeTitleB)
            ->mergeCells($rangeTitleC);

        $recordSysEmpresa = Dataworker::findFirst('sys_empresa');

        self::$objPHPExcel->getActiveSheet()
            ->setCellValue('B' . $rowTitleA . '', $recordSysEmpresa->nombre_empresa)
            ->setCellValue('B' . $rowTitleB . '', $pReportName)
            ->setCellValue('B' . $rowTitleC . '', utf8_encode($strFecha));

        self::$objPHPExcel->getActiveSheet()
            ->getStyle('B' . $rowTitleA . '')->applyFromArray($styleTitle);

        self::$objPHPExcel->getActiveSheet()
            ->getStyle('B' . $rowTitleB . '')->applyFromArray($estiloReporte);

        self::$objPHPExcel->getActiveSheet()
            ->getStyle('B' . $rowTitleC . '')->applyFromArray($estiloFecha);
    }

    public static function fillCellColor($objActiveSheet, $cell, $color){
        $objActiveSheet->getStyle($cell)->getFill()->applyFromArray(array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => $color
            )
        ));
    }

    public static function writeContent($objRecords){
        $numRow = 7;

        foreach($objRecords as $field => $value){
            $numColumn = 0;
            foreach($value as $fieldRecord => $valueRecord){

                $cellColumn = self::$arrayAlpha[$numColumn] . $numRow;

                self::$objPHPExcel->getActiveSheet()
                    ->setCellValue($cellColumn, $valueRecord);
                $numColumn++;
            }
            $numRow++;
        }
    }

    public static function writeCell($pCell,$pCellValue,$pObjConfig = null){

        self::$objPHPExcel->getActiveSheet()
            ->setCellValue($pCell, $pCellValue);

        if(isset($pObjConfig)){
            self::fillCellColor(
                self::$objPHPExcel->getActiveSheet(),
                $pCell,
                $pObjConfig->bgColor);

            if(isset($pObjConfig->align)){
                if($pObjConfig->align === 'left'){
                    $styleCell = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
                    );
                }

                if($pObjConfig->align === 'right'){
                    $styleCell = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
                    );
                }

                if($pObjConfig->align === 'center'){
                    $styleCell = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
                    );
                }

                $fgColor = '054F7D';
                if(isset($pObjConfig->fgColor)){
                    $fgColor = $pObjConfig->fgColor;
                }

                $fontBold = false;
                if(isset($pObjConfig->fontBold)){
                    $fontBold = $pObjConfig->fontBold;
                }

                $fontSize = 10;
                if(isset($pObjConfig->fontSize)){
                    $fontSize = $pObjConfig->fontSize;
                }

                $styleFont = array(
                    'font'  => array(
                        'bold'  => $fontBold,
                        'color' => array('rgb' => $fgColor),
                        'size'  => $fontSize,
                        'name'  => 'Verdana'
                    ));

                $styleCell = array_merge($styleCell,$styleFont);

                if($pObjConfig->borderActive){
                    $styleBorder = array(
                        'borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THICK,
                                'color' => array('argb' => $pObjConfig->borderColor),
                            ),
                        ),
                    );

                    $styleCell = array_merge($styleCell,$styleBorder);
                }

                self::$objPHPExcel->getActiveSheet()
                    ->getStyle($pCell)->applyFromArray($styleCell);
            }
        }

        $pCellValue = str_replace(',','',$pCellValue);
        $pCellValue = str_replace('.','',$pCellValue);

        if(is_numeric($pCellValue) and !property_exists($pObjConfig,'align')){
            $styleNumber = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
            );

            self::$objPHPExcel->getActiveSheet()
                ->getStyle($pCell)->applyFromArray($styleNumber);
        }

        if(property_exists($pObjConfig,'wrapText')){
            self::$objPHPExcel->getActiveSheet()
                ->getStyle($pCell)->getAlignment()->setWrapText(true);
        }

    } // writeCell

    public static function prepareTitleColumns($pArrayTitleColumns){
        $numColumn = 0;
        $numRow    = 6;

        $styleCellTitle = array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FFFFFF'),
                'size'  => 10,
                'name'  => 'Verdana'
            ));

        foreach ($pArrayTitleColumns as $tituloColumn){
            $cellColumn = self::$arrayAlpha[$numColumn] . $numRow;

            $arrayTituloColumn = explode(':',$tituloColumn);
            $columnWidth = intval($arrayTituloColumn[0]);
            $cellLabel   = $arrayTituloColumn[1];

            self::$objPHPExcel->getActiveSheet()
                ->getColumnDimension(self::$arrayAlpha[$numColumn])
                ->setWidth($columnWidth);

            self::$objPHPExcel->getActiveSheet()
                ->getStyle($cellColumn)
                ->applyFromArray($styleCellTitle);

            self::fillCellColor(
                self::$objPHPExcel->getActiveSheet(),
                $cellColumn,
                '054F7D');

            self::$objPHPExcel->getActiveSheet()
                ->setCellValue($cellColumn, utf8_encode($cellLabel));

            self::$objPHPExcel->getActiveSheet()
                ->getStyle($cellColumn)
                ->getAlignment()
                ->setWrapText(true);

            $numColumn = $numColumn + 1;
        }

        self::$currentRow = 7;
    }

    public static function saveFile(){
        Logger::enable(true,'saveFile');

        $finalName = str_replace('.php', '.xlsx', __FILE__);
        $finalName = str_replace('Reporter',self::$fileName,$finalName);
        $finalName = str_replace('recurso\clase','reporte',$finalName); /* Windows */
        $finalName = str_replace('recurso/clase','reporte',$finalName); /* Linux   */

        $sheetsWoorkbook = self::$objPHPExcel->getSheetCount();
        Logger::write('$sheetsWoorkbook: ');
        Logger::write(json_encode($sheetsWoorkbook));

        $iLoop = 0;
        while($iLoop < $sheetsWoorkbook){
            self::setActiveSheet($iLoop);
            $iLoop = $iLoop + 1;

            self::$objPHPExcel->getActiveSheet()
                ->getPageSetup()
                ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);

            self::$objPHPExcel->getActiveSheet()
                ->getPageSetup()
                ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);

            self::$objPHPExcel->getActiveSheet()
                ->setShowGridlines(false);

            self::$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.3);
            self::$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.3);
            self::$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.3);
            self::$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.3);

            $var = self::$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
            $var->setFitToWidth(1,true);
            $var->setFitToHeight(0,true);

            self::$objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
            self::$objPHPExcel->getActiveSheet()->getProtection()->setSort(true);
            self::$objPHPExcel->getActiveSheet()->getProtection()->setInsertRows(true);
            self::$objPHPExcel->getActiveSheet()->getProtection()->setFormatCells(true);
            self::$objPHPExcel->getActiveSheet()->getProtection()->setPassword(self::$accessWorkbook);
        } //iLoop

        self::setActiveSheet(0);

        self::$objPHPExcel->getSecurity()->setLockWindows(true);
        self::$objPHPExcel->getSecurity()->setLockStructure(true);
        self::$objPHPExcel->getSecurity()->setWorkbookPassword(self::$accessWorkbook);

        $objWriter = PHPExcel_IOFactory::createWriter(self::$objPHPExcel, 'Excel2007');
        $objWriter->save($finalName);

        return $finalName;
    }
}