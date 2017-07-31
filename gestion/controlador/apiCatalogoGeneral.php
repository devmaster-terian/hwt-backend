<?php
/////////////////////////////////
/// Archivo: apiCatalogoGeneral.php
/// Creador: Terian Software Developer Team
/// Objetivo: Creación del Listado de Catálogos de uso común
//////////////////////////

require_once('../recurso/clase/Emissary.php');
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Dataworker.php');
require_once('../recurso/clase/Logger.php');

function listadoGerentesRegionales($pUsaComodin = null)
{
    // ECRC: Obteniendo el Listado de los Gerentes Regionales
    Logger::enable(true, 'listadoGerentesRegionales');

    Dataworker::openConnection();

    Logger::write('Obteniendo el Listado de los Gerentes Regionales');
    $objCondicion = new \stdClass();
    $objCondicion->codigo_perfil = Dataworker::equalToString('gerente');
    $sysUsuarioGerente = Dataworker::getRecords('sys_usuario_perfil', $objCondicion);

    $arrayOpcionesGerente = array();
    $arrayBuscaGerente = array();
    foreach ($sysUsuarioGerente->data as $recordUsuarioGerente) {

        // ECRC: Localizando al Perfil Principal del Usuario
        $objCondicion = new \stdClass();
        $objCondicion->usuario = Dataworker::equalToString($recordUsuarioGerente->usuario);
        $sysUsuario = Dataworker::findFirst('sys_usuario', $objCondicion);
        unset($sysUsuario->acceso);
        array_push($arrayOpcionesGerente, $sysUsuario);
        array_push($arrayBuscaGerente, $sysUsuario);
    }

    Dataworker::closeConnection();

    if ($pUsaComodin) {
        $objGerenteComodin = new \stdClass();
        $objGerenteComodin->usuario = 'todo';
        $objGerenteComodin->nombre = 'TODOS LOS GERENTES REGIONALES';
        array_push($arrayBuscaGerente, $objGerenteComodin);
        return $arrayBuscaGerente;
    } else {
        return $arrayOpcionesGerente;
    }
}

function listaVendedores($pUsaComodin = null)
{
    Logger::enable(true, 'listaVendedores');
    Dataworker::openConnection();
    // ECRC: Obteniendo el Listado de los Vendedores
    Logger::write('Obteniendo el Listado de los Vendedores');
    $objCondicion = new \stdClass();
    $objCondicion->codigo_perfil = Dataworker::equalToString('vendedor');
    $sysUsuarioVendedor = Dataworker::getRecords('sys_usuario_perfil', $objCondicion);

    $arrayOpcionesVendedor = array();
    $arrayBuscaVendedor = array();
    foreach ($sysUsuarioVendedor->data as $recordUsuarioVendedor) {

        // ECRC: Localizando al Perfil Principal del Usuario
        $objCondicion = new \stdClass();
        $objCondicion->usuario = Dataworker::equalToString($recordUsuarioVendedor->usuario);
        $sysUsuario = Dataworker::findFirst('sys_usuario', $objCondicion);
        unset($sysUsuario->acceso);
        array_push($arrayOpcionesVendedor, $sysUsuario);
        array_push($arrayBuscaVendedor, $sysUsuario);
    }

    Dataworker::closeConnection();

    if ($pUsaComodin) {
        $objVendedorComodin = new \stdClass();
        $objVendedorComodin->usuario = 'todo';
        $objVendedorComodin->nombre = 'TODOS LOS VENDEDORES';
        array_push($arrayBuscaVendedor, $objVendedorComodin);
        return $arrayBuscaVendedor;
    } else {
        return $arrayOpcionesVendedor;
    }
}

function listadoConsecionarios($pUsaComodin = null)
{
    // ECRC: Obteniendo el Listado de los Consecionarios
    Dataworker::openConnection();
    $hwtConsecionario = Dataworker::getRecords('hwt_consecionario');
    Dataworker::closeConnection();

    if ($pUsaComodin) {
        $arrayConsecionario = (array)$hwtConsecionario->data;
        $objConsecionario = new \stdClass();
        $objConsecionario->codigo_consecionario = 'todo';
        $objConsecionario->descripcion = 'TODOS LOS CONSECIONARIOS';

        array_push($arrayConsecionario, $objConsecionario);
        return $arrayConsecionario;
    } else {
        return $hwtConsecionario->data;
    }
}

function listaPais()
{
    $arrayColumnas = array();
    array_push($arrayColumnas, 'cod_pais');
    array_push($arrayColumnas, 'pais');

    $tabla = 'sys_localizacion';

    Dataworker::openConnection();
    $hwtPais = Dataworker::getDistinct(
        $arrayColumnas,
        $tabla);
    Dataworker::closeConnection();

    return $hwtPais;
}

function obtieneNombrePais($pCodPais)
{
    Dataworker::openConnection();
    $objCondicion = new \stdClass();
    $objCondicion->cod_pais = Dataworker::equalToString($pCodPais);
    $sysCiudadEstado = Dataworker::findFirst('sys_localizacion', $objCondicion);
    Dataworker::closeConnection();

    return $sysCiudadEstado->pais;
}

function listaEstado($pCodPais)
{
    $arrayColumnas = array();
    array_push($arrayColumnas, 'cod_estado');
    array_push($arrayColumnas, 'estado');

    $tabla = 'sys_localizacion';

    $objConsulta = new \stdClass();
    $objConsulta->cod_pais = Dataworker::equalToString($pCodPais);

    $arrayOrden = array();
    array_push($arrayOrden, 'estado');

    Dataworker::openConnection();
    $hwtEstado = Dataworker::getDistinct(
        $arrayColumnas,
        $tabla,
        $objConsulta,
        $arrayOrden);
    Dataworker::closeConnection();

    return $hwtEstado;
}

function obtieneNombreEstado($pCodPais, $pCodEstado)
{
    Dataworker::openConnection();
    $objCondicion = new \stdClass();
    $objCondicion->cod_pais = Dataworker::equalToString($pCodPais);
    $objCondicion->cod_estado = Dataworker::equalToValue($pCodEstado);
    $sysCiudadEstado = Dataworker::findFirst('sys_localizacion', $objCondicion);
    Dataworker::closeConnection();

    return $sysCiudadEstado->estado;
}

function listaMunicipio($pCodPais, $pCodEstado)
{
    $arrayColumnas = array();
    array_push($arrayColumnas, 'cod_municipio');
    array_push($arrayColumnas, 'municipio');

    $tabla = 'sys_localizacion';

    $objConsulta = new \stdClass();
    $objConsulta->cod_pais = Dataworker::equalToString($pCodPais);
    $objConsulta->cod_estado = Dataworker::equalToValue($pCodEstado);
    $objConsulta->zona = Dataworker::compare('notEqualThan', 'string', 'Rural');

    $arrayOrden = array();
    array_push($arrayOrden, 'municipio');

    Dataworker::openConnection();
    $hwtEstado = Dataworker::getDistinct(
        $arrayColumnas,
        $tabla,
        $objConsulta,
        $arrayOrden);
    Dataworker::closeConnection();

    return $hwtEstado;
}

function obtieneNombreMunicipio($pCodPais, $pCodEstado, $pCodMunicipio)
{
    Dataworker::openConnection();
    $objCondicion = new \stdClass();
    $objCondicion->cod_pais = Dataworker::equalToString($pCodPais);
    $objCondicion->cod_estado = Dataworker::equalToValue($pCodEstado);
    $objCondicion->cod_municipio = Dataworker::equalToValue($pCodMunicipio);
    $sysCiudadEstado = Dataworker::findFirst('sys_localizacion', $objCondicion);
    Dataworker::closeConnection();

    return $sysCiudadEstado->municipio;
}


function listaCiudad($pCodPais, $pCodEstado, $codMunicipio)
{
    $arrayColumnas = array();
    array_push($arrayColumnas, 'asentamiento');

    $tabla = 'sys_localizacion';

    $objConsulta = new \stdClass();
    $objConsulta->cod_pais = Dataworker::equalToString($pCodPais);
    $objConsulta->cod_estado = Dataworker::equalToValue($pCodEstado);
    $objConsulta->cod_municipio = Dataworker::equalToValue($codMunicipio);
    $objConsulta->zona = Dataworker::compare('notEqualThan', 'string', 'Rural');

    $arrayOrden = array();
    array_push($arrayOrden, 'asentamiento');

    Dataworker::openConnection();
    $hwtEstado = Dataworker::getDistinct(
        $arrayColumnas,
        $tabla,
        $objConsulta,
        $arrayOrden);
    Dataworker::closeConnection();

    return $hwtEstado;
}

function nombreCliente($pCliente)
{

    $keyCliente = 'keyCliente' . $pCliente;
    $valorRetorno = '';

    if (Mnemea::checkKey($keyCliente)) {
        $valorRetorno = Mnemea::getKey($keyCliente);
    } else {
        Dataworker::openConnection();
        $objCondicion = new \stdClass();
        $objCondicion->codigo_cliente = Dataworker::equalToString($pCliente);
        $hwtCliente = Dataworker::findFirst('hwt_cliente', $objCondicion);
        Dataworker::closeConnection();

        $valorRetorno = $hwtCliente->razon_social;

        Mnemea::setKey($keyCliente, $hwtCliente->razon_social);
    }

    $valorRetorno = strtoupper($valorRetorno);
    return $valorRetorno;
}

function nombreUsuario($pUsuario)
{
    $keyUsuario = 'keyUsuario' . $pUsuario;
    $valorRetorno = '';
    if (Mnemea::checkKey($keyUsuario)) {
        $valorRetorno = Mnemea::getKey($keyUsuario);
    } else {
        Dataworker::openConnection();
        $objCondicion = new \stdClass();
        $objCondicion->usuario = Dataworker::equalToString($pUsuario);
        $sysUsuario = Dataworker::findFirst('sys_usuario', $objCondicion);
        Dataworker::closeConnection();

        $valorRetorno = $sysUsuario->nombre;

        Mnemea::setKey($keyUsuario, $sysUsuario->nombre);
    }

    $valorRetorno = strtoupper($valorRetorno);
    return $valorRetorno;
}

function descripcionConsecionario($pConsecionario)
{
    $keyConsecionario = 'keyConsecionario' . $pConsecionario;
    $valorRetorno = '';
    if (Mnemea::checkKey($keyConsecionario)) {
        $valorRetorno = Mnemea::getKey($keyConsecionario);
    } else {
        Dataworker::openConnection();
        $objCondicion = new \stdClass();
        $objCondicion->codigo_consecionario = Dataworker::equalToString($pConsecionario);
        $hwtConsecionario = Dataworker::findFirst('hwt_consecionario', $objCondicion);
        Dataworker::closeConnection();

        $valorRetorno = $hwtConsecionario->descripcion;

        Mnemea::setKey($keyConsecionario, $hwtConsecionario->descripcion);
    }

    return $valorRetorno;
}

function descripcionConsecionarioSucursal($pConsecionario, $pConsecionarioSucursal)
{
    $keySucursal = 'keySucursal' . $pConsecionario . $pConsecionarioSucursal;
    $valorRetorno = '';
    if (Mnemea::checkKey($keySucursal)) {
        $valorRetorno = Mnemea::getKey($keySucursal);
    } else {
        Dataworker::openConnection();
        $objCondicion = new \stdClass();
        $objCondicion->codigo_consecionario = Dataworker::equalToString($pConsecionario);
        $objCondicion->codigo_sucursal = Dataworker::equalToString($pConsecionarioSucursal);
        $hwtConsecionarioSucursal = Dataworker::findFirst('hwt_consecionario_sucursal', $objCondicion);
        Dataworker::closeConnection();

        $valorRetorno = $hwtConsecionarioSucursal->descripcion;

        Mnemea::setKey($keySucursal, $hwtConsecionarioSucursal->descripcion);
    }

    return $valorRetorno;
}