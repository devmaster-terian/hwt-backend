<?php
define('_JEXEC', 1);
define('JPATH_BASE', $_SERVER['DOCUMENT_ROOT']); // define JPATH_BASE on the external file
define('DS', DIRECTORY_SEPARATOR);
$definesPhp = JPATH_BASE . DS . 'includes' . DS . 'defines.php';

require_once($definesPhp);
require_once(JPATH_BASE . DS . 'includes' . DS . 'framework.php');

if (isset($_REQUEST['hwtConsulta'])) {
    $hwtConsulta     = $_REQUEST["hwtConsulta"];
} else
    $hwtConsulta = 'INFORMACION';

function listaProductos(){
    $hwtTipoUnidad    = $_REQUEST["hwtTipoUnidad"];
    $hwtMarca         = $_REQUEST["hwtMarca"];
    $hwtModelo        = $_REQUEST["hwtModelo"];
    $hwtAnnInicial    = $_REQUEST["hwtAnnInicial"];
    $hwtAnnFinal      = $_REQUEST["hwtAnnFinal"];
    $hwtPrecioInicial = $_REQUEST["hwtPrecioInicial"];
    $hwtPrecioFinal   = $_REQUEST["hwtPrecioFinal"];
    $hwtUbicacion     = $_REQUEST["hwtUbicacion"];

    //Obteniendo una Conexión a la base de Datos
    $db = JFactory::getDbo();

    // Crear un nuevo objeto de Consulta (Query)
    $query = $db->getQuery(true);

    $query->select($db->quoteName(array('tipo_unidad', 'vin', 'codigo', 'ubicacion', 'traslado', 'modelo', 'marca', 'ann_unidad',
        'color', 'motor', 'modelo_motor', 'potencia_motor', 'numero_serie', 'marca_transmision', 'modelo_transmision',
        'velocidades', 'relacion_dif', 'eje_delantero_capacidad', 'eje_trasero_capacidad', 'kilometraje', 'distancia_ejes', 'tipo_cabina',
        'propietario_anterior', 'estado', 'precio_sin_iva', 'precio_con_iva')))
        ->from($db->quoteName('hwt_vehiculo'));

    $query->where($db->quoteName('estado_unidad') . ' LIKE '. $db->quote('DISPONIBLE'));

    if($hwtTipoUnidad !== "TODO"){
        $query->where($db->quoteName('tipo_unidad') . ' LIKE '. $db->quote($hwtTipoUnidad));
    }

    if($hwtMarca !== "TODO"){
        $query->where($db->quoteName('marca') . ' LIKE '. $db->quote($hwtMarca));
    }

    IF($hwtModelo !== "TODO"){
        $query->where($db->quoteName('modelo') . ' LIKE '. $db->quote($hwtModelo));
    }

    $query->where($db->quoteName('ann_unidad') . ' >= '. $db->quote($hwtAnnInicial));
    $query->where($db->quoteName('ann_unidad') . ' <= '. $db->quote($hwtAnnFinal));

    $query->where($db->quoteName('precio_con_iva') . ' >= '. $db->quote($hwtPrecioInicial));
    $query->where($db->quoteName('precio_con_iva') . ' <= '. $db->quote($hwtPrecioFinal));

    IF($hwtUbicacion !== "TODO"){
        $query->where($db->quoteName('ubicacion') . ' LIKE '. $db->quote($hwtUbicacion));
    }

    $query->order('ann_unidad ASC');

    // Reset the query using our newly populated query object.
    $db->setQuery($query);

    // ECRC: Número de Resultados
    $response = $db->query();
    $num_rows = $db->getNumRows();

    // Load the results as a list of stdClass objects (see later for more options on retrieving data).
    $results = $db->loadObjectList();
    $jsonResult = json_encode($results);
    echo($jsonResult);
} //listaProductos

function cargaSelectores(){
    //Obteniendo una Conexión a la base de Datos
    $db = JFactory::getDbo();

    // Crear un nuevo objeto de Consulta (Query)
    $query = $db->getQuery(true);

    $query->select($db->quoteName(array('tipo_unidad', 'marca', 'modelo', 'ann_unidad', 'precio_con_iva', 'ubicacion',)))
          ->from($db->quoteName('hwt_vehiculo'))
          ->where($db->quoteName('estado_unidad') . ' LIKE '. $db->quote('DISPONIBLE'))
          ->order('ann_unidad ASC');
    $db->setQuery($query);
    $results = $db->loadObjectList();
    $jsonResult = json_encode($results);
    echo($jsonResult);
}

//ECRC Bloque Principal de Ejecución
switch ($hwtConsulta) {
    case 'listaProductos':
        listaProductos();
        break;
    case 'cargaSelectores':
        cargaSelectores();
        break;
}