<?php
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Dataworker.php');
require_once('../recurso/clase/Logger.php');
require_once('../recurso/clase/Mnemea.php');
require_once('../recurso/clase/Reporter.php');
require_once('apiConfigurador.php');

function creaEspecificacionSimple($pEtiqueta,$pValor){
    $objSeccion = new \stdClass();
    $objSeccion->etiqueta = $pEtiqueta;
    $objSeccion->valor    = $pValor;
    return $objSeccion;
} // function creaEspecificacionSimple

function reporteImprimeSeccion ($pArrayObjSeccion,$pObjFormato){

    $alfabeto = range('A', 'Z');

    $tipoPresentacion = 'titulo';
    if($pObjFormato !== null){
        $bgColor  = 'cddae1';
        $align    = 'right';

        if($pObjFormato->bgColor !== null){
            $bgColor = $pObjFormato->bgColor;
        }

        if($pObjFormato->align !== null){
            $align = $pObjFormato->align;
        }

        $objCeldaTitulo = new \stdClass();
        $objCeldaTitulo->bgColor      = $bgColor;
        $objCeldaTitulo->align        = $align;
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

                Reporter::writeCell($celdaTitulo . Reporter::getCurrentRow(),utf8_encode($objSeccion->etiqueta),$objCeldaTitulo);
                Reporter::writeCell($celdaValor  . Reporter::getCurrentRow(),$objSeccion->valor);

                $numIndice = $numIndice + 1;

                if($numIndice > 4){
                    $numIndice = 1;
                    Reporter::increaseCurrentRow();
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
                Reporter::writeCell($celdaTitulo . Reporter::getCurrentRow(),utf8_encode($objSeccion->etiqueta),$objCeldaTitulo);
                Reporter::writeCell($celdaValor  . Reporter::getCurrentRow(),$objSeccion->valor);

                $numIndice = $numIndice + 1;

                if($numIndice > 2){
                    $numIndice = 1;
                    Reporter::increaseCurrentRow();
                }
            } //foreach pArrayObjSeccion

            break;
        case 'simple':
            $numIndice = 0;
            foreach($pArrayObjSeccion as $objSeccion){
                $celdaValor = $alfabeto[$numIndice];

                Reporter::writeCell($celdaValor  . Reporter::getCurrentRow(),$objSeccion->valor,$objCeldaTitulo);

                $numIndice = $numIndice + 1;

                if($numIndice > 8){
                    $numIndice = 1;
                    Reporter::increaseCurrentRow();
                }
            } //foreach pArrayObjSeccion
            break;
    }
    Reporter::increaseCurrentRow();
} // function reporteImprimeSeccion

function generaHojaEspecificacionesRemolque(){
    $vin          = Receiver::getApiParameter('vin');

    $archivoGenerado = Reporter::openFile('hojaEspecificaciones');
    $nombreReporte = "Hoja de Especificaciones del Remolque de la Unidad  (" . $vin . ")";
    Reporter::setMaxColumn('H');
    Reporter::prepareHeader($nombreReporte);

    // ECRC: Preparando el Titulo de las Columnas
    $arrayTituloColumnas = array(
        "25: ",
        "22: ",
        "25: ",
        "22: ",
        "25: ",
        "22: ",
        "25: ",
        "22: "
    );

    Reporter::prepareTitleColumns($arrayTituloColumnas);

    $dbSession = Dataworker::openConnection();
    $objCondicion = new \stdClass();
    $objCondicion->vin        = Dataworker::equalToString($vin);

    $hwtRemolque = Dataworker::findFirst('hwt_remolque',$objCondicion);

    if($hwtRemolque->activeRecord === '1') {

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///            B L O Q U E    D E    P R E S E N T A C I O N    D E   L A    I N F O R M A C I O N           ///
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // ECRC: Contacto
        Reporter::decreaseCurrentRow();

        if($hwtRemolque->volquete_incluido === 'true'){
            Reporter::printVerticalSeparator('CARROCERIA (BALDE) DE VOLQUETE'  ,'FFFFFF','05486c');
            $arrayObjSeccion = array();
            array_push($arrayObjSeccion,creaEspecificacionSimple('Marca'               ,$hwtRemolque->volquete_marca));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Año'                 ,$hwtRemolque->volquete_ann));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Composicion'         ,$hwtRemolque->volquete_composicion));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Capacidad (Yardas)'  ,$hwtRemolque->volquete_capacidad));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Largo'               ,$hwtRemolque->volquete_largo));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Alto'                ,$hwtRemolque->volquete_alto));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Alto Panel Lateral'  ,$hwtRemolque->volquete_alto_paneles_laterales));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Alza de Valde'       ,$hwtRemolque->volquete_forma_alza_balde));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Cubierta Cabina'     ,$hwtRemolque->volquete_cubierta_cabina));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Sistema Carpa'       ,$hwtRemolque->volquete_sistema_carpa));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Estructura Inferior' ,$hwtRemolque->volquete_estructura_inferior));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Num. Panel Puerta'   ,$hwtRemolque->volquete_num_paneles_puerta));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Rampa Regado'        ,$hwtRemolque->volquete_rampa_regado));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Puerta'              ,$hwtRemolque->volquete_puerta));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Calefaccion Balde'   ,$hwtRemolque->volquete_calefaccion_balde));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Cuerno Remolque'     ,$hwtRemolque->volquete_pin_cuerno_remolque));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Electricidad y Aire' ,$hwtRemolque->volquete_conexion_electricidad_aire));
            reporteImprimeSeccion($arrayObjSeccion);
        }

        if($hwtRemolque->cajon_incluido === 'true') {
            Reporter::increaseCurrentRow();
            Reporter::printVerticalSeparator('CARROCERIA DE CAJON'  ,'FFFFFF','05486c');
            $arrayObjSeccion = array();
            array_push($arrayObjSeccion,creaEspecificacionSimple('Modelo'                  ,$hwtRemolque->cajon_modelo));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Año'                     ,$hwtRemolque->cajon_ann));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Peso Máximo (GVW)'       ,$hwtRemolque->cajon_peso_maximo));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Tipo'                    ,$hwtRemolque->cajon_tipo));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Construcción'            ,$hwtRemolque->cajon_construccion));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Largo (DI)'              ,$hwtRemolque->cajon_largo));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Ancho (DI)'              ,$hwtRemolque->cajon_ancho));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Alto (DI)'               ,$hwtRemolque->cajon_alto));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Puerta Posterior'        ,$hwtRemolque->cajon_puerta_posterior));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Lado Derecho'            ,$hwtRemolque->cajon_lado_derecho));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Lado Izquierdo'          ,$hwtRemolque->cajon_lado_izquierdo));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Tipo de Piso'            ,$hwtRemolque->cajon_tipo_piso));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Sist. Levanta Carga'     ,$hwtRemolque->cajon_sistema_carga));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Capac. Levanta Carga'    ,$hwtRemolque->cajon_sistema_carga_capacidad));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Logistica'               ,$hwtRemolque->cajon_logistica));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Insulación'              ,$hwtRemolque->cajon_insulacion));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Placa Antirresbalante'   ,$hwtRemolque->cajon_placa_antirresbalante));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Paredes Cubiertas'       ,$hwtRemolque->cajon_paredes_cubierta));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Techo Translúcido'       ,$hwtRemolque->cajon_techo_traslucido));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Capacidad Montacarga'    ,$hwtRemolque->cajon_capacidad_montacarga));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Sistema Descanso'        ,$hwtRemolque->cajon_sistema_descanso));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Guarda Choque Post.' ,$hwtRemolque->cajon_guardachoque_posterior));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Dormitorio Cajón'        ,$hwtRemolque->cajon_dormitorio));
            reporteImprimeSeccion($arrayObjSeccion);
        }

        if($hwtRemolque->refrigeracion_incluido === 'true') {
            Reporter::increaseCurrentRow();
            Reporter::printVerticalSeparator('UNIDAD DE REFRIGERACION'  ,'FFFFFF','05486c');
            $arrayObjSeccion = array();
            array_push($arrayObjSeccion,creaEspecificacionSimple('Marca'                ,$hwtRemolque->refrigeracion_marca));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Modelo'               ,$hwtRemolque->refrigeracion_modelo));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Horas'                ,$hwtRemolque->refrigeracion_horas));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Año'                  ,$hwtRemolque->refrigeracion_ann));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Tipo'                 ,$hwtRemolque->refrigeracion_tipo));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Sist. Eléctrico (CA)' ,$hwtRemolque->refrigeracion_sistema_electrico));
            reporteImprimeSeccion($arrayObjSeccion);
        }

        Reporter::increaseCurrentRow();
        Reporter::printVerticalSeparator('OBSERVACIONES'  ,'FFFFFF','05486c');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Observaciones' ,$hwtRemolque->observaciones));
        reporteImprimeSeccion($arrayObjSeccion);

    } // Remolque

    Reporter::saveFile();

    $objArchivoGenerado = new \stdClass();
    $objArchivoGenerado->nombre   = $archivoGenerado;

    Emissary::prepareEnvelope();

    $availableInfo = true;
    $apiMessage = 'Archivo generado: ' . $archivoGenerado;
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('archivoGenerado' , $objArchivoGenerado);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function grabaRemolque(){
    Logger::enable(true,'grabaRemolque');
    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    Receiver::setApiParameterValue('tfVolqueteIncluido'      ,Receiver::getApiParameter('indIncluirVolquete'));
    Receiver::setApiParameterValue('tfCajonIncluido'         ,Receiver::getApiParameter('indIncluirCajon'));
    Receiver::setApiParameterValue('tfRefrigeracionIncluido' ,Receiver::getApiParameter('indIncluirRefrigeracion'));
    Receiver::setApiParameterValue('tfVin'                   ,Receiver::getApiParameter('tfVinPrincipal'));

    Logger::write('INcluir Voquete');
    Logger::write(Receiver::getApiParameter('indIncluirVolquete'));

    $objCamposRegistro   = Dataworker::setFieldsTable('hwt_remolque');

    Logger::write(json_encode($objCamposRegistro));

    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    $registroActualizado = true;
    $apiMessage = 'Registro actualizado en la Base de Datos';

    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addMessage('sql-ejecutado' , $sqlEjecutado);
    Emissary::addData('camposRegistro' , $objCamposRegistro);
    Dataworker::closeConnection();

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function datosRemolque(){
    $vin = Receiver::getApiParameter('vin');
    Emissary::prepareEnvelope();

    Dataworker::openConnection();
    $SqlUnidadUsada = "SELECT * FROM hwt_remolque WHERE vin = '$vin'";
    $resultHwtRemolque = Dataworker::executeQuery($SqlUnidadUsada);

    if($resultHwtRemolque->numRecords > 0) {
        $availableInfo = true;
        $apiMessage = 'Registro Localizado';
        Emissary::addMessage('info-api' , $apiMessage);
        Emissary::addData('hwtRemolque' , $resultHwtRemolque->data);
        Emissary::addData('numRecords'  , $resultHwtRemolque->numRecords);
    }
    else{
        $availableInfo = false;
        $apiMessage = 'No se encontró el Registro en la Base';
        Emissary::addMessage('info-api' , $apiMessage);
    }

    Dataworker::closeConnection();

    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function generaHojaEspecificaciones(){

    $codigoUnidad = Receiver::getApiParameter('codigoUnidad');
    $vin          = Receiver::getApiParameter('vin');

    $archivoGenerado = Reporter::openFile('hojaEspecificaciones');
    $nombreReporte = "Hoja de Especificaciones de la Unidad " . $codigoUnidad . ' (' . $vin . ')';
    Reporter::setMaxColumn('H');
    Reporter::prepareHeader($nombreReporte);

    // ECRC: Preparando el Titulo de las Columnas
    $arrayTituloColumnas = array(
        "22: ",
        "22: ",
        "22: ",
        "22: ",
        "22: ",
        "22: ",
        "22: ",
        "22: "
    );

    Reporter::prepareTitleColumns($arrayTituloColumnas);

    $dbSession = Dataworker::openConnection();
    $objCondicion = new \stdClass();
    $objCondicion->codigo        = Dataworker::equalToString($codigoUnidad);

    $hwtVehiculoUsado = Dataworker::findFirst('hwt_vehiculo',$objCondicion);
    if($hwtVehiculoUsado->activeRecord === '1') {
        $objCeldaSeccion = new \stdClass();
        $objCeldaSeccion->bgColor = '05486c';
        $objCeldaSeccion->fgColor = 'ffffff';
        $objCeldaSeccion->align   = 'left';

        $objCeldaTitulo = new \stdClass();
        $objCeldaTitulo->bgColor = 'cddae1';
        $objCeldaTitulo->align = 'right';


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///            B L O Q U E    D E    P R E S E N T A C I O N    D E   L A    I N F O R M A C I O N           ///
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // ECRC: Contacto
        Reporter::decreaseCurrentRow();

        $objCeldaTitulo = new \stdClass();
        $objCeldaTitulo->tipoPresentacion = 'medio';

        Reporter::printVerticalSeparator('CONTACTO'  ,'FFFFFF','05486c');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Flota'     ,$hwtVehiculoUsado->nombre_flota));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Contacto'  ,$hwtVehiculoUsado->contacto_nombre));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Correo'    ,$hwtVehiculoUsado->contacto_correo));

        $telefonoContacto = $hwtVehiculoUsado->contacto_telefono;
        if($hwtVehiculoUsado->contacto_extension !== ''){
            $telefonoContacto = $telefonoContacto . ' Ext: ' . $hwtVehiculoUsado->contacto_extension;
        }
        array_push($arrayObjSeccion,creaEspecificacionSimple('Telefono'  ,$telefonoContacto));

        reporteImprimeSeccion($arrayObjSeccion,$objCeldaTitulo);

        // ECRC: Direccion
        Reporter::printVerticalSeparator('DIRECCION'  ,'FFFFFF','05486c');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Direccion'     ,$hwtVehiculoUsado->direccion));
        reporteImprimeSeccion($arrayObjSeccion);

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Ciudad'        ,$hwtVehiculoUsado->ciudad));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Estado'        ,$hwtVehiculoUsado->estado));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Pais'          ,$hwtVehiculoUsado->pais));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Codigo Postal' ,$hwtVehiculoUsado->codigo_postal));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Complementos::Componentes
        Reporter::printVerticalSeparator('COMPONENTES'  ,'FFFFFF','05486c');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Frenos' ,$hwtVehiculoUsado->frenos));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Chasis' ,$hwtVehiculoUsado->chasis));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Generales::Codigos
        Reporter::printVerticalSeparator('CODIGO','FFFFFF','05486c');

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Tipo Unidad' ,$hwtVehiculoUsado->tipo_unidad));
        array_push($arrayObjSeccion,creaEspecificacionSimple('VIN'         ,$hwtVehiculoUsado->vin));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Marca.'      ,$hwtVehiculoUsado->marca));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Modelo'      ,$hwtVehiculoUsado->modelo));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Codigo'      ,$hwtVehiculoUsado->codigo));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Anio'        ,$hwtVehiculoUsado->ann_unidad));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Kilometraje' ,$hwtVehiculoUsado->modelo));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Generales::Generales
        Reporter::increaseCurrentRow();
        Reporter::printVerticalSeparator('GENERALES','FFFFFF','05486c');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Estado'          ,$hwtVehiculoUsado->estado_unidad));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Ubicacion'       ,$hwtVehiculoUsado->ubicacion));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Dir. Hidraulica' ,$hwtVehiculoUsado->direccion_hidraulica));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Aire Acond.'     ,$hwtVehiculoUsado->aire_acondicionado));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Hubometro'       ,$hwtVehiculoUsado->hubometro));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Suspension'      ,$hwtVehiculoUsado->suspension));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Tamanio'         ,$hwtVehiculoUsado->tamano_unidad));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Traslado'        ,$hwtVehiculoUsado->traslado));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Generales::Motor
        Reporter::printVerticalSeparator('MOTOR','FFFFFF','05486c');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Modelo Motor' ,$hwtVehiculoUsado->modelo_motor));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Motor'        ,$hwtVehiculoUsado->motor));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Motor CPL.'   ,$hwtVehiculoUsado->motor_cpl));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Motor Freno'  ,$hwtVehiculoUsado->motor_freno));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Motor Horas'  ,$hwtVehiculoUsado->motor_horas));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Potencia'     ,$hwtVehiculoUsado->potencia_motor));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Serie'        ,$hwtVehiculoUsado->serie_motor));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Generales::Sistema Hidruaulico
        Reporter::increaseCurrentRow();
        Reporter::printVerticalSeparator('SISTEMA HIDRAULICO','FFFFFF','05486c');

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Sistema Hidraulico' ,$hwtVehiculoUsado->sistema_hidraulico));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Componentes.'       ,$hwtVehiculoUsado->sistema_hidraulico_componentes));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Generales::Transmision
        Reporter::increaseCurrentRow();
        Reporter::printVerticalSeparator('TRANSMISION','FFFFFF','05486c');

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Tipo'   ,$hwtVehiculoUsado->tipo_transmision));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Marca'  ,$hwtVehiculoUsado->marca_transmision));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Modelo' ,$hwtVehiculoUsado->modelo_transmision));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Estado' ,$hwtVehiculoUsado->estado_transmision));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Velocidades' ,$hwtVehiculoUsado->velocidades));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Distancia Ejes' ,$hwtVehiculoUsado->distancia_ejes));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Relacion Dif.'  ,$hwtVehiculoUsado->relacion_dif));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Adicionales::Accesorios
        Reporter::increaseCurrentRow();
        Reporter::printVerticalSeparator('ACCESORIOS','FFFFFF','05486c');

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Faldon Chasis'    ,$hwtVehiculoUsado->faldones_chasis));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Copete Deflector' ,$hwtVehiculoUsado->copete_deflector));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Ext. Laterales'   ,$hwtVehiculoUsado->extensiones_laterales));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Defensa'          ,$hwtVehiculoUsado->defensa));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Vicera Exterior'  ,$hwtVehiculoUsado->vicera_exterior));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Sistema Escape'   ,$hwtVehiculoUsado->sistema_escape));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Toma Fuerza'      ,$hwtVehiculoUsado->toma_fuerza));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Espejos'          ,$hwtVehiculoUsado->espejos));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Adicionales::Apariencia
        Reporter::printVerticalSeparator('APARIENCIA','FFFFFF','05486c');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Pintura Nueva' ,$hwtVehiculoUsado->pintura_nueva));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Color'         ,$hwtVehiculoUsado->color));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Adicionales::Radio Instalado
        Reporter::increaseCurrentRow();
        Reporter::printVerticalSeparator('RADIO INSTALADO','FFFFFF','05486c');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Radio Instalado' ,$hwtVehiculoUsado->radio_instalado));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Adicionales::Cabina
        Reporter::increaseCurrentRow();
        Reporter::printVerticalSeparator('CABINA','FFFFFF','05486c');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Cabina'           ,$hwtVehiculoUsado->tipo_cabina));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Interior'         ,$hwtVehiculoUsado->cabina_tipo_interior));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Vestidura'        ,$hwtVehiculoUsado->cabina_tipo_vestidura));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Asiento Operador' ,$hwtVehiculoUsado->cabina_tipo_asiento_operador));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Asiento Copiloto' ,$hwtVehiculoUsado->cabina_tipo_asiento_copiloto));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Dormitorio'       ,$hwtVehiculoUsado->cabina_dormitorio));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Nivel Interior'   ,$hwtVehiculoUsado->cabina_nivel_interior));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Color Interior'   ,$hwtVehiculoUsado->cabina_color_interior));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Susp. Asientos'   ,$hwtVehiculoUsado->cabina_suspension_asientos));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Doble Cama'       ,$hwtVehiculoUsado->cabina_doble_cama));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Adicionales::Combustible
        Reporter::increaseCurrentRow();
        Reporter::printVerticalSeparator('COMBUSTIBLE','FFFFFF','05486c');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Tipo' ,$hwtVehiculoUsado->combustible_tipo));
        reporteImprimeSeccion($arrayObjSeccion);

        //ECRC: Adicionales::Combustible:Tanque 1 y 2
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Tanque 1 Material' ,$hwtVehiculoUsado->tanque1_material));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Tanque 2 Material' ,$hwtVehiculoUsado->tanque2_material));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Tanque 3 Material' ,$hwtVehiculoUsado->tanque3_material));

        reporteImprimeSeccion($arrayObjSeccion);

        //ECRC: Adicionales::Combustible:Tanque 3
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Tanque 1 Cap.' ,$hwtVehiculoUsado->tanque1_capacidad));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Tanque 2 Cap.' ,$hwtVehiculoUsado->tanque2_capacidad));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Tanque 3 Cap.' ,$hwtVehiculoUsado->tanque3_capacidad));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Complementos::Llantas Ejes:Delanteras
        Reporter::increaseCurrentRow();
        Reporter::printVerticalSeparator('LLANTAS EJES','FFFFFF','05486c');
        Reporter::printVerticalSeparator('LLANTAS DELANTERAS','FFFFFF','6991a6');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Rines Delanteros'   ,$hwtVehiculoUsado->rines_delanteros));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Medidas'            ,$hwtVehiculoUsado->llantas_delanteras_medidas));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Marca Eje Del.'     ,$hwtVehiculoUsado->eje_delantero_marca));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Cap. Eje Del.'      ,$hwtVehiculoUsado->eje_delantero_capacidad));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Posicion. Eje Del.' ,$hwtVehiculoUsado->eje_delantero_posicion));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Complementos::Llantas Ejes:Traseras
        Reporter::printVerticalSeparator('LLANTAS TRASERAS'  ,'FFFFFF','6991a6');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Rines Traseros'  ,$hwtVehiculoUsado->rines_traseros));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Medidas'         ,$hwtVehiculoUsado->llantas_traseras_medidas));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Marca Eje Tras.' ,$hwtVehiculoUsado->eje_trasero_marca));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Cap. Eje Tras.'  ,$hwtVehiculoUsado->eje_trasero_capacidad));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Tipo Eje'        ,$hwtVehiculoUsado->eje_trasero_tipo));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Complementos::Ruedas Extras
        Reporter::printVerticalSeparator('RUEDAS EXTRAS'  ,'FFFFFF','6991a6');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Tercer Eje'   ,$hwtVehiculoUsado->tercer_eje));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Quinta Rueda' ,$hwtVehiculoUsado->quinta_rueda));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Complementos::Componentes
        Reporter::increaseCurrentRow();
        Reporter::printVerticalSeparator('COMPONENTES'  ,'FFFFFF','05486c');
        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('Frenos' ,$hwtVehiculoUsado->frenos));
        array_push($arrayObjSeccion,creaEspecificacionSimple('Chasis' ,$hwtVehiculoUsado->chasis));
        reporteImprimeSeccion($arrayObjSeccion);

        // ECRC: Montaje de las Ruedas
        Reporter::increaseCurrentRow();
        Reporter::printVerticalSeparator('MONTAJE DE LAS RUEDAS'  ,'FFFFFF','05486c');
        $objFormato = new \stdClass();
        $objFormato->bgColor          = 'f0f4f6';
        $objFormato->align            = 'right';
        $objFormato->tipoPresentacion = 'simple';
        $objFormato->borderActive     = true;
        $objFormato->borderColor      = '05486c';

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_delantero_llanta_izq . '/32'));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,''));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,''));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_delantero_llanta_der . '/32'));
        reporteImprimeSeccion($arrayObjSeccion,$objFormato);

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_trasero1_llanta_izq_ext . '/32'));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_trasero1_llanta_izq_int . '/32'));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_trasero1_llanta_der_ext . '/32'));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_trasero1_llanta_der_int . '/32'));
        reporteImprimeSeccion($arrayObjSeccion,$objFormato);

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_trasero2_llanta_izq_ext . '/32'));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_trasero2_llanta_izq_int . '/32'));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_trasero2_llanta_der_ext . '/32'));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_trasero2_llanta_der_int . '/32'));
        reporteImprimeSeccion($arrayObjSeccion,$objFormato);

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_trasero3_llanta_izq_ext . '/32'));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_trasero3_llanta_izq_int . '/32'));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_trasero3_llanta_der_ext . '/32'));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->set_trasero3_llanta_der_int . '/32'));
        reporteImprimeSeccion($arrayObjSeccion,$objFormato);

        // ECRC: Montaje de las Ruedas::Leyenda de las LLantas
        $objFormatTitle = new \stdClass();
        $objFormatTitle->fontForeground = 'ffffff';
        $objFormatTitle->cellBackground = '5e8295';
        $objFormatTitle->fontSize       = 10;
        $objFormatTitle->fontBold       = true;

        $celdaActual = Reporter::getCurrentRow();
        $celdaInicial = 'E' . ($celdaActual - 4);
        $celdaFinal   = 'H' . ($celdaActual - 4);
        Reporter::printTitle($celdaInicial, $celdaFinal, 'LEYENDA DE LAS LLANTAS', $objFormatTitle);

        $objFormatCell = new \stdClass();
        $objFormatCell->fgColor  = '000000';
        $objFormatCell->bgColor  = 'ffffff';
        $objFormatCell->align    = 'left';
        $objFormatCell->fontSize = 6.5;
        $objFormatCell->fontBold = true;

        // ECRC: Leyenda de Llantas Seccion 1
        $celdaActual = 'E' . (Reporter::getCurrentRow() - 3);
        Reporter::writeCell($celdaActual,'C = Cortado',$objFormatCell);

        $celdaActual = 'F' . (Reporter::getCurrentRow() - 3);
        Reporter::writeCell($celdaActual,'S = Seco Partida',$objFormatCell);

        $celdaActual = 'G' . (Reporter::getCurrentRow() - 3);
        Reporter::writeCell($celdaActual,'B = Baja',$objFormatCell);

        $celdaActual = 'H' . (Reporter::getCurrentRow() - 3);
        Reporter::writeCell($celdaActual,'D = Diferente Labor o Porte',$objFormatCell);

        // ECRC: Leyenda de Llantas Seccion 2
        $celdaActual = 'E' . (Reporter::getCurrentRow() - 2);
        Reporter::writeCell($celdaActual,'R = Reencauchada',$objFormatCell);

        $celdaActual = 'F' . (Reporter::getCurrentRow() - 2);
        Reporter::writeCell($celdaActual,'V = Virgen',$objFormatCell);

        $celdaActual = 'G' . (Reporter::getCurrentRow() - 2);
        Reporter::writeCell($celdaActual,'I = Desgaste Irregular',$objFormatCell);

        $celdaActual = 'H' . (Reporter::getCurrentRow() - 2);
        Reporter::writeCell($celdaActual,'X = Reencauchado Multiple',$objFormatCell);

        // ECRC: Montaje de las Ruedas
        Reporter::increaseCurrentRow();
        Reporter::printVerticalSeparator('MONTAJE DE LOS FRENOS'  ,'FFFFFF','05486c');
        $objFormato = new \stdClass();
        $objFormato->bgColor          = 'f0f4f6';
        $objFormato->align            = 'right';
        $objFormato->tipoPresentacion = 'simple';
        $objFormato->borderActive     = true;
        $objFormato->borderColor      = '05486c';

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->eje_delantero_izq));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,''));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,''));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->eje_delantero_der));
        reporteImprimeSeccion($arrayObjSeccion,$objFormato);

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->eje_trasero1_izq));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,''));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,''));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->eje_trasero1_der));
        reporteImprimeSeccion($arrayObjSeccion,$objFormato);

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->eje_trasero2_izq));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,''));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,''));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->eje_trasero2_der));
        reporteImprimeSeccion($arrayObjSeccion,$objFormato);

        $arrayObjSeccion = array();
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->eje_trasero3_izq));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,''));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,''));
        array_push($arrayObjSeccion,creaEspecificacionSimple('' ,$hwtVehiculoUsado->eje_trasero3_der));
        reporteImprimeSeccion($arrayObjSeccion,$objFormato);

        // ECRC: Montaje de los Frenos::Leyenda de los Frenos
        $objFormatTitle = new \stdClass();
        $objFormatTitle->fontForeground = 'ffffff';
        $objFormatTitle->cellBackground = '5e8295';
        $objFormatTitle->fontSize       = 10;
        $objFormatTitle->fontBold       = true;

        $celdaActual = Reporter::getCurrentRow();
        $celdaInicial = 'E' . ($celdaActual - 4);
        $celdaFinal   = 'H' . ($celdaActual - 4);
        Reporter::printTitle($celdaInicial, $celdaFinal, 'LEYENDA DE LOS FRENOS', $objFormatTitle);

        $objFormatCell = new \stdClass();
        $objFormatCell->fgColor  = '000000';
        $objFormatCell->bgColor  = 'ffffff';
        $objFormatCell->align    = 'left';
        $objFormatCell->fontSize = 6.5;
        $objFormatCell->fontBold = true;

        $celdaActual = 'E' . (Reporter::getCurrentRow() - 3);
        Reporter::writeCell($celdaActual,'TM = Tambor Malo',$objFormatCell);

        $celdaActual = 'F' . (Reporter::getCurrentRow() - 3);
        Reporter::writeCell($celdaActual,'ZT = Zapata Trizada',$objFormatCell);

        $celdaActual = 'G' . (Reporter::getCurrentRow() - 3);
        Reporter::writeCell($celdaActual,'RL = Reten Linqueando (Sello)',$objFormatCell);

        // ECRC: Montaje de los Frenos::Leyenda de la Labor
        $objFormatTitle = new \stdClass();
        $objFormatTitle->fontForeground = 'ffffff';
        $objFormatTitle->cellBackground = '5e8295';
        $objFormatTitle->fontSize       = 10;
        $objFormatTitle->fontBold       = true;

        $celdaActual = Reporter::getCurrentRow();
        $celdaInicial = 'E' . ($celdaActual - 2);
        $celdaFinal   = 'H' . ($celdaActual - 2);
        Reporter::printTitle($celdaInicial, $celdaFinal, 'LEYENDA DE LA LABOR', $objFormatTitle);

        $objFormatCell = new \stdClass();
        $objFormatCell->fgColor      = '000000';
        $objFormatCell->bgColor      = 'ffffff';
        $objFormatCell->align        = 'left';
        $objFormatCell->fontSize     = 6.5;
        $objFormatCell->fontBold     = true;

        $celdaActual = 'E' . (Reporter::getCurrentRow() - 1);
        Reporter::writeCell($celdaActual,'A = Autopista (Direccion)',$objFormatCell);

        $celdaActual = 'F' . (Reporter::getCurrentRow() - 1);
        Reporter::writeCell($celdaActual,'T = Traccion',$objFormatCell);


        // ECRC: Presentando la Información del Reporte de la Condición
        $objCondicion = new \stdClass();
        $objCondicion->vin        = Dataworker::equalToString($hwtVehiculoUsado->vin);

        $hwtReporteCondicion = Dataworker::findFirst('hwt_reporte_condicion',$objCondicion);
        if($hwtReporteCondicion->activeRecord === '1'){
            // ECRC: Montaje de las Ruedas
            Reporter::increaseCurrentRow();
            Reporter::printVerticalSeparator('INSPECCION DE CONDICION DE LA UNIDAD'  ,'FFFFFF','05486c');

            $objCondicion = new \stdClass();
            $objCondicion->usuario        = Dataworker::equalToString($hwtReporteCondicion->usuario);

            $hwtUsuario = Dataworker::findFirst('hwt_usuario',$objCondicion);

            $arrayObjSeccion = array();
            array_push($arrayObjSeccion,creaEspecificacionSimple('Fecha Inspeccion' ,$hwtReporteCondicion->fecha_reporte));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Inspector'        ,$hwtUsuario->nombre));
            array_push($arrayObjSeccion,creaEspecificacionSimple('No. Reparaciones' ,$hwtReporteCondicion->num_reparaciones));
            array_push($arrayObjSeccion,creaEspecificacionSimple('Valor Reparacion' ,number_format(floatval($hwtReporteCondicion->precio_total_estimado), 2)));
            reporteImprimeSeccion($arrayObjSeccion);
        }
        else{
            Reporter::increaseCurrentRow();
            Reporter::printVerticalSeparator('NO SE HA REALIZADO INSPECCION A LA UNIDAD'  ,'FFFFFF','7f0000');
        }


    } // hwtVehiculoUsado

    Reporter::saveFile();

    $objArchivoGenerado = new \stdClass();
    $objArchivoGenerado->nombre   = $archivoGenerado;

    Emissary::prepareEnvelope();

    $availableInfo = true;
    $apiMessage = 'Archivo generado: ' . $archivoGenerado;
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('archivoGenerado' , $objArchivoGenerado);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();

    echo json_encode($objReturn);
}

function reporteUnidadUsada(){
    $filtroEstado = Receiver::getApiParameter('filtroEstado');

    $archivoGenerado = Reporter::openFile('reporteUnidadUsada');
    $nombreReporte = "Unidades Usadas";
    Reporter::prepareHeader($nombreReporte);

    //ECRC: Preparando el Titulo de las Columnas
    $arrayTituloColumnas = array(
        "15:ESTADO\rUNIDAD",
        "12:CODIGO",
        "20:VIN",
        "10:MODELO",
        "15:MARCA",
        "06:AÑO",
        "20:UBICACION",
        "20:COLOR",
        "10:MOTOR",
        "10:MODELO\rMOTOR",
        "12:POTENCIA\rMOTOR",
        "10:NUMERO\rSERIE",
        "17:MARCA\rTRANSMISION",
        "18:MODELO\rTRANSMISION",
        "16:VELOCIDADES",
        "16:RELACION\rDIFERENCIAL",
        "16:KILOMETRAJE",
        "20:DISTANCIA\rEJES",
        "16:PROPIETARIO\rANTERIOR",
        "10:PRECIO\rSIN IVA",
        "10:PRECIO\rCON IVA",
        "16:FECHA\rPUBLICACION",
        "16:FECHA\rVENTA"
    );

    Reporter::prepareTitleColumns($arrayTituloColumnas);

    $arrayCamposTabla = array(
        'estado_unidad',
        'codigo',
        'vin',
        'modelo',
        'marca',
        'ann_unidad',
        'ubicacion',
        'color',
        'motor',
        'modelo_motor',
        'potencia_motor',
        'numero_serie',
        'marca_transmision',
        'modelo_transmision',
        'velocidades',
        'relacion_dif',
        'kilometraje',
        'distancia_ejes',
        'propietario_anterior',
        'precio_sin_iva',
        'precio_con_iva',
        'fecha_publicacion',
        'fecha_venta'
    );

    $SqlUnidadUsada = "SELECT " . implode(',',$arrayCamposTabla) ." FROM hwt_vehiculo ";

    if($filtroEstado){
        $SqlUnidadUsada = $SqlUnidadUsada
            . " WHERE estado_unidad = '$filtroEstado'";
    }

    Dataworker::openConnection();
    $resultHwtUnidadUsada = Dataworker::executeQuery($SqlUnidadUsada);
    Reporter::writeContent($resultHwtUnidadUsada->data);
    Reporter::saveFile();

    $objArchivoGenerado = new \stdClass();
    $objArchivoGenerado->nombre   = $archivoGenerado;

    Emissary::prepareEnvelope();

    $availableInfo = true;
    $apiMessage = 'Archivo generado: ' . $archivoGenerado;
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('archivoGenerado' , $objArchivoGenerado);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();

    echo json_encode($objReturn);
}

function miniaturaImagenUnidad(){
    $archivoImagen = Receiver::getApiParameter('archivoImagen');
    $codigoUnidad  = Receiver::getApiParameter('codigo');
    $modeloUnidad  = Receiver::getApiParameter('modelo');

    Logger::enable(true,'miniaturaImagenUnidad');

    Logger::write('archivoImagen: ' . $archivoImagen);
    Logger::write('codigoUnidad: ' . $codigoUnidad);
    Logger::write('modeloUnidad: ' . $modeloUnidad);

    $arrayArchivo = explode('/',$archivoImagen);
    $ultimaPosicion = sizeof($arrayArchivo) - 1;

    Logger::write('ultimaPosicion: ' . $ultimaPosicion);

    $nombreArchivo = $arrayArchivo[$ultimaPosicion];

    $arrayNombreArchivo = explode('?',$nombreArchivo);
    $nombreArchivo = $arrayNombreArchivo[0];

    Logger::write('nombreArchivo: ' . $nombreArchivo);

    $pathFile = '../../recursos/imagen/' . $modeloUnidad .'_' . $codigoUnidad . '/' . $nombreArchivo;
    $newpathFile = '../../recursos/imagen/' . $modeloUnidad .'_' . $codigoUnidad . '/imagen_thumb.jpg';

    $binarioImagen = imagecreatefromjpeg($pathFile);
    $degrees = 0;
    $rotate = imagerotate($binarioImagen, $degrees, 0);

    Logger::write($newpathFile);
    unlink($pathFile); // Eliminar el Archivo
    $availableInfo = imagejpeg($rotate,$newpathFile);
    $availableInfo = imagejpeg($rotate,$pathFile);

    if($availableInfo){
        $apiMessage = 'Se ha establecido la Imagen como Miniatura en los Resultados de Búsqueda del Portal.';
    }
    else{
        $apiMessage = 'No se logró Procesar la Imagen';
    }

    Emissary::prepareEnvelope();

    Emissary::success($availableInfo);
    Emissary::addMessage('info-api' , $apiMessage);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function rotarImagenUnidad(){
    $archivoImagen = Receiver::getApiParameter('archivoImagen');
    $codigoUnidad  = Receiver::getApiParameter('codigo');
    $modeloUnidad  = Receiver::getApiParameter('modelo');

    Logger::enable(true,'rotarImagenUnidad');

    Logger::write('archivoImagen: ' . $archivoImagen);
    Logger::write('codigoUnidad: ' . $codigoUnidad);
    Logger::write('modeloUnidad: ' . $modeloUnidad);

    $arrayArchivo = explode('/',$archivoImagen);
    $ultimaPosicion = sizeof($arrayArchivo) - 1;

    Logger::write('ultimaPosicion: ' . $ultimaPosicion);

    $nombreArchivo = $arrayArchivo[$ultimaPosicion];

    $arrayNombreArchivo = explode('?',$nombreArchivo);
    $nombreArchivo = $arrayNombreArchivo[0];

    Logger::write('nombreArchivo: ' . $nombreArchivo);

    $pathFile = '../../recursos/imagen/' . $modeloUnidad .'_' . $codigoUnidad . '/' . $nombreArchivo;
    $newpathFile = '../../recursos/imagen/' . $modeloUnidad .'_' . $codigoUnidad . '/fixed_' . $nombreArchivo;

    $binarioImagen = imagecreatefromjpeg($pathFile);
    $degrees = 270;
    $rotate = imagerotate($binarioImagen, $degrees, 0);

    Logger::write($newpathFile);
    unlink($pathFile); // Eliminar el Archivo
    $availableInfo = imagejpeg($rotate,$pathFile);

    if($availableInfo){
        $apiMessage = 'Se ha rotado la imagen.';
    }
    else{
        $apiMessage = 'No se logró rotar la imagen';
    }

    Emissary::prepareEnvelope();

    Emissary::success($availableInfo);
    Emissary::addMessage('info-api' , $apiMessage);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function borrarImagenesUnidad(){
    $codigoUnidad = Receiver::getApiParameter('codigo');
    $modeloUnidad = Receiver::getApiParameter('modelo');

    $pathFile = '../../recursos/imagen/' . $modeloUnidad .'_' . $codigoUnidad . '/*.jpg';

    $files = glob($pathFile); // Extrae todos los Archivos del Directorio
    $numFilesDeleted = 0;
    $availableInfo = false;

    Logger::enable();
    foreach($files as $file){ // Iterar los Archivos
        if(is_file($file)){

            unlink($file); // Eliminar el Archivo
            $numFilesDeleted = $numFilesDeleted + 1;
            $availableInfo = true;
        }

    }

    if($availableInfo){
        $apiMessage = 'Se eliminaron ' . $numFilesDeleted . ' archivos.';
    }
    else{
        $apiMessage = 'No se encontraron archivos para eliminar';
    }


    Emissary::prepareEnvelope();

    Emissary::success($availableInfo);
    Emissary::addMessage('info-api' , $apiMessage);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function cargaImagenUnidad(){
    if(isset($_FILES)){
        $campoArchivoForm   = Receiver::getPostParameter('fieldFileLoad');
        $codigoUnidad       = Receiver::getPostParameter('codigo');
        $modeloUnidad       = Receiver::getPostParameter('modelo');
        $temp_file_name     = $_FILES[$campoArchivoForm]['tmp_name'];
        $original_file_name = $_FILES[$campoArchivoForm]['name'];

        // Find file extention
        $ext = explode ('.', $original_file_name);
        $ext = $ext [count ($ext) - 1];

        // Remove the extention from the original file name
        $file_name = str_replace ($ext, '', $original_file_name);

        $pathFile = '../../recursos/imagen/' . $modeloUnidad .'_' . $codigoUnidad . '/';

        if (!file_exists($pathFile)) {
            mkdir($pathFile, 0777, true);
        }

        $filecount = 0;
        $files = glob($pathFile . "*imagen*min*.jpg");
        if ($files){
            $filecount = count($files);
        }
        $filecount = $filecount + 1;

        if($filecount < 10){
            $filecount = '0' . $filecount;
        }

        $nuevoNombre = 'imagen' . $filecount . '-min.';
        $new_name = $pathFile . $nuevoNombre . $ext;

        if (move_uploaded_file ($temp_file_name, $new_name)) {
            $availableInfo = true;
            $apiMessage = 'Archivo cargado en el Servidor';
        } else {
            $availableInfo = false;
            $apiMessage = 'No se logró la transferencia del Archivo';
        }

        Emissary::prepareEnvelope();

        Emissary::success($availableInfo);
        Emissary::addMessage('info-api' , $apiMessage);

        $objReturn = Emissary::getEnvelope();
        echo json_encode($objReturn);

    }
}

function datosOpciones(){
    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    $objectOpcionesEstadoUnidad      = listaParametro('combos_unidades','estado');
    $objectOpcionesFiltroEstado      = listaParametro('combos_unidades','estado');
    $objectOpcionesTipoUnidad        = listaParametro('combos_unidades','tipo_unidad');
    $objectOpcionesUbicacion         = listaParametro('combos_unidades','ubicacion');
    $objectOpcionesModelo            = listaParametro('combos_unidades','modelo');
    $objectOpcionesFiltroModelo      = listaParametro('combos_unidades','modelo');
    $objectOpcionesMarca             = listaParametro('combos_unidades','marca');
    $objectOpcionesFiltroMarca       = listaParametro('combos_unidades','marca');
    $objectOpcionesColor             = listaParametro('combos_unidades','color');
    $objectOpcionesMotorMarca        = listaParametro('combos_unidades','motor_marca');
    $objectOpcionesMotorModelo       = listaParametro('combos_unidades','motor_modelo');
    $objectOpcionesPotenciaMotor     = listaParametro('combos_unidades','motor_potencia_hp');
    $objectOpcionesDormitorio        = listaParametro('combos_unidades','dormitorio');
    $objectDistanciaEjes             = listaParametro('combos_unidades','distancia_ejes');
    $objectTransmisionMarca          = listaParametro('combos_unidades','transmision_marca');
    $objectTransmisionModelo         = listaParametro('combos_unidades','transmision_modelo');
    $objectVelocidades               = listaParametro('combos_unidades','num_velocidades');
    $objectTipoTransmision           = listaParametro('combos_unidades','tipo_transmision');
    $objectSuspension                = listaParametro('combos_unidades','suspension');
    $objectEjeDelanteroMarca         = listaParametro('combos_unidades','eje_delantero_marca');
    $objectEjeDelanteroCapacidad     = listaParametro('combos_unidades','eje_delantero_capacidad');
    $objectEjeTraseroMarca           = listaParametro('combos_unidades','eje_trasero_marca');
    $objectEjeTraseroCapacidad       = listaParametro('combos_unidades','eje_trasero_capacidad');
    $objectTercerEje                 = listaParametro('combos_unidades','tercer_eje');
    $objectRelacionDiferencial       = listaParametro('combos_unidades','relacion_diferencial');
    $objectDireccionHidraulica       = listaParametro('combos_unidades','direccion_hidraulica');
    $objectMotorFreno                = listaParametro('combos_unidades','motor_freno');
    $objectAireAcondicionado         = listaParametro('combos_unidades','aire_acondicionado');
    $objectCombustibleTipo           = listaParametro('combos_unidades','combustible_tipo');
    $objectFaldonesChasis            = listaParametro('combos_unidades','faldones_chasis');
    $objectCopeteDeflector           = listaParametro('combos_unidades','deflector');
    $objectExtensionesLaterales      = listaParametro('combos_unidades','extensiones_laterales');
    $objectTipoCabina                = listaParametro('combos_unidades','tipo_cabina');
    $objectViceraExterior            = listaParametro('combos_unidades','vicera_exterior');
    $objectQuintaRueda               = listaParametro('combos_unidades','quinta_rueda');
    $objectLlantasDelanterasMedidas  = listaParametro('combos_unidades','medida_llanta_delantera');
    $objectLlantasTraserasMedidas    = listaParametro('combos_unidades','medida_llanta_trasera');
    $objectChasis                    = listaParametro('combos_unidades','chasis');
    $objectCabinaTipoInterior        = listaParametro('combos_unidades','cabina_tipo_interior');
    $objectCabinaTipoVestidura       = listaParametro('combos_unidades','cabina_tipo_vestidura');
    $objectCabinaTipoAsientoOperador = listaParametro('combos_unidades','cabina_tipo_asiento_operador');
    $objectCabinaTipoAsientoCopiloto = listaParametro('combos_unidades','cabina_tipo_asiento_copiloto');
    $objectCabinaColorInterior       = listaParametro('combos_unidades','cabina_color_interior');
    $objectCabinaDobleCama           = listaParametro('combos_unidades','cabina_doble_cama');
    $objectCombustibleTanques        = listaParametro('combos_unidades','combustible_tanques');
    $objectCombustibleCapacidad      = listaParametro('combos_unidades','combustible_capacidad');
    $objectDefensa                   = listaParametro('combos_unidades','defensa');
    $objectSistemaEscape             = listaParametro('combos_unidades','sistema_escape');
    $objectRinesDelanteros           = listaParametro('combos_unidades','rines_delanteros');
    $objectRinesTraseros             = listaParametro('combos_unidades','rines_traseros');
    $objectTomaFuerza                = listaParametro('combos_unidades','toma_fuerza');
    $objectFrenos                    = listaParametro('combos_unidades','frenos');
    $objectEspejos                   = listaParametro('combos_unidades','espejos');
    $objectSistemaHidraulico         = listaParametro('combos_unidades','sistema_hidraulico');
    $objectPinturaNueva              = listaParametro('combos_unidades','pintura_nueva');
    $objectTamanoUnidad              = listaParametro('combos_unidades','tamano_unidad');
    $objectEjeTraseroTipo            = listaParametro('combos_unidades','eje_trasero_tipo');
    $objectRadioInstalado            = listaParametro('combos_unidades','radio_instalado');

    $objectVolqueteComposicion              = listaParametro('combos_remolque','volquete_composicion');
    $objectVolqueteFormaAlzaBalde           = listaParametro('combos_remolque','volquete_forma_alza_balde');
    $objectVolqueteCubiertaCabina           = listaParametro('combos_remolque','volquete_cubierta_cabina');
    $objectVolqueteSistemaCarpa             = listaParametro('combos_remolque','volquete_sistema_carpa');
    $objectVolqueteEstructuraInferior       = listaParametro('combos_remolque','volquete_estructura_inferior');
    $objectVolquetePuerta                   = listaParametro('combos_remolque','volquete_puerta');
    $objectVolqueteCalefaccionBalde         = listaParametro('combos_remolque','volquete_calefaccion_balde');
    $objectVolquetePinCuernoRemolque        = listaParametro('combos_remolque','volquete_pin_cuerno_remolque');
    $objectVolqueteConexionElectricidadAire = listaParametro('combos_remolque','volquete_conexion_electricidad_aire');
    $objectCajonTipo                        = listaParametro('combos_remolque','cajon_tipo');
    $objectCajonConstruccion                = listaParametro('combos_remolque','cajon_construccion');
    $objectCajonPuertaPosterior             = listaParametro('combos_remolque','cajon_puerta_posterior');
    $objectCajonLadoDerecho                 = listaParametro('combos_remolque','cajon_lado_derecho');
    $objectCajonLadoIzquierdo               = listaParametro('combos_remolque','cajon_lado_izquierdo');
    $objectCajonTipoPiso                    = listaParametro('combos_remolque','cajon_tipo_piso');
    $objectCajonSistemaCarga                = listaParametro('combos_remolque','cajon_sistema_carga');
    $objectCajonLogistica                   = listaParametro('combos_remolque','cajon_logistica');
    $objectCajonInsulacion                  = listaParametro('combos_remolque','cajon_insulacion');
    $objectCajonPlacaAntirresbalante        = listaParametro('combos_remolque','cajon_placa_antirresbalante');
    $objectCajonParedesCubierta             = listaParametro('combos_remolque','cajon_paredes_cubierta');
    $objectCajonTechoTraslucido             = listaParametro('combos_remolque','cajon_techo_traslucido');
    $objectCajonCapacidadMontacarga         = listaParametro('combos_remolque','cajon_capacidad_montacarga');
    $objectCajonSistemaDescanso             = listaParametro('combos_remolque','cajon_sistema_descanso');
    $objectCajonGuardachoquePosterior       = listaParametro('combos_remolque','cajon_guardachoque_posterior');
    $objectCajonDormitorio                  = listaParametro('combos_remolque','cajon_dormitorio');
    $objectRefrigeracionMarca               = listaParametro('combos_remolque','refrigeracion_marca');
    $objectRefrigeracionTipo                = listaParametro('combos_remolque','refrigeracion_tipo');
    $objectRefrigeracionSistemaElectrico    = listaParametro('combos_remolque','refrigeracion_sistema_electrico');

    //----------------------------------------------------------//
    // ECRC: Extrayendo las Reglas para el Cálculo de los Años  //
    //----------------------------------------------------------//
    $arrayParamtrosAnn = array();
    for($iCiclo = 1; $iCiclo <= 10; $iCiclo++){
        $reglaCalculo = 'regla_calculo_' . $iCiclo;
        $objectParametro  = valorParametro('regla_calculo_ann',$reglaCalculo);
        if($objectParametro !== null){
            array_push($arrayParamtrosAnn,$objectParametro);
        }
    }

    $objectParametrosAnn = (object) $arrayParamtrosAnn;

    Dataworker::closeConnection();


    $arrayOpcionesFiltroModelo = (array) $objectOpcionesFiltroModelo;
    $objValue = (object) [
        codigo      => 'ALL',
        descripcion => 'TODOS LOS MODELOS',
        valor       => 'TODAS'
    ];
    array_push($arrayOpcionesFiltroModelo,$objValue);
    $objectOpcionesFiltroModelo = (object) $arrayOpcionesFiltroModelo;

    $arrayOpcionesFiltroMarca = (array) $objectOpcionesFiltroMarca;
    $objValue = (object) [
        codigo      => 'ALL',
        descripcion => 'TODAS LAS MARCAS',
        valor       => 'TODAS'
    ];
    array_push($arrayOpcionesFiltroMarca,$objValue);
    $objectOpcionesFiltroMarca = (object) $arrayOpcionesFiltroMarca;

    $availableInfo = true;
    $apiMessage = 'Información para Opciones de Formulario';
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('opcionesEstadoUnidad'              , $objectOpcionesEstadoUnidad);
    Emissary::addData('opcionesFiltroEstado'              , $objectOpcionesFiltroEstado);
    Emissary::addData('opcionesTipoUnidad'                , $objectOpcionesTipoUnidad);
    Emissary::addData('opcionesUbicacion'                 , $objectOpcionesUbicacion);
    Emissary::addData('opcionesModelo'                    , $objectOpcionesModelo);
    Emissary::addData('opcionesFiltroModelo'              , $objectOpcionesFiltroModelo);
    Emissary::addData('opcionesMarca'                     , $objectOpcionesMarca);
    Emissary::addData('opcionesFiltroMarca'               , $objectOpcionesFiltroMarca);
    Emissary::addData('opcionesColor'                     , $objectOpcionesColor);
    Emissary::addData('opcionesMotor'                     , $objectOpcionesMotorMarca);
    Emissary::addData('opcionesModeloMotor'               , $objectOpcionesMotorModelo);
    Emissary::addData('opcionesPotenciaMotor'             , $objectOpcionesPotenciaMotor);
    Emissary::addData('opcionesCabinaDormitorio'          , $objectOpcionesDormitorio);
    Emissary::addData('opcionesDistanciaEjes'             , $objectDistanciaEjes);
    Emissary::addData('opcionesMarcaTransmision'          , $objectTransmisionMarca);
    Emissary::addData('opcionesModeloTransmision'         , $objectTransmisionModelo);
    Emissary::addData('opcionesVelocidades'               , $objectVelocidades);
    Emissary::addData('opcionesTipoTransmision'           , $objectTipoTransmision);
    Emissary::addData('opcionesSuspension'                , $objectSuspension);
    Emissary::addData('opcionesEjeDelanteroMarca'         , $objectEjeDelanteroMarca);
    Emissary::addData('opcionesEjeDelanteroCapacidad'     , $objectEjeDelanteroCapacidad);
    Emissary::addData('opcionesEjeTraseroMarca'           , $objectEjeTraseroMarca);
    Emissary::addData('opcionesEjeTraseroCapacidad'       , $objectEjeTraseroCapacidad);
    Emissary::addData('opcionesEjeTraseroTipo'            , $objectEjeTraseroTipo);
    Emissary::addData('opcionesTercerEje'                 , $objectTercerEje);
    Emissary::addData('opcionesRelacionDif'               , $objectRelacionDiferencial);
    Emissary::addData('opcionesDireccionHidraulica'       , $objectDireccionHidraulica);
    Emissary::addData('opcionesMotorFreno'                , $objectMotorFreno);
    Emissary::addData('opcionesAireAcondicionado'         , $objectAireAcondicionado);
    Emissary::addData('opcionesCombustibleTipo'           , $objectCombustibleTipo);
    Emissary::addData('opcionesFaldonesChasis'            , $objectFaldonesChasis);
    Emissary::addData('opcionesCopeteDeflector'           , $objectCopeteDeflector);
    Emissary::addData('opcionesExtensionesLaterales'      , $objectExtensionesLaterales);
    Emissary::addData('opcionesTipoCabina'                , $objectTipoCabina);
    Emissary::addData('opcionesViceraExterior'            , $objectViceraExterior);
    Emissary::addData('opcionesQuintaRueda'               , $objectQuintaRueda);
    Emissary::addData('opcionesLlantasDelanterasMedidas'  , $objectLlantasDelanterasMedidas);
    Emissary::addData('opcionesLlantasTraserasMedidas'    , $objectLlantasTraserasMedidas);
    Emissary::addData('opcionesChasis'                    , $objectChasis);
    Emissary::addData('opcionesCabinaTipoInterior'        , $objectCabinaTipoInterior);
    Emissary::addData('opcionesCabinaTipoVestidura'       , $objectCabinaTipoVestidura);
    Emissary::addData('opcionesCabinaTipoAsientoOperador' , $objectCabinaTipoAsientoOperador);
    Emissary::addData('opcionesCabinaTipoAsientoCopiloto' , $objectCabinaTipoAsientoCopiloto);
    Emissary::addData('opcionesCabinaColorInterior'       , $objectCabinaColorInterior);
    Emissary::addData('opcionesCabinaDobleCama'           , $objectCabinaDobleCama);
    Emissary::addData('opcionesTanque1Material'           , $objectCombustibleTanques);
    Emissary::addData('opcionesTanque1Capacidad'          , $objectCombustibleCapacidad);
    Emissary::addData('opcionesTanque2Material'           , $objectCombustibleTanques);
    Emissary::addData('opcionesTanque2Capacidad'          , $objectCombustibleCapacidad);
    Emissary::addData('opcionesTanque3Material'           , $objectCombustibleTanques);
    Emissary::addData('opcionesTanque3Capacidad'          , $objectCombustibleCapacidad);
    Emissary::addData('opcionesDefensa'                   , $objectDefensa);
    Emissary::addData('opcionesSistemaEscape'             , $objectSistemaEscape);
    Emissary::addData('opcionesRinesDelanteros'           , $objectRinesDelanteros);
    Emissary::addData('opcionesRinesTraseros'             , $objectRinesTraseros);
    Emissary::addData('opcionesTomaFuerza'                , $objectTomaFuerza);
    Emissary::addData('opcionesEspejos'                   , $objectEspejos);
    Emissary::addData('opcionesFrenos'                    , $objectFrenos);
    Emissary::addData('opcionesSistemaHidraulico'         , $objectSistemaHidraulico);
    Emissary::addData('opcionesPinturaNueva'              , $objectPinturaNueva);
    Emissary::addData('opcionesTamanoUnidad'              , $objectTamanoUnidad);
    Emissary::addData('parametrosCalculoAnn'              , $objectParametrosAnn);
    Emissary::addData('opcionesRadioInstalado'            , $objectRadioInstalado);

    Emissary::addData('opcionesVolqueteComposicion'              , $objectVolqueteComposicion);
    Emissary::addData('opcionesVolqueteFormaAlzaBalde'           , $objectVolqueteFormaAlzaBalde);
    Emissary::addData('opcionesVolqueteCubiertaCabina'           , $objectVolqueteCubiertaCabina);
    Emissary::addData('opcionesVolqueteSistemaCarpa'             , $objectVolqueteSistemaCarpa);
    Emissary::addData('opcionesVolqueteEstructuraInferior'       , $objectVolqueteEstructuraInferior);
    Emissary::addData('opcionesVolquetePuerta'                   , $objectVolquetePuerta);
    Emissary::addData('opcionesVolqueteCalefaccionBalde'         , $objectVolqueteCalefaccionBalde);
    Emissary::addData('opcionesVolquetePinCuernoRemolque'        , $objectVolquetePinCuernoRemolque);
    Emissary::addData('opcionesVolqueteConexionElectricidadAire' , $objectVolqueteConexionElectricidadAire);
    Emissary::addData('opcionesCajonTipo'                        , $objectCajonTipo);
    Emissary::addData('opcionesCajonConstruccion'                , $objectCajonConstruccion);
    Emissary::addData('opcionesCajonPuertaPosterior'             , $objectCajonPuertaPosterior);
    Emissary::addData('opcionesCajonLadoDerecho'                 , $objectCajonLadoDerecho);
    Emissary::addData('opcionesCajonLadoIzquierdo'               , $objectCajonLadoIzquierdo);
    Emissary::addData('opcionesCajonTipoPiso'                    , $objectCajonTipoPiso);
    Emissary::addData('opcionesCajonSistemaCarga'                , $objectCajonSistemaCarga);
    Emissary::addData('opcionesCajonLogistica'                   , $objectCajonLogistica);
    Emissary::addData('opcionesCajonInsulacion'                  , $objectCajonInsulacion);
    Emissary::addData('opcionesCajonPlacaAntirresbalante'        , $objectCajonPlacaAntirresbalante);
    Emissary::addData('opcionesCajonParedesCubierta'             , $objectCajonParedesCubierta);
    Emissary::addData('opcionesCajonTechoTraslucido'             , $objectCajonTechoTraslucido);
    Emissary::addData('opcionesCajonCapacidadMontacarga'         , $objectCajonCapacidadMontacarga);
    Emissary::addData('opcionesCajonSistemaDescanso'             , $objectCajonSistemaDescanso);
    Emissary::addData('opcionesCajonGuardachoquePosterior'       , $objectCajonGuardachoquePosterior);
    Emissary::addData('opcionesCajonDormitorio'                  , $objectCajonDormitorio);
    Emissary::addData('opcionesRefrigeracionMarca'               , $objectRefrigeracionMarca);
    Emissary::addData('opcionesRefrigeracionTipo'                , $objectRefrigeracionTipo);
    Emissary::addData('opcionesRefrigeracionSistemaElectrico'    , $objectRefrigeracionSistemaElectrico);

    Emissary::success($availableInfo);

    Emissary::deliverEnvelope();
}

function datosUnidadUsada(){
    $codigoUnidad = Receiver::getApiParameter('codigoUnidad');
    Emissary::prepareEnvelope();

    Dataworker::openConnection();
    $SqlUnidadUsada = "SELECT * FROM hwt_vehiculo WHERE codigo = '$codigoUnidad'";
    $resultHwtUnidadUsada = Dataworker::executeQuery($SqlUnidadUsada);

    if($resultHwtUnidadUsada->numRecords > 0) {
        $availableInfo = true;
        $apiMessage = 'Registro Localizado';
        Emissary::addMessage('info-api' , $apiMessage);
        Emissary::addData('hwtUnidadUsada' , $resultHwtUnidadUsada->data);
        Emissary::addData('numRecords'     , $resultHwtUnidadUsada->numRecords);
    }
    else{
        $availableInfo = false;
        $apiMessage = 'No se encontró el Registro en la Base';
        Emissary::addMessage('info-api' , $apiMessage);
    }

    Dataworker::closeConnection();

    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function listaUnidadUsada(){
    Logger::enable(true,'listaUnidadUsada');

    Emissary::prepareEnvelope();

    $codigoUnidadBusca = Receiver::getApiParameter('codigoBusca');
    $filtroEstado      = Receiver::getApiParameter('filtroEstado');
    $busquedaVin       = Receiver::getApiParameter('busquedaVin');

    Dataworker::openConnection();
    $SqlUnidadUsada = "SELECT * FROM hwt_vehiculo ";

    if($codigoUnidadBusca){
        $SqlUnidadUsada = $SqlUnidadUsada
            . " WHERE codigo = $codigoUnidadBusca";
    }
    if($filtroEstado and $filtroEstado !== 'todo'){
        $SqlUnidadUsada = $SqlUnidadUsada
            . " WHERE estado_unidad = '$filtroEstado'";
    }

    if($filtroEstado === 'todo'){
        $SqlUnidadUsada = $SqlUnidadUsada
            . " WHERE vin != ''";
    }


    Logger::write('$busquedaVin:' . $busquedaVin);
    if(trim($busquedaVin) !== ''){

        $SqlUnidadUsada = $SqlUnidadUsada
            . " AND estado_unidad != ''";

        $filtroModelo = Receiver::getApiParameter('cbxFiltroModelo');
        $filtroMarca  = Receiver::getApiParameter('cbxFiltroMarca');
        $vinBusca     = Receiver::getApiParameter('tfBuscaVin');

        if(!strpos('x'.$filtroModelo,'TOD')){
            $SqlUnidadUsada = $SqlUnidadUsada
                . " AND modelo = '$filtroModelo'";
        }

        if(!strpos('x'.$filtroMarca,'TOD')){
            $SqlUnidadUsada = $SqlUnidadUsada
                . " AND marca = '$filtroMarca'";
        }

        if(trim($vinBusca) !== ''){
            $SqlUnidadUsada = $SqlUnidadUsada
                . " AND vin LIKE '%$vinBusca%'";
        }
    }

    $SqlUnidadUsada = $SqlUnidadUsada . ' '
                    . 'ORDER BY estado_unidad, marca, modelo';

    Logger::write('$SqlUnidadUsada: ' . $SqlUnidadUsada);

    $resultHwtUnidadUsada = Dataworker::executeQuery($SqlUnidadUsada);

    if($resultHwtUnidadUsada->numRecords > 0) {
        $availableInfo = true;
        $apiMessage = 'Registros Localizados';
        Emissary::addMessage('info-api' , $apiMessage);
        Emissary::addData('hwtUnidadUsada' , $resultHwtUnidadUsada->data);
        Emissary::addData('numRecords'     , $resultHwtUnidadUsada->numRecords);
    }
    else{
        $availableInfo = false;
        $apiMessage = 'No hay Registros en la Base';
        Emissary::addMessage('info-api' , $apiMessage);
    }

    Dataworker::closeConnection();

    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function grabaUnidadUsada(){
    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    $objCamposRegistro   = Dataworker::setFieldsTable('hwt_vehiculo');
    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    $registroActualizado = true;
    $apiMessage = 'Registro actualizado en la Base de Datos';

    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addMessage('sql-ejecutado' , $sqlEjecutado);
    Emissary::addData('camposRegistro' , $objCamposRegistro);
    Dataworker::closeConnection();

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function eliminaUnidadUsada(){
    $codigoUnidad = Receiver::getApiParameter('codigoUnidad');
    Emissary::prepareEnvelope();

    $objFieldsRecord = (object) [
        tableName => 'hwt_vehiculo',
        keyField  => 'codigo',
        keyValue  => $codigoUnidad
    ];

    Dataworker::openConnection();
    $resultadoSql = Dataworker::deleteRecord($objFieldsRecord);
    Dataworker::closeConnection();

    if($resultadoSql->success){
        $apiMessage = 'Se ha eliminado la Unidad con Código ' . $codigoUnidad;
    }
    else{
        $apiMessage = 'No se logró eliminar la Unidad con Código ' . $codigoUnidad;
    }
    Emissary::success($resultadoSql->success);
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('resultadoSQL' , $resultadoSql);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}
/* ECRC: Bloque Principal de Ejecución */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);