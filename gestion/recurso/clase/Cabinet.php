<?php


class Cabinet
{
    private static $pathDirectory;
    private static $internalPath;
    private static $externalPath;

    public static function getFileString($pFileField){
        $pFileField = $pFileField . '-button';
        return $pFileField;
    }

    public static function deleteFile($pFileToDelete){
        self::prepareDefaults();

        $pFileToDelete = str_replace(self::$externalPath,self::$internalPath,$pFileToDelete);
        
        $objFileDeleted = new \stdClass();
        $objFileDeleted->url = $pFileToDelete;
        
        if(file_exists($pFileToDelete)){
            unlink($pFileToDelete); // Eliminar el Archivo
            $objFileDeleted->message = 'Cabinet: File delete succesfully';
            $objFileDeleted->success = true;
        }
        else{
            $objFileDeleted->message = 'Cabinet: Fail deleting file';
            $objFileDeleted->success = false;
        }

        return $objFileDeleted;
    }

    public static function prepareDefaults(){
        self::$internalPath  = '../../';
        self::$externalPath  = '../../../';
    }

    public static function saveFile($pObjArchivoCarga)
    {
        if(isset($_FILES)){
            $imagenOrigen         = $pObjArchivoCarga->imagenOrigen;
            $imagenDocumento      = $pObjArchivoCarga->imagenDocumento;
            $imagenIdentificador  = $pObjArchivoCarga->imagenIdentificador;
            $temp_file_name       = $pObjArchivoCarga->temp_file_name;
            $original_file_name   = $pObjArchivoCarga->original_file_name;

            self::prepareDefaults();

            self::$pathDirectory = self::$internalPath.'recursos/imagen/';

            // Find file extention
            $ext = explode ('.', $original_file_name);
            $ext = $ext [count ($ext) - 1];

            // Remove the extention from the original file name
            $file_name = str_replace ($ext, '', $original_file_name);

            self::$pathDirectory = self::$pathDirectory . $imagenOrigen . '/' . $imagenDocumento . '/';

            if (!file_exists(self::$pathDirectory)) {
                mkdir(self::$pathDirectory, 0777, true);
            }

            $filecount = 0;
            $files = glob(self::$pathDirectory . "*$imagenIdentificador*.jpg");
            if ($files){
                $filecount = count($files);
            }
            $filecount = $filecount + 1;

            if($filecount < 10){
                $filecount = '0' . $filecount;
            }

            $newName     = 'imagen_' . $imagenIdentificador . '_' . $filecount ;
            $newFileName = self::$pathDirectory . $newName . '.'. $ext;

            if (move_uploaded_file ($temp_file_name, $newFileName)) {
                $availableInfo = true;
                $apiMessage = 'Cabinet: File uploded to Server';
            } else {
                $availableInfo = false;
                $apiMessage = 'Cabinet: Fail to upload to Server';
            }

            $newFileName = str_replace(self::$internalPath,self::$externalPath,$newFileName);

            $objImage = new \stdClass;
            $objImage->success  = $availableInfo;
            $objImage->message  = $apiMessage;
            $objImage->fileName = $newName;
            $objImage->file     = $newFileName;
            $objImage->numero   = $filecount;

            return $objImage;
        }
    }
}
