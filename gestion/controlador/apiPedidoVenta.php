<?php
require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Dataworker.php');
require_once('../recurso/clase/Logger.php');
require_once('../recurso/clase/Mnemea.php');
require_once('../recurso/clase/Reporter.php');
require_once('apiConfigurador.php');
require_once('apiCatalogoGeneral.php');

function cancelarPedidoVenta(){
    Logger::enable(true,'cancelarPedido');
    Dataworker::openConnection();

    $numPedido = Receiver::getApiParameter('tfNumPedido');
    $userId    = Receiver::getApiParameter('user_id');

    Logger::write('Iniciando la cancelacion del Pedido: ' . $numPedido);

    Emissary::prepareEnvelope();

    $mensaje = 'Cancelación de Pedido de Venta.';
    if(!evaluaSituacionPedido($numPedido)){
        $procesoCorrecto = false;
        $mensaje = 'La Situación del Pedido de Venta no permite Cancelación.';
    }
    else{
        // ECRC: Localizando el Pedido
        $objCondicion = new \stdClass();
        $objCondicion->num_pedido        = Dataworker::equalToValue($numPedido);
        $hwtPedidoVenta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

        Logger::write('Va por aqui');

        Receiver::resetApiParameters();
        $hwtPedidoVenta->situacion_pedido = '6';
        $objCamposRegistro = Dataworker::setFieldsTable('hwt_pedido_venta',$hwtPedidoVenta);

        Dataworker::updateRecord($objCamposRegistro);

        registraActualizacion($numPedido,$userId,'cancela');
        $mensaje = 'El Pedido ' . $numPedido . ' ha sido Cancelado.';
        $procesoCorrecto = true;
    }

    Dataworker::closeConnection();

    datosPedidoVenta($numPedido,$mensaje,$procesoCorrecto);
    return;
}

function asignarUnidadesPedido($pNumPedido,$pAccion){
    Emissary::prepareEnvelope();
    Dataworker::openConnection();

    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue  ($pNumPedido);
    $hwtPedidoVentaLinea = Dataworker::getRecords('hwt_pedido_venta_linea',$objCondicion);

    if($hwtPedidoVentaLinea->numRecords > 0) {

        // ECRC: Cargando la Información de la Unidad en el Registro de Unidad de Cotización
        foreach($hwtPedidoVentaLinea->data as $recordPedidoVentaLinea){

            Logger::write('Asignando las Unidades: ' . $recordPedidoVentaLinea->codigo);

            $estadoUnidad = 'DISPONIBLE';
            switch ($pAccion){
                case 'asignar':
                    $estadoUnidad = 'ASIGNADO';
                    break;
                case 'deasignar':
                    $estadoUnidad = 'DISPONIBLE';
                    break;
            }

            // ECRC: Actualizando el Estado de la Unidad
            Receiver::resetApiParameters();
            $hwtVehiculo = new \stdClass();
            $hwtVehiculo->codigo        = $recordPedidoVentaLinea->codigo;
            $hwtVehiculo->estado_unidad = $estadoUnidad;

            $objCamposRegistro = Dataworker::setFieldsTable('hwt_vehiculo',$hwtVehiculo);

            Dataworker::updateRecord($objCamposRegistro);
        }

        $availableInfo = true;
    }
    else{
        $availableInfo = false;
    }

    return $availableInfo;
}

function desligarIntegracionFactura(){
    $numPedido    = Receiver::getApiParameter('tfNumPedido');

    grabaIntegracionFactura($numPedido,'desligar');
}

function grabaIntegracionFactura($pNumPedido = null, $pAccion = null){
    Dataworker::openConnection();

    if(!isset($pNumPedido)){
        $numPedido    = Receiver::getApiParameter('tfNumPedidoIntegra');
        $NumPedidoERP = Receiver::getApiParameter('tfIntegraNumPedidoErp');
        $FacturaFecha = Receiver::getApiParameter('dfIntegraFacturaFecha');
        $FacturaSerie = Receiver::getApiParameter('tfIntegraFacturaSerie');
        $FacturaFolio = Receiver::getApiParameter('tfIntegraFacturaFolio');
        $userId       = Receiver::getApiParameter('user_id');
    }else{
        $numPedido    = $pNumPedido;
        $NumPedidoERP = '';
        $FacturaFecha = '';
        $FacturaSerie = '';
        $FacturaFolio = '';
        $userId       = Receiver::getApiParameter('user_id');
    }


    Emissary::prepareEnvelope();
    // ECRC: Localizando el Pedido
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido        = Dataworker::equalToValue($numPedido);
    $hwtPedidoVenta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

    $mensaje = '';
    $procesoCorrecto = false;
    if(intval($hwtPedidoVenta->situacion_pedido) >= 4 and
       intval($hwtPedidoVenta->situacion_pedido) < 6  and
       $pAccion === null){
        $mensaje = $mensaje
            . 'El Pedido ya tiene una Factura registrada.';
        $procesoCorrecto = false;
    }
    else{
        Receiver::resetApiParameters();

        if(!isset($pAccion)){
            $hwtPedidoVenta->situacion_pedido = '4';
            $mensaje = 'Se ha registrado la Factura para el Pedido ' . $numPedido . '.';
        }
        else{
            switch($pAccion){
                case 'desligar':
                    $hwtPedidoVenta->situacion_pedido = '3';
                    $mensaje = 'Se ha desligado la Factura para el Pedido ' . $numPedido . '.</br>'
                    . 'El Pedido quedo en Situación de ASIGNADO.';
                    break;
                case 'proteger':
                    break;
            }
        }

        $hwtPedidoVenta->integracion_num_pedido_erp = $NumPedidoERP;
        $hwtPedidoVenta->integracion_factura_fecha  = $FacturaFecha;
        $hwtPedidoVenta->integracion_factura_serie  = $FacturaSerie;
        $hwtPedidoVenta->integracion_factura_folio  = $FacturaFolio;
        $objCamposRegistro = Dataworker::setFieldsTable('hwt_pedido_venta',$hwtPedidoVenta);

        Dataworker::updateRecord($objCamposRegistro);
        registraActualizacion($numPedido,$userId,'actualiza');

        $procesoCorrecto = true;
    }

    Dataworker::closeConnection();
    datosPedidoVenta($numPedido,$mensaje,$procesoCorrecto);
    return;
}

function generarFormatoPedido($pObjImpresion){
    Logger::enable(true,'formatoPedidoVenta');
    Dataworker::openConnection();

    if(isset($pObjImpresion)){
        $numPedidoVenta = $pObjImpresion->num_pedido_venta;
    }
    else{
        $numPedidoVenta = Receiver::getApiParameter('numPedido');
    }

    $archivoGenerado = Reporter::openFile('reportePedidoVenta' . $numPedidoVenta);
    $nombreReporte = "Pedido de Venta de Unidades Usadas (No. " . $numPedidoVenta . ")";
    Reporter::setMaxColumn('D');
    Reporter::prepareHeader(utf8_encode($nombreReporte));

    //ECRC: Preparando el Titulo de las Columnas
    $arrayTituloColumnas = array(
        "30:",
        "30:",
        "30:",
        "30:",
    );

    Reporter::prepareTitleColumns($arrayTituloColumnas);

    Reporter::decreaseCurrentRow(1);

    // ECRC: Localizando la PedidoVenta
    $objCondicion             = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue($numPedidoVenta);
    $hwtPedidoVenta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

    // ECRC: Localizando al Cliente
    $objCondicion = new \stdClass();
    $objCondicion->codigo_cliente        = Dataworker::equalToValue($hwtPedidoVenta->codigo_cliente);
    $hwtCliente = Dataworker::findFirst('hwt_cliente',$objCondicion);

    /*
    $fgColor = '000000';
    $bgColor = 'FFFFFF';
    Reporter::printVerticalSeparator('',$fgColor,$bgColor);
    */

    //ECRC: Comenzando la Esritura del Contenido del Reporte

    //////////////////////////////////////////
    // ECRC: Sección de Datos de la Empresa //
    //////////////////////////////////////////
    $sysEmpresa = Dataworker::findFirst('sys_empresa');

    $fgColor = 'FFFFFF';
    $bgColor = '054F7D';
    $valorSeparador = 'DATOS DE ' . strtoupper($sysEmpresa->nombre_empresa);
    Reporter::printVerticalSeparator($valorSeparador,$fgColor,$bgColor);

    $arrayObjSeccion = array();
    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Gerente Regional' ,
            nombreUsuario($hwtPedidoVenta->codigo_gerente_regional)));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Vendedor ',
            nombreUsuario($hwtPedidoVenta->codigo_vendedor)));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Concesionario',
            descripcionConsecionario($hwtPedidoVenta->codigo_consecionario)));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Sucursal',
            descripcionConsecionarioSucursal(
                $hwtPedidoVenta->codigo_consecionario,
                $hwtPedidoVenta->codigo_sucursal)));

    $pObjFormato = new \stdClass();
    $pObjFormato->tipoPresentacion = 'custom';
    $pObjFormato->celdaSaltoLinea  = 4;

    Reporter::printSectionData($arrayObjSeccion,$pObjFormato);

    //////////////////////////////////////////
    // ECRC: Sección de Datos del Cliente   //
    //////////////////////////////////////////
    $valorSeparador = 'DATOS DEL CLIENTE ' . strtoupper($hwtCliente->nombre_corto);
    Reporter::printVerticalSeparator($valorSeparador,$fgColor,$bgColor);

    $arrayObjSeccion = array();
    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Razón Social' ,
            $hwtCliente->razon_social));


    $domicilioFiscal = $hwtCliente->dir_calle . ' '
        . $hwtCliente->dir_num_exterior . ' '
        . (intval($hwtCliente->dir_num_interior) > 0 ? $hwtCliente->dir_num_interior : '') . ' '
        . 'Col. ' . $hwtCliente->dir_colonia;

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Dirección' ,
            strtoupper($domicilioFiscal)));

    $pObjFormato = new \stdClass();
    $pObjFormato->tipoPresentacion = 'custom';
    $pObjFormato->celdaSaltoLinea  = 2;
    Reporter::printSectionData($arrayObjSeccion,$pObjFormato);
    Reporter::decreaseCurrentRow();

    $arrayObjSeccion = array();
    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Municipio/Delegación' ,
            $hwtCliente->dir_municipio));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('RFC' ,
            $hwtCliente->rfc));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Estado' ,
            $hwtCliente->dir_estado));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Tel. Contacto' ,
            $hwtCliente->rfc));

    $objFormatoCelda = new \stdClass();
    $objFormatoCelda->fgColor = '000000';
    $objFormatoCelda->bgColor = 'FFFFFF';
    $objFormatoCelda->align   = 'left';

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Código Postal' ,
            $hwtCliente->codigo_postal,$objFormatoCelda));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Contacto' ,
            $hwtCliente->contacto_nombre));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Representante Legal' ,
            $hwtCliente->representante_legal));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Correo Factura' ,
            $hwtCliente->facturacion_email));

    $pObjFormato = new \stdClass();
    $pObjFormato->tipoPresentacion = 'custom';
    $pObjFormato->celdaSaltoLinea  = 4;
    Reporter::printSectionData($arrayObjSeccion,$pObjFormato);


    //////////////////////////////////////////
    // ECRC: Sección de las Unidades        //
    //////////////////////////////////////////
    $valorSeparador = 'UNIDADES ADQUIRIDAS';
    Reporter::printVerticalSeparator($valorSeparador,$fgColor,$bgColor);

    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue  ($hwtPedidoVenta->num_pedido);
    $hwtPedidoVentaLinea = Dataworker::getRecords('hwt_pedido_venta_linea',$objCondicion);

    if($hwtPedidoVentaLinea->numRecords > 0) {

        // ECRC: Cargando la Información de la Unidad en el Registro de Unidad de Cotización
        foreach($hwtPedidoVentaLinea->data as $recordPedidoVentaLinea){
            $bgColorUnidad = '6995B1';
            $valorSeparador = 'PARTIDA ' . $recordPedidoVentaLinea->num_partida . ' - CODIGO ' . $recordPedidoVentaLinea->codigo;
            Reporter::printVerticalSeparator($valorSeparador,$fgColor,$bgColorUnidad);

            $objCondicion = new \stdClass();
            $objCondicion->codigo        = Dataworker::equalToString($recordPedidoVentaLinea->codigo);
            $hwtVehiculo = Dataworker::findFirst('hwt_vehiculo',$objCondicion);

            $objFormatoCelda = new \stdClass();
            $objFormatoCelda->fgColor = '000000';
            $objFormatoCelda->bgColor = 'FFFFFF';
            $objFormatoCelda->align   = 'left';

            $arrayObjSeccion = array();

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('VIN' ,
                    $hwtVehiculo->vin,$objFormatoCelda));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Modelo' ,
                    $hwtVehiculo->modelo,$objFormatoCelda));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Marca' ,
                    $hwtVehiculo->marca,$objFormatoCelda));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Año' ,
                    $hwtVehiculo->ann_unidad,$objFormatoCelda));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Motor Modelo' ,
                    $hwtVehiculo->modelo_motor,$objFormatoCelda));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Motor Potencia' ,
                    $hwtVehiculo->modelo_motor,$objFormatoCelda));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Motor Serie' ,
                    $hwtVehiculo->numero_serie,$objFormatoCelda));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Relac. Diferencial' ,
                    $hwtVehiculo->relacion_dif,$objFormatoCelda));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Transmision Marca' ,
                    $hwtVehiculo->marca_transmision));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Precio Bruto' ,
                    number_format(floatval($hwtVehiculo->precio_sin_iva),2)));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Transmision Modelo' ,
                    $hwtVehiculo->modelo_transmision));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Precio Neto' ,
                    number_format(floatval($hwtVehiculo->precio_con_iva),2)));

            $pObjFormato = new \stdClass();
            $pObjFormato->tipoPresentacion = 'custom';
            $pObjFormato->celdaSaltoLinea  = 4;
            Reporter::printSectionData($arrayObjSeccion,$pObjFormato);
        }
    }


    //////////////////////////////////////////
    // ECRC: Gastos Adicionales             //
    //////////////////////////////////////////
    $valorSeparador = 'GASTOS ADICIONALES';
    Reporter::printVerticalSeparator($valorSeparador,$fgColor,$bgColor);

    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue  ($hwtPedidoVenta->num_pedido);
    $hwtPedidoVentaAdicional = Dataworker::getRecords('hwt_pedido_venta_adicional',$objCondicion);

    if($hwtPedidoVentaAdicional->numRecords > 0) {

        //ECRC: Escribiendo el Encabezado de los Gastos Adicionales
        $arrayTituloUnidades = array();
        array_push($arrayTituloUnidades,'PROVEEDOR');
        array_push($arrayTituloUnidades,'DESCRIPCION');
        array_push($arrayTituloUnidades,'CON CARGO AL CLIENTE');
        array_push($arrayTituloUnidades,'SIN CARGO AL CLIENTE');

        $arrayObjSeccion = array();
        foreach ($arrayTituloUnidades as $tituloUnidad){
            array_push($arrayObjSeccion,
                Reporter::createSimpleData('GASTO' ,
                    $tituloUnidad));
        }

        $pObjFormato = new \stdClass();
        $pObjFormato->tipoPresentacion = 'simple';
        $pObjFormato->celdaSaltoLinea  = 4;
        $pObjFormato->align    = 'center';
        $pObjFormato->bgColor  = '6995B1';
        $pObjFormato->fgColor  = 'FFFFFF';
        $pObjFormato->fontBold = true;
        $pObjFormato->wrapText = true;

        Reporter::printSectionData($arrayObjSeccion,$pObjFormato);

        // ECRC: Desplegando los Gastos Adicionales del Pedido
        $valorConCargoCliente = 0;
        $valorSinCargoCliente = 0;
        foreach($hwtPedidoVentaAdicional->data as $recordPedidoVentaAdicional){

            $valorConCargoCliente = $valorConCargoCliente
                + floatval($recordPedidoVentaAdicional->servicio_con_cargo_cliente);

            $valorSinCargoCliente = $valorSinCargoCliente
                + floatval($recordPedidoVentaAdicional->servicio_sin_cargo_cliente);

            $objCondicion = new \stdClass();
            $objCondicion->codigo_proveedor        = Dataworker::equalToString($recordPedidoVentaAdicional->codigo_proveedor);
            $hwtProveedor = Dataworker::findFirst('hwt_proveedor',$objCondicion);

            $arrayObjSeccion = array();

            $objFormatoCelda = new \stdClass();
            $objFormatoCelda->fgColor = '000000';
            $objFormatoCelda->bgColor = 'FFFFFF';
            $objFormatoCelda->align   = 'left';

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Proveedor' ,
                    $hwtProveedor->razon_social,
                    $objFormatoCelda));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Descripcion' ,
                    $recordPedidoVentaAdicional->descripcion,
                    $objFormatoCelda));

            $objFormatoCelda->align   = 'right';

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Con Cargo al Cliente' ,
                    number_format(floatval($recordPedidoVentaAdicional->servicio_con_cargo_cliente),2),
                    $objFormatoCelda));

            array_push($arrayObjSeccion,
                Reporter::createSimpleData('Sin Cargo al Cliente' ,
                    number_format(floatval($recordPedidoVentaAdicional->servicio_sin_cargo_cliente),2),
                    $objFormatoCelda));

            $pObjFormato = new \stdClass();
            $pObjFormato->tipoPresentacion = 'simple';
            $pObjFormato->celdaSaltoLinea  = 4;
            $pObjFormato->bgColor          = 'FFFFFF';
            $pObjFormato->fgColor          = '000000';
            $pObjFormato->wrapText         = true;

            Reporter::printSectionData($arrayObjSeccion,$pObjFormato);
        }
    } // ECRC: Existen gastos adicionales

    // ECRC: Desplegando los Totales de los Gastos Adicionales
    $pObjFormato = new \stdClass();
    $pObjFormato->align    = 'right';
    $pObjFormato->bgColor  = 'cddae1';
    $pObjFormato->fgColor  = '054F7D';
    $pObjFormato->fontBold = true;
    $pObjFormato->wrapText = true;

    Reporter::writeCell('A' . Reporter::getCurrentRow(),
        '',
        $pObjFormato);

    Reporter::writeCell('B' . Reporter::getCurrentRow(),
        'TOTAL DE GASTOS ADICIONALES',
        $pObjFormato);


    Reporter::writeCell('C' . Reporter::getCurrentRow(),
        number_format(floatval($valorConCargoCliente),2),
        $pObjFormato);

    Reporter::writeCell('D' . Reporter::getCurrentRow(),
        number_format(floatval($valorSinCargoCliente),2),
        $pObjFormato);

    Reporter::increaseCurrentRow(2);

    // ECRC: En caso de que sean varias Unidades se crea una Hoja de Resumen
    if($hwtPedidoVentaLinea->numRecords > 1){
        $filaActual = intval(Reporter::getCurrentRow());

        while($filaActual < 63){
            Reporter::increaseCurrentRow();
            $filaActual = intval(Reporter::getCurrentRow());
        }

        $pObjConfig = new \stdClass();
        $pObjConfig->rowTitle = Reporter::getCurrentRow();

        Reporter::prepareHeader(utf8_encode($nombreReporte),$pObjConfig);
        Reporter::increaseCurrentRow(5);
    }



    //////////////////////////////////////////
    // ECRC: Entrega de las Unidades        //
    //////////////////////////////////////////
    $valorSeparador = 'ENTREGA DE LAS UNIDADES';
    Reporter::printVerticalSeparator($valorSeparador,$fgColor,$bgColor);

    $arrayObjSeccion = array();
    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Tipo de Entrega' ,
            $hwtPedidoVenta->tipo_entrega));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Consecionario' ,
            descripcionConsecionario(
                $hwtPedidoVenta->codigo_consecionario)));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Sucursal' ,
            descripcionConsecionarioSucursal(
                $hwtPedidoVenta->codigo_consecionario,
                $hwtPedidoVenta->codigo_sucursal)));

    $pObjFormato = new \stdClass();
    $pObjFormato->tipoPresentacion = 'custom';
    $pObjFormato->celdaSaltoLinea  = 4;
    Reporter::printSectionData($arrayObjSeccion,$pObjFormato);

    $pObjFormato = new \stdClass();
    $pObjFormato->align    = 'right';
    $pObjFormato->bgColor  = 'cddae1';
    $pObjFormato->fgColor  = '054F7D';
    $pObjFormato->fontBold = false;
    $pObjFormato->wrapText = true;

    Reporter::writeCell('A' . Reporter::getCurrentRow(),
        'Observaciones',
        $pObjFormato);

    $pObjFormato = new \stdClass();
    $pObjFormato->align    = 'left';
    $pObjFormato->bgColor  = 'FFFFFF';
    $pObjFormato->fgColor  = '000000';
    $pObjFormato->fontBold = false;

    Reporter::writeCell('B' . Reporter::getCurrentRow(),
        $hwtPedidoVenta->entrega_observaciones,
        $pObjFormato);

    Reporter::increaseCurrentRow(2);

    //////////////////////////////////////////
    // ECRC: Resumen del Pedido             //
    //////////////////////////////////////////
    $valorSeparador = 'RESUMEN DEL PEDIDO';
    Reporter::printVerticalSeparator($valorSeparador,$fgColor,$bgColor);

    $arrayObjSeccion = array();
    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Unidades Pedida' ,
            $hwtPedidoVenta->cantidad_unidades));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Subtotal',
            number_format($hwtPedidoVenta->valor_subtotal,2)));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Valor de Unidades',
            number_format($hwtPedidoVenta->valor_subtotal_unidades,2)));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Impuestos',
            number_format($hwtPedidoVenta->valor_impuesto,2)));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Cargos Adicionales',
            number_format($hwtPedidoVenta->valor_subtotal_adicionales,2)));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Valor Total Pedido',
            number_format($hwtPedidoVenta->valor_total,2)));

    $pObjFormato = new \stdClass();
    $pObjFormato->tipoPresentacion = 'custom';
    $pObjFormato->celdaSaltoLinea  = 4;

    Reporter::printSectionData($arrayObjSeccion,$pObjFormato);

    //////////////////////////////////////////
    // ECRC: Firmas del Documento        //
    //////////////////////////////////////////
    $valorSeparador = 'FIRMAS DEL DOCUMENTO';
    Reporter::printVerticalSeparator($valorSeparador,$fgColor,$bgColor);

    $pObjSignature = new \stdClass();
    $pObjSignature->column         = 'A';
    $pObjSignature->titlePerson    = '';
    $pObjSignature->namePerson     = '';
    $pObjSignature->cellForeground = $bgColor;
    $pObjSignature->cellBackground = $bgColor;
    $pObjSignature->borderColor    = $bgColor;
    Reporter::signatureBox($pObjSignature);

    $pObjSignature = new \stdClass();
    $pObjSignature->column         = 'B';
    $pObjSignature->titlePerson    = 'REPRESENTANTE LEGAL';
    $pObjSignature->namePerson     = $hwtCliente->representante_legal;
    $pObjSignature->cellForeground = '000000';
    $pObjSignature->cellBackground = 'f0f4f7';
    $pObjSignature->borderColor    = $bgColor;
    Reporter::signatureBox($pObjSignature);

    $pObjSignature = new \stdClass();
    $pObjSignature->column         = 'C';
    $pObjSignature->titlePerson    = 'VENDEDOR';
    $pObjSignature->namePerson     = nombreUsuario($hwtPedidoVenta->codigo_vendedor);
    $pObjSignature->cellForeground = '000000';
    $pObjSignature->cellBackground = 'f0f4f7';
    $pObjSignature->borderColor    = $bgColor;
    Reporter::signatureBox($pObjSignature);

    $pObjSignature = new \stdClass();
    $pObjSignature->column         = 'D';
    $pObjSignature->titlePerson    = 'GERENTE REGIONAL';
    $pObjSignature->namePerson     = nombreUsuario($hwtPedidoVenta->codigo_gerente_regional);
    $pObjSignature->cellForeground = '000000';
    $pObjSignature->cellBackground = 'f0f4f7';
    $pObjSignature->borderColor    = $bgColor;
    Reporter::signatureBox($pObjSignature);

    Reporter::increaseCurrentRow(6);

    ////////////////////////////////////////////
    // ECRC: Hoja de Contrato de Compra Venta //
    ////////////////////////////////////////////
    $tituloContrato = 'Contrato de Compra Venta (Pedido No. ' . $hwtPedidoVenta->num_pedido . ')';
    $indiceHoja = Reporter::createSheet('Contrato');

    Logger::write('Hoja activa: ' . $indiceHoja);

    Reporter::setActiveSheet($indiceHoja);
    Reporter::prepareHeader($tituloContrato);

    //ECRC: Preparando el Titulo de las Columnas
    $arrayTituloColumnas = array(
        "30:",
        "30:",
        "30:",
        "30:",
    );

    Reporter::prepareTitleColumns($arrayTituloColumnas);
    Reporter::decreaseCurrentRow();

    ////////////////////////////////////////////
    // ECRC: Valor Total de la Operación      //
    ////////////////////////////////////////////
    $valorSeparador = 'VALOR TOTAL DE LA OPERACION';
    Reporter::printVerticalSeparator($valorSeparador,$fgColor,$bgColor);

    $arrayObjSeccion = array();
    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Fecha del Pedido',
            $hwtPedidoVenta->fecha_implantacion));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Folio Pedido' ,
            $hwtPedidoVenta->num_pedido));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Fecha del Contrato',
            date("Y-m-d")));

    array_push($arrayObjSeccion,
        Reporter::createSimpleData('Total de la Operación',
            number_format($hwtPedidoVenta->valor_total,2)));

    $pObjFormato = new \stdClass();
    $pObjFormato->tipoPresentacion = 'custom';
    $pObjFormato->celdaSaltoLinea  = 4;

    Reporter::printSectionData($arrayObjSeccion,$pObjFormato);


    ////////////////////////////////////////////
    // ECRC: Condiciones del Contrato         //
    ////////////////////////////////////////////
    $valorSeparador = 'CONDICIONES DEL CONTRATO';
    Reporter::printVerticalSeparator($valorSeparador,$fgColor,$bgColor);

    $objCondicionesContrato = conjuntoParametros('pedido_venta_condiciones');

    $condicionesContrato = '';
    foreach ($objCondicionesContrato as $recordCondicionContrato){
        $condicionesContrato = $condicionesContrato
                             . $recordCondicionContrato->valor
                             . PHP_EOL . PHP_EOL;
    }

    Reporter::writeCell('A' . Reporter::getCurrentRow(),$condicionesContrato);

    $celdaInicial = 'A' . Reporter::getCurrentRow();
    $celdaFinal   = 'D' . (intval(Reporter::getCurrentRow()) + 42);
    Reporter::mergeCells($celdaInicial,$celdaFinal);
    Reporter::setFontSize($celdaInicial,$celdaFinal,8);
    Reporter::increaseCurrentRow(43);

    ////////////////////////////////////////////
    // ECRC: Aceptación del Contrato         //
    ////////////////////////////////////////////
    $valorSeparador = 'FIRMAS DE ACEPTACION DEL CONTRATO';
    Reporter::printVerticalSeparator($valorSeparador,$fgColor,$bgColor);

    $pObjSignature = new \stdClass();
    $pObjSignature->column         = 'A';
    $pObjSignature->columnEnd      = 'B';
    $pObjSignature->rowEnd         = '7';
    $pObjSignature->titlePerson    = 'REPRESENTANTE LEGAL'
                                   . PHP_EOL . strtoupper($hwtCliente->razon_social);
    $pObjSignature->namePerson     = strtoupper($hwtCliente->representante_legal);
    $pObjSignature->cellForeground = '000000';
    $pObjSignature->cellBackground = 'f0f4f7';
    $pObjSignature->borderColor    = $bgColor;
    Reporter::signatureBox($pObjSignature);

    /*$sysEmpreDataworker::findFirst('sys_empresa');*/
    $pObjSignature = new \stdClass();
    $pObjSignature->column         = 'C';
    $pObjSignature->columnEnd      = 'D';
    $pObjSignature->rowEnd         = '7';
    $pObjSignature->titlePerson    = 'REPRESENTANTE LEGAL'
                                   . PHP_EOL . strtoupper($sysEmpresa->fiscal_razon_social);
    $pObjSignature->namePerson     = strtoupper($sysEmpresa->fiscal_representante_legal);
    $pObjSignature->cellForeground = '000000';
    $pObjSignature->cellBackground = 'f0f4f7';
    $pObjSignature->borderColor    = $bgColor;
    Reporter::signatureBox($pObjSignature);

    //////////////////////////////////////////
    // ECRC: Cierre del Reporte             //
    //////////////////////////////////////////
    $archivoServidor = Reporter::saveFile();

    $objArchivoGenerado = new \stdClass();
    $objArchivoGenerado->nombre          = $archivoGenerado;
    $objArchivoGenerado->archivoServidor = $archivoServidor;

    Emissary::prepareEnvelope();

    $availableInfo = true;
    $apiMessage = 'Archivo generado: ' . $archivoGenerado;
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('archivoGenerado' , $objArchivoGenerado);
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();

    if(isset($pObjImpresion)){
        return $objArchivoGenerado;
    }
    else{
        echo json_encode($objReturn);
    }
}

function evaluaSituacionPedido($pNumPedido){
    // ECRC: Localizando el Pedido
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido        = Dataworker::equalToValue($pNumPedido);
    $hwtPedidoVenta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

    $permiteActualizacion = true;
    if(intval($hwtPedidoVenta->situacion_pedido) > 2){
        $permiteActualizacion = false;
    }

    return $permiteActualizacion;
}

function confirmarPedidoVenta(){
    Dataworker::openConnection();

    $numPedido = Receiver::getApiParameter('tfNumPedido');
    $userId    = Receiver::getApiParameter('user_id');

    Emissary::prepareEnvelope();
    // ECRC: Localizando el Pedido
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido        = Dataworker::equalToValue($numPedido);
    $hwtPedidoVenta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

    $mensaje = 'ConfirmarPedidoVenta:: ';
    $procesoCorrecto = true;
    if(intval($hwtPedidoVenta->situacion_pedido) >= 2){
        $mensaje = $mensaje
                 . 'El Pedido ya está confirmado (En Firme)';
        $procesoCorrecto = false;
    }
    else{
        Receiver::resetApiParameters();
        $hwtPedidoVenta->situacion_pedido = '2';
        $objCamposRegistro = Dataworker::setFieldsTable('hwt_pedido_venta',$hwtPedidoVenta);

        Dataworker::updateRecord($objCamposRegistro);

        registraActualizacion($numPedido,$userId,'actualiza');
        $mensaje = 'El Pedido ' . $numPedido . ' ha sido Confirmado.';
        $procesoCorrecto = true;
    }

    Dataworker::closeConnection();

    datosPedidoVenta($numPedido,$mensaje,$procesoCorrecto);
    return;
}

function desasignarPedidoVenta(){
    $numPedido = Receiver::getApiParameter('tfNumPedido');
    asignarPedidoVenta($numPedido,'desasignar');
}

function asignarPedidoVenta($pNumPedido = null, $pAccion = null){
    Dataworker::openConnection();

    if(!isset($pAccion)){
        $accionAsignacion = 'asignar';
    }
    else{
        $accionAsignacion = $pAccion;
    }

    $numPedido = Receiver::getApiParameter('tfNumPedido');
    $userId    = Receiver::getApiParameter('user_id');

    Emissary::prepareEnvelope();
    // ECRC: Localizando el Pedido
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido        = Dataworker::equalToValue($numPedido);
    $hwtPedidoVenta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

    $mensaje = 'ConfirmarPedidoVenta:: ';
    switch ($accionAsignacion){
        case 'asignar':
            if(intval($hwtPedidoVenta->situacion_pedido) >= 3){
                $mensaje = $mensaje
                    . 'El Pedido ya está Asignado al Cliente';
            }
            else{
                Receiver::resetApiParameters();
                $hwtPedidoVenta->situacion_pedido = '3';
                $objCamposRegistro = Dataworker::setFieldsTable('hwt_pedido_venta',$hwtPedidoVenta);
                Dataworker::updateRecord($objCamposRegistro);

                Logger::write('Va a signar las Unidades');
                asignarUnidadesPedido($hwtPedidoVenta->num_pedido,'asignar');
                registraActualizacion($numPedido,$userId,'actualiza');

                $mensaje = 'El Pedido ' . $numPedido . ' ha sido Asignado al Cliente. ' . '</br>'
                    . 'Las Unidades relacionadas han cambiado su Estado a Asignadas.';
            }
            break;
        case 'desasignar':
            Receiver::resetApiParameters();
            $hwtPedidoVenta->situacion_pedido = '2';
            $objCamposRegistro = Dataworker::setFieldsTable('hwt_pedido_venta',$hwtPedidoVenta);
            Dataworker::updateRecord($objCamposRegistro);

            Logger::write('Va a desasignar las Unidades');
            asignarUnidadesPedido($hwtPedidoVenta->num_pedido,'desasignar');
            registraActualizacion($numPedido,$userId,'actualiza');

            $mensaje = 'El Pedido ' . $numPedido . ' ha sido Desasignado del Cliente. ' . '</br>'
                . 'Las Unidades relacionadas han cambiado su Estado a Disponibles.';
            break;
    }

    Dataworker::closeConnection();

    datosPedidoVenta($numPedido,$mensaje,true);
    return;
}

function eliminarPedidoVentaAdicional(){
    Logger::enable(true,'eliminarPedidoVentaAdicional');
    Dataworker::openConnection();

    $numPedido = Receiver::getApiParameter('numPedido');
    $userId    = Receiver::getApiParameter('user_id');
    $rowidPedidoVentaAdicional = Receiver::getApiParameter('rowidPedidoVentaAdicional');

    if(!evaluaSituacionPedido($numPedido)){
        $registroActualizado = false;
        $apiMessage = 'La Situación del Pedido de Venta no permite modificación.';
    }
    else{
        $objCondicion = new \stdClass();
        $objCondicion->tableName = 'hwt_pedido_venta_adicional';
        $objCondicion->keyField  = 'rowid';
        $objCondicion->keyValue  = $rowidPedidoVentaAdicional;
        $objDeletedRecord = Dataworker::deleteRecord($objCondicion);

        // ECRC: Actualizando el Pedido de Venta
        calculaPedidoVenta($numPedido);
        registraActualizacion($numPedido,$userId,'actualiza');
        $registroActualizado = true;
        $apiMessage = 'El Cargo Adicional ha sido eliminado del Pedido de Venta.';
    }

    Dataworker::closeConnection();

    Logger::write(json_encode($objDeletedRecord));

    // ECRC: Localizando el Pedido para retornar los Nuevos Valores
    Dataworker::openConnection();
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue  ($numPedido);
    $hwtPedidoVenta = Dataworker::getRecords('hwt_pedido_venta',$objCondicion);

    Emissary::prepareEnvelope();
    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api'        , $apiMessage);
    Emissary::addData('objDeletedRecord' , $objDeletedRecord);
    Emissary::addData('hwtPedidoVenta'  , $hwtPedidoVenta->data);
    Dataworker::closeConnection();

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function entregarPedidoVentaLinea(){
    Logger::enable(true,'entregarPedidoVentaLinea');
    Dataworker::openConnection();

    Logger::write('**** Inicia entregarPedidoVentaLinea');
    $numPedido = Receiver::getApiParameter('numPedido');
    $userId    = Receiver::getApiParameter('user_id');
    $rowidPedidoVentaLinea = Receiver::getApiParameter('rowidPedidoVentaLinea');

    // ECRC: Validando si es posible realizar la Entrega
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue($numPedido);
    $hwtPedidoVenta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

    Logger::write(json_encode($hwtPedidoVenta));

    if(!contieneValor($hwtPedidoVenta->integracion_factura_serie)){
        $registroActualizado = false;
        $apiMessage = 'Debe de registrar la Factura del Pedido para proceder con la Entrega de las Unidades.';
    }
    else{
        $objCondicion = new \stdClass();
        $objCondicion->rowid = Dataworker::equalToValue($rowidPedidoVentaLinea);
        $hwtPedidoVentaLinea = Dataworker::findFirst('hwt_pedido_venta_linea',$objCondicion);

        $fechaActual = date("Y-m-d");
        $hwtPedidoVentaLinea->entrega_fecha = $fechaActual;
        $hwtPedidoVentaLinea->entrega_usuario = $userId;

        $objCamposRegistro   = Dataworker::setFieldsTable('hwt_pedido_venta_linea',$hwtPedidoVentaLinea);
        Dataworker::updateRecord($objCamposRegistro);

        $registroActualizado = true;

        //ECRC: Actualizando el Estado de la Unidad a VENDIDA
        $objCondicion = new \stdClass();
        $objCondicion->codigo = Dataworker::equalToString($hwtPedidoVentaLinea->codigo);
        $hwtVehiculo = Dataworker::findFirst('hwt_vehiculo',$objCondicion);

        $hwtVehiculo->estado_unidad = 'VENDIDO';
        $objCamposRegistro   = Dataworker::setFieldsTable('hwt_vehiculo',$hwtVehiculo);
        Dataworker::updateRecord($objCamposRegistro);

        //ECRC: Si todas las Unidades del Pedido han sido Vendidas se marca el Pedido con situacion de ENTREGADO
        $objCondicion = new \stdClass();
        $objCondicion->num_pedido = Dataworker::equalToValue($numPedido);
        $hwtPedidoVentaLinea = Dataworker::getRecords('hwt_pedido_venta_linea',$objCondicion);

        $totalmenteEntregado = true;

        foreach ($hwtPedidoVentaLinea->data as $recordPedidoVentaLinea) {
            $objCondicion = new \stdClass();
            $objCondicion->codigo = Dataworker::equalToString($recordPedidoVentaLinea->codigo);
            $hwtVehiculo = Dataworker::findFirst('hwt_vehiculo',$objCondicion);

            Logger::write('>>>Estado de la Unidad: ' . $hwtVehiculo->estado_unidad);

            if($hwtVehiculo->estado_unidad !== 'VENDIDO'){
                $totalmenteEntregado = false;
            }
        }

        $apiMessage = 'Se ha registrado la Entrega de la UNIDAD y ésta ha quedado en estado de VENDIDA.';

        if($totalmenteEntregado){
            $hwtPedidoVenta->situacion_pedido = '5'; // ENTREGADO
            $objCamposRegistro   = Dataworker::setFieldsTable('hwt_pedido_venta',$hwtPedidoVenta);
            Dataworker::updateRecord($objCamposRegistro);

            $apiMessage = $apiMessage . '</br>'
                        . 'El Pedido ha sido Entregado Totalmente.';
        }
    }

    datosPedidoVenta($numPedido,$apiMessage,$registroActualizado);
}

function eliminarPedidoVentaLinea(){
    Logger::enable(true,'eliminarPedidoVentaLinea');
    Dataworker::openConnection();

    $numPedido = Receiver::getApiParameter('numPedido');
    $userId    = Receiver::getApiParameter('user_id');
    $rowidPedidoVentaLinea = Receiver::getApiParameter('rowidPedidoVentaLinea');

    if(!evaluaSituacionPedido($numPedido)){
        $registroActualizado = false;
        $apiMessage = 'La Situación del Pedido de Venta no permite modificación.';
    }
    else {
        $objCondicion = new \stdClass();
        $objCondicion->tableName = 'hwt_pedido_venta_linea';
        $objCondicion->keyField  = 'rowid';
        $objCondicion->keyValue  = $rowidPedidoVentaLinea;
        $objDeletedRecord = Dataworker::deleteRecord($objCondicion);

        // ECRC: Actualizando la Cotización
        calculaPedidoVenta($numPedido);
        registraActualizacion($numPedido,$userId,'actualiza');

        $registroActualizado = true;
        $apiMessage = 'La Unidad ha sido eliminada del Pedido de Venta.';
    }
    Dataworker::closeConnection();


    // ECRC: Localizando el Pedido para retornar los Nuevos Valores
    Dataworker::openConnection();
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue  ($numPedido);
    $hwtPedidoVenta = Dataworker::getRecords('hwt_pedido_venta',$objCondicion);

    Emissary::prepareEnvelope();
    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api'        , $apiMessage);
    Emissary::addData('objDeletedRecord' , $objDeletedRecord);
    Emissary::addData('hwtPedidoVenta'  , $hwtPedidoVenta->data);
    Dataworker::closeConnection();

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function registraActualizacion($pNumPedido,$pUsuario,$pTipoRegistro){
    Logger::enable('registraActualizacion');

    Dataworker::openConnection();

    // ECRC: Localizando el Pedido
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido        = Dataworker::equalToValue($pNumPedido);
    $hwtPedidoVenta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

    $fechaActual = date("Y-m-d");

    switch ($pTipoRegistro){
        case 'actualiza':
            $hwtPedidoVenta->usuario_actualizacion = $pUsuario;
            $hwtPedidoVenta->fecha_actualizacion   = $fechaActual;
            break;
        case 'cancela':
            $hwtPedidoVenta->usuario_cancelacion = $pUsuario;
            $hwtPedidoVenta->fecha_cancelacion   = $fechaActual;
            break;
    }

    Logger::write('Va a actualizar el Registro con los datos de Usario ' . $pUsuario);

    Receiver::resetApiParameters();
    $objCamposRegistro   = Dataworker::setFieldsTable('hwt_pedido_venta',$hwtPedidoVenta);
    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    Dataworker::closeConnection();

    Logger::write($sqlEjecutado);

    return $sqlEjecutado;
}

function calculaPedidoVenta($pNumPedido){
    Logger::enable(true,actualizaPedidoVenta);

    Logger::write('Iniciando actualizaPedidoVenta');

    Dataworker::openConnection();

    //ECRC: Totalizando las Unidades del Pedido
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue  ($pNumPedido);
    $hwtPedidoVentaLinea       = Dataworker::getRecords('hwt_pedido_venta_linea',$objCondicion);

    $cantidadUnidades = 0;
    $pctImpuesto      = .16;
    $valorSubtotalUnidades    = 0;
    $valorImpuesto    = 0;
    $valorTotal       = 0;
    foreach($hwtPedidoVentaLinea->data as $recordPedidoVentaLinea){
        $cantidadUnidades = $cantidadUnidades + 1;
        $valorPartida = floatval($recordPedidoVentaLinea->valor_partida);
        $valorUnitario = $valorPartida / (1 + $pctImpuesto);

        $valorSubtotalUnidades = $valorSubtotalUnidades
                               + ($valorUnitario);

        $valorTotal = $valorTotal + $valorPartida;
    }

    //ECRC: Totalizando los Cargos al Cliente
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue  ($pNumPedido);
    $hwtPedidoVentaAdicional   = Dataworker::getRecords('hwt_pedido_venta_adicional',$objCondicion);

    $valorSubtotalAdicionales = 0;
    $valoAcumConCargoCliente  = 0;
    $valorAcumSinCargoCliente = 0;
    foreach($hwtPedidoVentaAdicional->data as $recordPedidoVentaAdicional){
        $valConCargoCliente = floatval($recordPedidoVentaAdicional->servicio_con_cargo_cliente);
        $valSinCargoCliente = floatval($recordPedidoVentaAdicional->servicio_sin_cargo_cliente);

        //ECRC: Acumulando los Cargos al Cliente con IVA (Informativos)
        $valoAcumConCargoCliente = $valoAcumConCargoCliente
                                 + ($valConCargoCliente * ( 1 + $pctImpuesto));

        $valorAcumSinCargoCliente = $valorAcumSinCargoCliente
                                  + ($valSinCargoCliente * ( 1 + $pctImpuesto));

        //ECRC: El valor se subtotaliza sin IVA
        $valorSubtotalAdicionales = $valorSubtotalAdicionales
                                  + $valConCargoCliente;

        //ECRC: Se adiciona el Valor con IVA al TOTAL
        $valorTotal = $valorTotal + ($valConCargoCliente * ( 1 + $pctImpuesto));
    }

    //ECRC: Totalizando el Subtotal e Impuestos
    $valorSubtotal = $valorTotal / ( 1 + $pctImpuesto);
    $valorImpuesto = $valorTotal - $valorSubtotal;

    // ECRC: Localizando el Pedido
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido        = Dataworker::equalToValue($pNumPedido);
    $hwtPedidoVenta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

    $hwtPedidoVenta->cantidad_unidades = $cantidadUnidades;
    $hwtPedidoVenta->valor_con_cargo_cliente    = $valoAcumConCargoCliente;
    $hwtPedidoVenta->valor_sin_cargo_cliente    = $valorAcumSinCargoCliente;
    $hwtPedidoVenta->valor_subtotal_unidades    = $valorSubtotalUnidades;
    $hwtPedidoVenta->valor_subtotal_adicionales = $valorSubtotalAdicionales;
    $hwtPedidoVenta->valor_subtotal             = $valorSubtotal;
    $hwtPedidoVenta->valor_impuesto             = $valorImpuesto;
    $hwtPedidoVenta->valor_total                = $valorTotal;

    Receiver::resetApiParameters();
    $objCamposRegistro   = Dataworker::setFieldsTable('hwt_pedido_venta',$hwtPedidoVenta);
    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    return $sqlEjecutado;    
}

function grabaPedidoVentaAdicional(){
    Logger::enable(true,'grabaUnidadPedidoVentaLinea');

    Emissary::prepareEnvelope();

    Receiver::prepareParameters();
    Dataworker::openConnection();

    $userId               = Receiver::getApiParameter('user_id');
    $numPedido            = Receiver::getApiParameter('tfNumPedido');
    $descripcionAdicional = Receiver::getApiParameter('tfDescripcionAdicional');
    $codigoProveedor      = Receiver::getApiParameter('tfCodigoProveedor');
    $valConCargoCliente   = Receiver::getApiParameter('tfValConCargoCliente');
    $valSinCargoCliente   = Receiver::getApiParameter('tfValSinCargoCliente');

    if($valConCargoCliente === ''){
        $valConCargoCliente = 0;
    }

    if($valSinCargoCliente === ''){
        $valSinCargoCliente = 0;
    }

    // ECRC: Localizando el Registro del Pedido
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido        = Dataworker::equalToValue  ($numPedido);
    $hwtPedidoVenta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

    // ECRC: Calculando el Número de Partida
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue  ($numPedido);
    $numSecuencia = Dataworker::getMaxValue('hwt_pedido_venta_adicional','num_secuencia',$objCondicion);

    $numSecuencia = intval($numSecuencia);
    $numSecuencia = $numSecuencia + 10;

    // ECRC: Estableciendo el Resto de Valores por Defecto
    Receiver::resetApiParameters();
    Receiver::setApiParameterValue('tfNumPedido'               ,$numPedido);
    Receiver::setApiParameterValue('tfNumSecuencia'            ,$numSecuencia);
    Receiver::setApiParameterValue('tfCodigoProveedor'         ,$codigoProveedor);
    Receiver::setApiParameterValue('tfDescripcion'             ,$descripcionAdicional);
    Receiver::setApiParameterValue('tfServicioConCargoCliente' ,$valConCargoCliente);
    Receiver::setApiParameterValue('tfServicioSinCargoCliente' ,$valSinCargoCliente);

    $objCamposRegistro   = Dataworker::setFieldsTable('hwt_pedido_venta_adicional');

    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    // ECRC: Actualizando la Cotización
    calculaPedidoVenta($numPedido);
    registraActualizacion($numPedido,$userId,'actualiza');

    // ECRC: Localizando el Pedido para retornar los Nuevos Valores
    Dataworker::openConnection();
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue  ($numPedido);
    $hwtPedidoVenta = Dataworker::getRecords('hwt_pedido_venta',$objCondicion);

    $registroActualizado = true;
    $apiMessage = 'Unidad actualizada en el Pedido de Venta.';

    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api'        , $apiMessage);
    Emissary::addMessage('sql-ejecutado'   , $sqlEjecutado);
    Emissary::addData('camposRegistro' , $objCamposRegistro);
    Emissary::addData('hwtPedidoVenta'  , $hwtPedidoVenta->data);
    Dataworker::closeConnection();

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function grabaPedidoVentaLinea(){
    Logger::enable(true,'grabaUnidadPedidoVentaLinea');

    Emissary::prepareEnvelope();

    Receiver::prepareParameters();
    Dataworker::openConnection();
    $userId       = Receiver::getApiParameter('user_id');
    $numPedido    = Receiver::getApiParameter('tfNumPedido');
    $codigoUnidad = Receiver::getApiParameter('tfCodigoUnidad');

    Logger::write('Inicia proceos de validacion');

    $indProcesoCorrecto = true;
    $apiMessage = '';

    // ECRC: Verificando si la Unidad no está siendo utilizada en otro Pedido de Venta
    $objCondicion = new \stdClass();
    $objCondicion->codigo = Dataworker::equalToString($codigoUnidad);
    $hwt_pedido_venta_linea = Dataworker::getRecords('hwt_pedido_venta_linea',$objCondicion);

    foreach($hwt_pedido_venta_linea->data as $recordHwtPedidoLinea){

        Logger::write('-->>Buscando el Pedido de la Unidad Maclovia');
        $objCondicion = new \stdClass();
        $objCondicion->num_pedido       = Dataworker::compare('equalTo','value',$recordHwtPedidoLinea->num_pedido);
        $objCondicion->situacion_pedido = Dataworker::compare('lessThan','string',6);
        $hwt_pedido_venta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

        if(Dataworker::availableRecord($hwt_pedido_venta)){
            $indProcesoCorrecto = false;
            $apiMessage = $apiMessage
                . 'La Unidad ya se encuentra registrada en un Pedido de Venta.</br>'
                . '<span style="color: darkred"><b>'
                . 'Pedido: ' . $hwt_pedido_venta->num_pedido . ' '
                . 'Cliente: ' . nombreCliente($hwt_pedido_venta->codigo_cliente) .'</br>'
                . '</b></span>'
                . 'No es posible utilizarla nuevamente.';
            break;
        }
    }

    if($indProcesoCorrecto === false){
        Emissary::success($indProcesoCorrecto);
        Emissary::addMessage('info-api'        , $apiMessage);
        Dataworker::closeConnection();

        $objReturn = Emissary::getEnvelope();
        echo json_encode($objReturn);
        return;
    }

    Logger::write('$hwt_pedido_venta_linea:' . json_encode($hwt_pedido_venta_linea));

    // ECRC: Localizando el Registro del Pedido de Venta
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido        = Dataworker::equalToValue  ($numPedido);
    $hwtPedidoVenta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

    // ECRC: Localizando la Información de la Unidad
    $objCondicion = new \stdClass();
    $objCondicion->codigo        = Dataworker::equalToString($codigoUnidad);
    $hwtVehiculo = Dataworker::findFirst('hwt_vehiculo',$objCondicion);

    // ECRC: Calculando el Número de Partida
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue ($hwtPedidoVenta->num_pedido);
    $numPartidaPedidoVenta = Dataworker::getMaxValue('hwt_pedido_venta_linea','num_partida',$objCondicion);
    Logger::write('Previo $numPartidaPedidoVenta: ' . json_encode($numPartidaPedidoVenta));

    $numPartidaPedidoVenta = intval($numPartidaPedidoVenta);
    $numPartidaPedidoVenta = $numPartidaPedidoVenta + 10;

    Logger::write('Posterior $numPartidaPedidoVenta: ' . json_encode($numPartidaPedidoVenta));

    Logger::write('hwtPedidoVenta...');
    Logger::write(json_encode($hwtPedidoVenta));


    $valorUnitario = floatval($hwtVehiculo->precio_con_iva) / 1.16;
    $valorTotal    = floatval($hwtVehiculo->precio_con_iva);
    $valorImpuesto = ($valorTotal - $valorUnitario);

    // ECRC: Estableciendo el Resto de Valores por Defecto
    Receiver::resetApiParameters();
    Receiver::setApiParameterValue('tfNumPedido'      ,$numPedido);
    Receiver::setApiParameterValue('tfNumPartida'     ,$numPartidaPedidoVenta);
    Receiver::setApiParameterValue('tfCodigo'         ,$codigoUnidad);
    Receiver::setApiParameterValue('tfCantidad'       ,1);
    Receiver::setApiParameterValue('tfValorUnitario'  ,$valorUnitario);
    Receiver::setApiParameterValue('tfValorImpuestos' ,$valorImpuesto);
    Receiver::setApiParameterValue('tfValorPartida'   ,$valorTotal);

    $objCamposRegistro   = Dataworker::setFieldsTable('hwt_pedido_venta_linea');

    Logger::write(json_encode($objCamposRegistro));

    $sqlEjecutado = Dataworker::updateRecord($objCamposRegistro);

    // ECRC: Actualizando la Cotización
    calculaPedidoVenta($numPedido);
    registraActualizacion($numPedido,$userId,'actualiza');

    // ECRC: Localizando el Pedido para retornar los Nuevos Valores
    Dataworker::openConnection();
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue  ($numPedido);
    $hwtPedidoVenta = Dataworker::getRecords('hwt_pedido_venta',$objCondicion);

    $registroActualizado = true;
    $apiMessage = 'Unidad actualizada en el Pedido de Venta.';

    Emissary::success($registroActualizado);
    Emissary::addMessage('info-api'        , $apiMessage);
    Emissary::addMessage('sql-ejecutado'   , $sqlEjecutado);
    Emissary::addData('camposRegistro' , $objCamposRegistro);
    Emissary::addData('hwtPedidoVenta'  , $hwtPedidoVenta->data);
    Dataworker::closeConnection();

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);    
}

function grabaPedidoVenta(){
    Logger::enable(true,'grabaPedidoVenta');
    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    if(intval(Receiver::getApiParameter('tfVinPrincipal')) > 0){
        //ECRC: Es un registro que se va a actualizar
    }
    else{
        //ECRC: Es un registro que se va a crear. Se establecen los Valores por Defecto
        $numPedido = Dataworker::getNextSequence('seq_pedido_venta');
        Logger::write('Numero generado ' . $numPedido);

        Receiver::setApiParameterValue('tfNumPedido',$numPedido);

        Receiver::setApiParameterValue('tfSituacionPedido','1');
    }

    $objCamposRegistro   = Dataworker::setFieldsTable('hwt_pedido_venta');
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

function listaPedidoVentaAdicional(){
    Emissary::prepareEnvelope();
    Dataworker::openConnection();

    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue  (Receiver::getApiParameter('tfNumPedido'));
    $hwtPedidoVentaAdicional = Dataworker::getRecords('hwt_pedido_venta_Adicional',$objCondicion);

    if($hwtPedidoVentaAdicional->numRecords > 0) {

        // ECRC: Cargando la Información de la Unidad en el Registro de Unidad de Cotización
        foreach($hwtPedidoVentaAdicional->data as $recordPedidoVentaAdicional){

            $objCondicion = new \stdClass();
            $objCondicion->codigo_proveedor        = Dataworker::equalToString($recordPedidoVentaAdicional->codigo_proveedor);
            $hwtProveedor = Dataworker::findFirst('hwt_proveedor',$objCondicion);

            $recordPedidoVentaAdicional->nombre_proveedor            = $hwtProveedor->razon_social;
        }

        $availableInfo = true;
        $apiMessage = 'Registros Localizados';
        Emissary::addMessage('info-api'             , $apiMessage);
        Emissary::addData('hwtPedidoVentaAdicional' , $hwtPedidoVentaAdicional->data);
        Emissary::addData('numRecords'          , $hwtPedidoVentaAdicional->numRecords);
    }
    else{
        $availableInfo = false;
        $apiMessage = 'No hay Registros en la Base';
        Emissary::addMessage('info-api' , $apiMessage);
    }

    Dataworker::closeConnection();
    Emissary::success(true);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function listaPedidoVentaLinea(){
    Emissary::prepareEnvelope();
    Dataworker::openConnection();

    $objCondicion = new \stdClass();
    $objCondicion->num_pedido = Dataworker::equalToValue  (Receiver::getApiParameter('tfNumPedido'));
    $hwtPedidoVentaLinea = Dataworker::getRecords('hwt_pedido_venta_linea',$objCondicion);

    if($hwtPedidoVentaLinea->numRecords > 0) {

        // ECRC: Cargando la Información de la Unidad en el Registro de Unidad de Cotización
        foreach($hwtPedidoVentaLinea->data as $recordPedidoVentaLinea){

            $objCondicion = new \stdClass();
            $objCondicion->codigo        = Dataworker::equalToString($recordPedidoVentaLinea->codigo);
            $hwtVehiculo = Dataworker::findFirst('hwt_vehiculo',$objCondicion);

            $recordPedidoVentaLinea->vin            = $hwtVehiculo->vin;
            $recordPedidoVentaLinea->modelo         = $hwtVehiculo->modelo;
            $recordPedidoVentaLinea->marca          = $hwtVehiculo->marca;
            $recordPedidoVentaLinea->ann_unidad     = $hwtVehiculo->ann_unidad;
            $recordPedidoVentaLinea->estado_unidad  = $hwtVehiculo->estado_unidad;
            $recordPedidoVentaLinea->precio_sin_iva = $hwtVehiculo->precio_sin_iva;
            $recordPedidoVentaLinea->precio_con_iva = $hwtVehiculo->precio_con_iva;
        }

        $availableInfo = true;
        $apiMessage = 'Registros Localizados';
        Emissary::addMessage('info-api'             , $apiMessage);
        Emissary::addData('hwtPedidoVentaLinea' , $hwtPedidoVentaLinea->data);
        Emissary::addData('numRecords'          , $hwtPedidoVentaLinea->numRecords);
    }
    else{
        $availableInfo = false;
        $apiMessage = 'No hay Registros en la Base';
        Emissary::addMessage('info-api' , $apiMessage);
    }

    Dataworker::closeConnection();
    Emissary::success(true);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function listaPedidoVenta(){
    Logger::enable(true,'listaPedidoVenta');

    Mnemea::wakeUp();

    Emissary::prepareEnvelope();
    Dataworker::openConnection();
    $filtroEstado = substr(Receiver::getApiParameter('filtroEstado'),0,1);


    if($filtroEstado === 'A'){
        $objCondicion = null;
    }
    else{
        $objCondicion = new \stdClass();
        $objCondicion->situacion_pedido = Dataworker::equalToString($filtroEstado);
    }

    ///////////////////////////////////////////////////
    /// ECRC: Preparando la Consulta para Busqueda. ///
    ///////////////////////////////////////////////////
    if($filtroEstado === 'B'){
        $buscaGerenteRegional  = Receiver::getApiParameter('cbxBuscaGerenteRegional');
        $buscaVendedor         = Receiver::getApiParameter('cbxBuscaVendedor');
        $buscaConsecionario    = Receiver::getApiParameter('cbxBuscaConsecionario');
        $buscaCliente          = Receiver::getApiParameter('tfBuscaCliente');
        $buscaUnidad           = Receiver::getApiParameter('tfBuscaUnidad');
        $buscaFechaInicial     = Receiver::getApiParameter('dtBuscaFechaInicial');
        $buscaFechaFinal       = Receiver::getApiParameter('dtBuscaFechaFinal');
        $buscaSituacionInicial = Receiver::getApiParameter('cbxBuscaSituacionInicial');
        $buscaSituacionFinal   = Receiver::getApiParameter('cbxBuscaSituacionFinal');

        $objCondicion = new \stdClass();

        if($buscaGerenteRegional !== 'todo'){
            $objCondicion->codigo_gerente_regional = Dataworker::equalToString($buscaGerenteRegional);
        }

        if($buscaVendedor !== 'todo'){
            $objCondicion->codigo_vendedor = Dataworker::equalToString($buscaVendedor);
        }

        if($buscaConsecionario !== 'todo'){
            $objCondicion->codigo_consecionario = Dataworker::equalToString($buscaConsecionario);
        }

        if($buscaCliente !== ''){
            $objCondicion->codigo_cliente = Dataworker::equalToValue($buscaCliente);
        }

        if($buscaUnidad !== ''){
            $objCondicionLinea = new \stdClass();
            $objCondicionLinea->codigo = Dataworker::equalToString($buscaUnidad);
            $hwtPedidoVentaLinea = Dataworker::getRecords('hwt_pedido_venta_linea',$objCondicionLinea);

            foreach($hwtPedidoVentaLinea->data as $recordPedidoVentaLinea){
                $objCondicion->num_pedido = Dataworker::equalToValue($recordPedidoVentaLinea->num_pedido);
            }
        }

        $objCondicion->fecha_pedido_range_ini = Dataworker::compare('greaterEqualThan','date', $buscaFechaInicial);
        $objCondicion->fecha_pedido_range_end = Dataworker::compare('lessEqualThan','date', $buscaFechaFinal);

        $objCondicion->situacion_pedido_range_ini = Dataworker::compare('greaterEqualThan','string', $buscaSituacionInicial);
        $objCondicion->situacion_pedido_range_end = Dataworker::compare('lessEqualThan','string', $buscaSituacionFinal);

    }

    $hwtPedidoVenta = Dataworker::getRecords('hwt_pedido_venta',$objCondicion);

    $objectOpcionesFiltroSituacionPedido = listaParametro('combos_pedido'  ,'situacion_pedido');

    if($hwtPedidoVenta->numRecords > 0) {
        foreach($hwtPedidoVenta->data as $recordPedidoVenta){
            // ECRC: Estableciendo la Descripción de la Situación del Pedido
            $recordPedidoVenta->situacion_pedido_descripcion = '';
            foreach($objectOpcionesFiltroSituacionPedido as $opcionSituacionPedido){
                if($recordPedidoVenta->situacion_pedido_descripcion !== ''){
                    break;
                }

                $codigoSituacion = substr($opcionSituacionPedido->descripcion,0,1);
                $descripcionSituacion =substr($opcionSituacionPedido->descripcion,2);

                if($codigoSituacion === $recordPedidoVenta->situacion_pedido){
                    $recordPedidoVenta->situacion_pedido_descripcion = $descripcionSituacion;
                }
            }

            // ECRC: Localizando al Cliente
            $recordPedidoVenta->cliente_nombre = nombreCliente($recordPedidoVenta->codigo_cliente);

            // ECRC: Localizando a los Usuarios de Gerente y Vendedor
            $recordPedidoVenta->gerente_regional_nombre = nombreUsuario($recordPedidoVenta->codigo_gerente_regional);
            $recordPedidoVenta->vendedor_nombre         = nombreUsuario($recordPedidoVenta->codigo_vendedor);

            // ECRC: Localizando al Consecionar
            $recordPedidoVenta->concesionario_descripcion = descripcionConsecionario($recordPedidoVenta->codigo_consecionario);

            // ECRC: Localizando la Sucursal
            $recordPedidoVenta->consecionario_sucursal = descripcionConsecionarioSucursal(
                $recordPedidoVenta->codigo_consecionario,
                $recordPedidoVenta->codigo_sucursal);
        }

        $availableInfo = true;
        $apiMessage = 'Registros Localizados';
        Emissary::addMessage('info-api'       , $apiMessage);
        Emissary::addData('hwtPedidoVenta' , $hwtPedidoVenta->data);
        Emissary::addData('numRecords'    , $hwtPedidoVenta->numRecords);
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

function datosOpciones(){
    Logger::enable(true,'apiPedidoVenta::datosOpciones');

    Emissary::prepareEnvelope();

    Dataworker::openConnection();

    $objectOpcionesFiltroSituacionPedido = listaParametro('combos_pedido'  ,'situacion_pedido');
    $objectOpcionesFiltroTipoEntrega     = listaParametro('combos_pedido'  ,'tipo_entrega');
    $objectOpcionesFiltroModelo          = listaParametro('combos_unidades','modelo');
    $objectOpcionesFiltroMarca           = listaParametro('combos_unidades','marca');

    $keySituacionPedido      = 'keySituacionPedido';
    $keyBuscaSituacionPedido = 'keyBuscaSituacionPedido';
    Mnemea::wakeUp();
    if(!Mnemea::checkKey($keySituacionPedido)){
        //ECRC: Extrayendo el Código y la Descripción de Situaciones del Pedido

        foreach($objectOpcionesFiltroSituacionPedido as $opcionFiltroSituacionPedido){
            $opcionFiltroSituacionPedido->codigo      = substr($opcionFiltroSituacionPedido->descripcion,0,1);
            $opcionFiltroSituacionPedido->descripcion = substr($opcionFiltroSituacionPedido->descripcion,2);
        }

        //ECRC: Agregando los Registros Comodines para la Extracción de todos los registros
        $arrayOpcionesBuscaSituacionPedido  = (array) $objectOpcionesFiltroSituacionPedido;
        $arrayOpcionesFiltroSituacionPedido = (array) $objectOpcionesFiltroSituacionPedido;
        $objValue = (object) [
            codigo      => 'ALL',
            descripcion => 'TODAS LAS SITUACIONES',
            valor       => 'TODOS'
        ];
        array_push($arrayOpcionesFiltroSituacionPedido,$objValue);
        $objectOpcionesFiltroSituacionPedido = (object) $arrayOpcionesFiltroSituacionPedido;

        Mnemea::setKey($keySituacionPedido,json_encode($arrayOpcionesFiltroSituacionPedido));
        Mnemea::setKey($keyBuscaSituacionPedido,json_encode($arrayOpcionesBuscaSituacionPedido));
    }
    else{
        $arrayOpcionesFiltroSituacionPedido  = json_decode(Mnemea::getKey($keySituacionPedido));
        $arrayOpcionesBuscaSituacionPedido   = json_decode(Mnemea::getKey($keyBuscaSituacionPedido));

        $objectOpcionesFiltroSituacionPedido = (object) $arrayOpcionesFiltroSituacionPedido;
        $objectOpcionesBuscaSituacionPedido  = (object) $arrayOpcionesBuscaSituacionPedido;
    }

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

    // ECRC: Obteniendo el Listado de las Sucursales
    $hwtSucursal = Dataworker::getRecords('hwt_consecionario_sucursal');

    // ECRC: Cerrando la Conexión
    Dataworker::closeConnection();

    $availableInfo = true;
    $apiMessage = 'Información para Opciones de Formulario';
    Emissary::addMessage('info-api' , $apiMessage);
    Emissary::addData('opcionesFiltroSituacionPedido' , $objectOpcionesFiltroSituacionPedido);
    Emissary::addData('opcionesBuscaSituacionPedido'  , $objectOpcionesBuscaSituacionPedido);

    Emissary::addData('opcionesFiltroTipoEntrega'     , $objectOpcionesFiltroTipoEntrega);

    Emissary::addData('opcionesCodigoGerenteRegional', listadoGerentesRegionales());
    Emissary::addData('opcionesBuscaGerenteRegional', listadoGerentesRegionales(true));

    Emissary::addData('opcionesCodigoVendedor', listaVendedores());
    Emissary::addData('opcionesBuscaVendedor', listaVendedores(true));

    Emissary::addData('opcionesCodigoConsecionario', listadoConsecionarios());
    Emissary::addData('opcionesBuscaConsecionario', listadoConsecionarios(true));

    Emissary::addData('opcionesCodigoSucursal'        , $hwtSucursal->data);
    Emissary::addData('opcionesBuscaSucursal'         , $hwtSucursal->data);

    Emissary::addData('opcionesCodigoConsecionarioEntrega', listadoConsecionarios());
    Emissary::addData('opcionesCodigoSucursalEntrega'      , $hwtSucursal->data);
    Emissary::addData('opcionesFiltroModelo'               , $objectOpcionesFiltroModelo);
    Emissary::addData('opcionesFiltroMarca'                , $objectOpcionesFiltroMarca);

    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

function datosPedidoVenta($pNumPedido,$pMensaje,$pProcesoCorrecto = null){
    Logger::enable(true,'datosPedidoVenta');

    Dataworker::openConnection();

    if(isset($pNumPedido)){
        $numPedido = $pNumPedido;
    }
    else{
        $numPedido = Receiver::getApiParameter('numPedido');
    }

    Emissary::prepareEnvelope();

    Logger::write('iniciando la busqueda');

    // ECRC: Localizando el Pedido de Venta
    $objCondicion = new \stdClass();
    $objCondicion->num_pedido        = Dataworker::equalToValue($numPedido);
    $hwtPedidoVenta = Dataworker::findFirst('hwt_pedido_venta',$objCondicion);

    // ECRC: Localizando al Cliente
    $objCondicion = new \stdClass();
    $objCondicion->codigo_cliente        = Dataworker::equalToValue($hwtPedidoVenta->codigo_cliente);
    $hwtCliente = Dataworker::findFirst('hwt_cliente',$objCondicion);
    $hwtPedidoVenta->nombre_cliente   = $hwtCliente->razon_social;
    $hwtPedidoVenta->rfc_cliente      = $hwtCliente->rfc;
    $hwtPedidoVenta->contacto_cliente = $hwtCliente->contacto_nombre;

    //ECRC: Localizando a los Usuarios
    for ($iCiclo =1; $iCiclo <= 3; $iCiclo++){
        $objCondicion = new \stdClass();
        switch ($iCiclo){
            case 1:
                $objCondicion->usuario = Dataworker::equalToString($hwtPedidoVenta->usuario_implantacion);
                $sysUsuario = Dataworker::findFirst('sys_usuario',$objCondicion);
                $hwtPedidoVenta->usuario_nombre_implantacion   = $sysUsuario->nombre;
                break;
            case 2:
                $objCondicion->usuario = Dataworker::equalToString($hwtPedidoVenta->usuario_actualizacion);
                $sysUsuario = Dataworker::findFirst('sys_usuario',$objCondicion);
                $hwtPedidoVenta->usuario_nombre_actualizacion   = $sysUsuario->nombre;
                break;
            case 3:
                $objCondicion->usuario = Dataworker::equalToString($hwtPedidoVenta->usuario_cancelacion);
                $sysUsuario = Dataworker::findFirst('sys_usuario',$objCondicion);
                $hwtPedidoVenta->usuario_nombre_cancelacion   = $sysUsuario->nombre;
                break;
        }
    }

    //ECRC: Desplegando las descripciones adicionales del Pedido
    $keySituacionPedido = 'keySituacionPedido';

    Mnemea::wakeUp();
    $objSituacionPedido = json_decode(Mnemea::getKey($keySituacionPedido));

    foreach($objSituacionPedido as $recSituacionPedido){
        if(intval($recSituacionPedido->codigo) === intval($hwtPedidoVenta->situacion_pedido)){
            $hwtPedidoVenta->situacion_pedido_descripcion = $recSituacionPedido->descripcion;
        }
    }

    if(isset($pMensaje)){
        $apiMessage    = $pMensaje;
        $availableInfo = $pProcesoCorrecto;
    }
    else{
        $apiMessage = 'Información del Pedido de Venta Disponible';
        $availableInfo = true;
    }

    Emissary::addMessage('info-api'       , $apiMessage);
    Emissary::addData('hwtPedidoVenta' , $hwtPedidoVenta);

    Dataworker::closeConnection();
    Emissary::success($availableInfo);

    $objReturn = Emissary::getEnvelope();
    echo json_encode($objReturn);
}

/* ECRC: Bloque Principal de Ejecución */
$functionName = Receiver::getApiMethod();
call_user_func($functionName);


