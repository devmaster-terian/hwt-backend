<?php
require_once('../recurso/clase/Receiver.php');
require_once('../recurso/clase/Logger.php');
require_once('../recurso/clase/Datarecord.php');
ini_set('display_errors', false);
ini_set('display_startup_errors', false);

define('EQUAL_TO', 'equalTo');

class Dataworker
{
    protected $Connection;

    private static $activeConnection;

    public static function getDistinct($pArrayColumn, $pTable, $pObjConstraint = null, $pArrayOrder = null)
    {
        Logger::enable(true, 'getDistinct');

        $fieldsTable = '';
        foreach ($pArrayColumn as $fieldColumn) {
            if ($fieldsTable === '') {
                $fieldsTable = $fieldColumn;
            } else {
                $fieldsTable = $fieldsTable . ', ' . $fieldColumn;
            }
        }

        $SqlQuery = "SELECT DISTINCT " . $fieldsTable . "   FROM " . $pTable . " ";


        Logger::write(json_encode($pObjConstraint));
        if ($pObjConstraint !== null) {
            $Constraint = null;
            foreach ($pObjConstraint as $key => $value) {

                Logger::write('$key: ' . $key . ' ------ ' . '$value: ' . $value);
                Logger::write('$Constraint: ' . $Constraint);

                if ($Constraint === null) {
                    $Constraint = 'WHERE ' . $key . $value . ' ';
                } else {
                    $Constraint = 'AND ' . $key . $value . ' ';
                }

                $SqlQuery = $SqlQuery . $Constraint;
            }
        }

        if ($pArrayOrder !== null) {
            $SqlQuery = $SqlQuery . ' ORDER BY ';
            $numFields = 0;
            foreach ($pArrayOrder as $fieldOrder) {
                if ($numFields === 0) {
                    $SqlQuery = $SqlQuery . $fieldOrder;
                } else {
                    $SqlQuery = $SqlQuery . ', ' . $fieldOrder;
                }

                $numFields = $numFields + 1;
            }
        }

        Logger::write($SqlQuery);
        $objResultQuery = self::executeQuery($SqlQuery);

        $recordResult = $objResultQuery->data;

        return $recordResult;
    }

    public static function getMaxValue($pTable, $pField, $pObjConstraint){
        Logger::enable(true,'getMaxValue');
        $SqlQuery = "SELECT MAX(" . $pField . ") AS 'maxvalue'    FROM " . $pTable. " ";

        $Constraint = '';

        if($pObjConstraint !== null){

            foreach($pObjConstraint as $key=>$value) {
                if($Constraint === ''){
                    $Constraint = 'WHERE ' . $key . $value . ' ';
                }
                else{
                    $Constraint = 'AND ' . $key . $value . ' ';
                }
            }

            $SqlQuery = $SqlQuery . $Constraint;
        }

        Logger::write($SqlQuery);
        $objResultQuery = self::executeQuery($SqlQuery);

        $recordResult = $objResultQuery->data;

        $maxValue = 0;
        foreach($recordResult as $recordValue){
            $maxValue = intval($recordValue->maxvalue);
        }

        return $maxValue;
    }

    public static function equalToValue($field,$type = null){
        $returnValue = ' = ' . $field ;

        if($type != null){
            if($type === 'string'){
                $returnValue = ' = "' . $field .'"';
            }
        }
        return $returnValue;
    }

    public static function equalToString($field){
        $returnValue = ' = "' . $field .'"';
        return $returnValue;
    }

    public static function compare($comparison,$type,$field){

        $operator = '';
        $initField = '';
        $endField = '';

        if($type === 'string'){
            $initField = '"';
            $endField  = '"';
        }


        switch($comparison){
            case 'notEqualThan':
                $operator = ' != ';
                break;
            case 'equalTo':
                $operator = ' = ';
                break;
            case 'greaterThan':
                $operator = ' > ';
                break;
            case 'greaterEqualThan':
                $operator = ' >= ';
                break;
            case 'lessThan':
                $operator = ' < ';
                break;
            case 'lessEqualThan':
                $operator = ' <= ';
                break;
        }

        if($type === 'date'){
            $field = self::convertDate($field);
        }

        $returnValue = $operator . $initField . $field . $endField . '';
        return $returnValue;
    }

    public static function deleteRecord($objFieldsRecord){
        Logger::enable(true,'deleteRecord');
        $SqlUpdateQuery = "DELETE FROM " . $objFieldsRecord->tableName
                        . " WHERE " . $objFieldsRecord->keyField . " = " . $objFieldsRecord->keyValue;

        Logger::write('Query ' . $SqlUpdateQuery);
        $objResult = self::updateQuery($SqlUpdateQuery);

        return $objResult;
    }

    public static function updateRecord($objFieldsRecord){
        Logger::enable(true,'updateRecord');

        $SqlSpace = ' ';
        $SqlUpdateQuery = "INSERT INTO  " . $objFieldsRecord->tableName
            . $SqlSpace . $objFieldsRecord->fieldList
            . " VALUES " . $objFieldsRecord->fieldValue
            . " ON DUPLICATE KEY UPDATE "
            . $objFieldsRecord->fieldRecord;


        Logger::write($SqlUpdateQuery);
        self::updateQuery($SqlUpdateQuery);

        return $SqlUpdateQuery;
    }

    public static function dashedToCamel($dashedString, $capitalizeFirstCharacter = true)
    {
        $returnValue = str_replace(' ', '', ucwords(str_replace('_', ' ', $dashedString)));

        if (!$capitalizeFirstCharacter) {
            $returnValue[0] = strtolower($returnValue[0]);
        }

        return $returnValue;
    }

    private static function convertDate($fieldDataValue){
        $dateDateReady = false;
        if(preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', str_replace("'","",$fieldDataValue))){
            $dateDateReady = true;
        } else {
            $dateDateReady = false;
        }

        if($dateDateReady){
            $fieldDataValue = 'STR_TO_DATE(' . $fieldDataValue . ',\'%Y-%m-%d\')';
        }
        else{
            if(strpos($fieldDataValue,'/')){
                $fieldDataValue = "STR_TO_DATE('" . $fieldDataValue . "','%m/%d/%Y')";
            }
            else{
                $fieldDataValue = 'STR_TO_DATE(' . $fieldDataValue . ',\'%m-%d-%Y\')';
            }
        }

        return $fieldDataValue;
    }

    public static function setFieldsTable($tableName, $objectData = null)
    {
        Logger::enable(true,'setFieldsTable');

        $Connection = self::$activeConnection;
        $SqlQuery = "SHOW COLUMNS FROM $tableName";
        $SqlResult  = $Connection->query($SqlQuery);
        $numRecords = 0;

        $objFieldsTable = (object) [];

        $fieldList   = '';
        $fieldValue  = '';
        $fieldRecord = '';
        $rows = array();

        while($row = $SqlResult->fetch_assoc()) {
            $numRecords = $numRecords + 1;
            $fieldName  = $row["Field"];
            $fieldType  = $row["Type"];

            if($fieldName === 'rowid'){
                continue;
            }

            if(isset($objectData)){
                if(property_exists($objectData,$fieldName)){
                    $objFieldsTable->$fieldName = $objectData->$fieldName;
                }
                else{
                    continue;
                }
            }else{
                $parameterApi = self::dashedToCamel($fieldName);
                $objFieldsTable->$fieldName = Receiver::getApiParameter($parameterApi);

                if(is_array($objFieldsTable->$fieldName)){
                    $objFieldsTable->$fieldName = implode(',',$objFieldsTable->$fieldName);
                }
            }

            if(!strpos($fieldName, 'mail') and !strpos($fieldName, 'correo')){
                $objFieldsTable->$fieldName = strtoupper($objFieldsTable->$fieldName);
            }

            //ECRC: Concatenate the Field Value List.
            if(property_exists($objFieldsTable,$fieldName)){
                //ECRC: Concatenate the Field List.
                if($fieldList === ''){
                    $fieldList = $fieldName;
                }
                else{
                    $fieldList = $fieldList . ',' . $fieldName;
                }

                if(preg_match('#^float#'  , $fieldType) === 1
                or preg_match('#^double#' , $fieldType) === 1
                or preg_match('#^int#'    , $fieldType) === 1){
                    $fieldDataValue = "0";
                    if(intval($objFieldsTable->$fieldName) <= 0){
                        $objFieldsTable->$fieldName = $fieldDataValue;
                    }
                }
                else{
                    $fieldDataValue = "";
                }

                if(preg_match('#^varchar#', $fieldType) === 1 or preg_match('#^date#', $fieldType) === 1){
                    $fieldDataValue = "'" . $objFieldsTable->$fieldName . "'" ;

                    if(preg_match('#^date#', $fieldType) === 1){
                        $fieldNameData = $objFieldsTable->$fieldName;
                        $fieldDataValue = "'" . str_replace('/', '-', substr($fieldNameData,0,10)) . "'" ;
                        if(strlen($fieldDataValue) < 10){
                            $fieldDataValue = 'null';
                        }

                        if($fieldDataValue !== null){
                            $fieldDataValue = self::convertDate($fieldDataValue);
                        }
                    }
                }
                else{
                    $fieldDataValue = $objFieldsTable->$fieldName;
                }

                if($fieldValue === ''){
                    $fieldValue = $fieldDataValue;
                }
                else{
                    $fieldValue = $fieldValue . ',' . $fieldDataValue;
                }

                $fieldDataValue = utf8_decode($fieldDataValue);
                $fieldDataValue = str_replace('á', 'A', $fieldDataValue);
                $fieldDataValue = str_replace('é', 'E', $fieldDataValue);
                $fieldDataValue = str_replace('í', 'I', $fieldDataValue);
                $fieldDataValue = str_replace('ó', 'O', $fieldDataValue);
                $fieldDataValue = str_replace('ú', 'U', $fieldDataValue);

                if($fieldRecord === ''){
                    $fieldRecord = $fieldName . "=" . $fieldDataValue;
                }
                else{
                    $fieldRecord = $fieldRecord . ',' . $fieldName . "=" . $fieldDataValue;
                }
            }
        }

        $fieldValue = utf8_decode($fieldValue);
        $fieldValue = str_replace('á', 'A', $fieldValue);
        $fieldValue = str_replace('é', 'E', $fieldValue);
        $fieldValue = str_replace('í', 'I', $fieldValue);
        $fieldValue = str_replace('ó', 'O', $fieldValue);
        $fieldValue = str_replace('ú', 'U', $fieldValue);

        $objFieldsTable->tableName   = $tableName;
        $objFieldsTable->fieldList   = '(' . $fieldList  . ')';
        $objFieldsTable->fieldValue  = '(' . $fieldValue . ')';
        $objFieldsTable->fieldRecord = $fieldRecord;

        return $objFieldsTable;
    }

    public static function utf8_converter($array)
    {
        array_walk_recursive($array, function (&$item, $key) {
            if (!mb_detect_encoding($item, 'utf-8', true)){
                $item = utf8_encode($item);
            }
        });

        return $array;
    }

    private static function getServer($pEnvironment){
            switch ($pEnvironment){
                case 'prod':
                    $objServer = new \stdClass();
                    $objServer->machinesrv = "localhost";
                    $objServer->datausn = "utilityt_hwtustr";
                    $objServer->datakey = "HWTU$3dTrucks";
                    $objServer->dataspace = "utilityt_hwtusedtrucks";
                    break;
                case 'beta':
                    $objServer = new stdClass();
                    $objServer->machinesrv = "localhost";
                    $objServer->datausn = "terianco_hwtusr";
                    $objServer->datakey = "4Ace33so$";
                    $objServer->dataspace = "terianco_hwtsite";
                    break;
                case 'dev':
                    $objServer = new stdClass();
                    $objServer->machinesrv = "localhost";
                    $objServer->datausn = "terianco";
                    $objServer->datakey = "t3ri@n$723";
                    $objServer->dataspace = "terianco_hwtsite";
                    break;
            }

        return $objServer;
    }

    public static function openConnection()    {
        $dbLive = false;

        if (self::validateConnection(self::$activeConnection)) {
            return self::$activeConnection;
        }

        for($iCiclo = 1; $iCiclo <= 3; $iCiclo++){

            switch($iCiclo){
                case 1:
                    $workingEnv = 'prod';
                    break;
                case 2:
                    $workingEnv = 'beta';
                    break;
                case 3:
                    $workingEnv = 'dev';
                    break;
            }

            $currentServer = self::getServer($workingEnv);
            $dbEstatus = '';

            if($dbLive === false){
                // Create connection
                $Connection = new mysqli(
                    $currentServer->machinesrv,
                    $currentServer->datausn,
                    $currentServer->datakey,
                    $currentServer->dataspace);

                // Check connection
                if ($Connection->connect_error) {
                    $dbEstatus = "Dataworker:: getConnection: Connection failed: " . $Connection->connect_error;
                }
                else{
                    $dbEstatus = 'Dataworker:: getConnection: Connection established in Environment ' . $workingEnv;
                    $dbLive = true;
                }
            }
        } // for iCiclo

        self::$activeConnection = $Connection;
        return $Connection;
    }

    public static function validateConnection($Connection)
    {
        if(!$Connection){
            $activeConnection = false;
        }
        else{
            $activeConnection = true;
        }
        return $activeConnection;
    }

    public static function executeQuery($SqlQuery)
    {
        $Connection = self::$activeConnection;
        $SqlResult  = $Connection->query($SqlQuery);
        $numRecords = 0;

        $rows = array();
        if(substr($SqlQuery,0,6) === "SELECT" and $SqlResult){
            while($row = $SqlResult->fetch_assoc()) {
                $numRecords = $numRecords + 1;
                array_push($rows,$row);
            }
        }

        $rows = self::utf8_converter($rows);
        $jsonData =  json_encode($rows);

        $jsonData =  json_decode($jsonData);
        $objResultQuery = new \stdClass();
        $objResultQuery->sqlQuery   = $SqlQuery;
        $objResultQuery->numRecords = $numRecords;
        $objResultQuery->data = $jsonData;

        return $objResultQuery;
    }

    public static function updateQuery($SqlQuery)
    {
        $Connection = self::$activeConnection;
        $SqlResult  = $Connection->query($SqlQuery);
        $numRecords = 0;

        $objResultQuery = new \stdClass();
        $objResultQuery->sqlQuery   = $SqlQuery;
        $objResultQuery->numRecords = $numRecords;
        $objResultQuery->success    = true;

        if(!empty(!$SqlResult)){
            $objResultQuery->sqlError = mysqli_error($Connection);
            $objResultQuery->success  = false;
        }

        return $objResultQuery;
    }

    public static function getRecords($Table, $pObjConstraint = null, $pArrayCustomFields = null)
    {
        Logger::enable(true,'getRecords');

        $Connection = self::$activeConnection;

        if ($pArrayCustomFields !== null) {
            $SqlQuery = "SELECT " . implode(',', $pArrayCustomFields) . " FROM " . $Table . " ";
        } else {
            $SqlQuery = "SELECT * FROM " . $Table . " ";
        }

        $Constraint = '';

        if($pObjConstraint !== null){
            foreach($pObjConstraint as $key=>$value) {

                $key = str_replace('_range_ini','',$key);
                $key = str_replace('_range_end','',$key);

                Logger::write($key . ' -> ' . $value);

                if($Constraint === ''){
                    $Constraint = 'WHERE ' . $key . $value . ' ';
                }
                else{
                    $Constraint = 'AND ' . $key . $value . ' ';
                }

                $SqlQuery = $SqlQuery . $Constraint;
            }
        }

        Logger::write($SqlQuery);
        $objResultQuery = self::executeQuery($SqlQuery);

        return $objResultQuery;
    }

    public static function closeConnection()
    {
        $Connection = self::$activeConnection;
        if($Connection){
            $Connection->close();
        }
    }

    public static function availableRecord($pDataRecord){
        Logger::enable(true,'availableRecord');
        Logger::write(json_encode($pDataRecord));

        $returnValue = false;
        if($pDataRecord->activeRecord === '1'){
            $returnValue = true;
        }

        Logger::write($returnValue);

        return $returnValue;
    }

    public static function findFirst($pTable, $pObjConstraint){
        Logger::enable(true,'findFirst');
        $SqlQuery = "SELECT * FROM $pTable ";
        $Constraint = '';

        if($pObjConstraint !== null){
            foreach($pObjConstraint as $key=>$value) {
                if($Constraint === ''){
                    $Constraint = 'WHERE ' . $key . $value . ' ';
                }
                else{
                    $Constraint = 'AND ' . $key . $value . ' ';
                }
                $SqlQuery = $SqlQuery . $Constraint;
            }
        }

        $SqlQuery = $SqlQuery . 'LIMIT 1';

        Logger::write($SqlQuery);
        $resultQuery = self::executeQuery($SqlQuery);

        $dataRecord = new Datarecord();
        $dataRecord->createProperty('activeRecord','0');

        $objRecord = $resultQuery->data;
        foreach($objRecord as $position => $record){
            foreach($record as $fieldRecord => $valueRecord){
                $dataRecord->createProperty('activeRecord','1');
                $dataRecord->createProperty($fieldRecord,$valueRecord);
            }
        }
        return $dataRecord;
    }

    public static function getNextSequence($pSequencer){
        Logger::enable(true,'getNextSequence');
        $SQLConnection = self::openConnection();
        $SqlQuery = "SELECT nextval('" . $pSequencer . "') as next_sequence";

        $resultQuery = self::executeQuery($SqlQuery);


        $dataRecord = new Datarecord();

        $objRecord = $resultQuery->data;
        foreach($objRecord as $position => $record){
            foreach($record as $fieldRecord => $valueRecord){
                $dataRecord->createProperty($fieldRecord,$valueRecord);
            }
        }
        return (int)$valueRecord;
    }

    public static function listOptions($fieldTable, $nameTable){
        $SqlQuery = "SELECT DISTINCT " . $fieldTable . " FROM " . $nameTable;
        $objResultQuery = self::executeQuery($SqlQuery);
        return $objResultQuery;
    }
}
