<?php
require_once('../recurso/clase/PHPMailer/PHPMailerAutoload.php');
require_once('../recurso/clase/Logger.php');
setlocale(LC_TIME, 'es_MX','esp').': ';
class Mailer
{
    private static $mail;
    private static $mailTemplate;

    private static $CR         = '</br>';

    private static $TableTotalsStart = "<table class='tablaTotales'>";
    private static $TableTotalsEnd   = "</table>";
    private static $TableStart = "<table class='tabla'>";
    private static $TableEnd   = '</table>';
    private static $RowHalfStart   = "<tr class='filaMitad'>";
    private static $RowStart   = '<tr>';
    private static $RowEnd     = '</tr>';
    private static $CellStart  = "<td class='celdaTabla''>";
    private static $CellStartMoney  = "<td class='celdaTablaNumero'>";
    private static $CellEnd    = '</td>';
    private static $CellTitleStart  = "<td class='celdaTituloTabla'>";
    private static $CellTitleEnd    = '</td>';

    public static function attachFile($pPathFile,$pNameFile){
        Logger::enable(true,'attachFile');
        self::$mail->AddAttachment($pPathFile,$pNameFile);
        Logger::write('Archivo atachaedo ' . $pPathFile);
    }

    public static function prepareConection(){
        self::$mail = new PHPMailer(false);
        self::$mail->isSMTP();
        self::$mail->Host       = 'host26.321hospedando.info';
        self::$mail->Port       = 465;
        self::$mail->SMTPSecure = 'ssl';
        self::$mail->SMTPAuth   = true;
        self::$mail->Username   = 'cotizacion@hwtusados.com';
        self::$mail->Password   = 'HWT$C0t1Z4Ci0n';
        self::$mail->CharSet    = 'UTF-8';
        self::$mail->IsHTML(true);
        self::$mail->SetFrom('cotizacion@hwtusados.com', 'Cotizacion HWT Usados');
    }

    public static function generateTable($pArrayHeader,$pObjData,$pTypeTable){

        $table = '';
        switch($pTypeTable){
            case 'tabular':
                $table = self::$TableStart
                    . self::$RowStart;

                // ECRC: Preparing the Table Header
                foreach($pArrayHeader as $header){
                    $table = $table
                        . self::$CellTitleStart
                        . $header
                        . self::$CellTitleEnd;
                }

                $table = $table
                    . self::$RowEnd
                ;

                // ECRC: Preparing the Table Content
                foreach($pObjData as $recordData){
                    $table = $table
                        . self::$RowStart;

                    foreach($recordData as $field=>$value){

                        $posMoneySymbol = strpos($value, '$');

                        $cell = self::$CellStartMoney;
                        if($posMoneySymbol !== 0){
                            $cell = self::$CellStart;
                        }

                        $table = $table
                            . $cell
                            . $value
                            . self::$CellEnd;
                    }
                    $table = $table
                        . self::$RowEnd;
                }

                $table = $table
                    . self::$TableEnd;

                break; // tabular
            case 'list':
                $table = self::$TableTotalsStart;

                // ECRC: Preparing the Table Content
                foreach($pObjData as $recordData){
                    foreach($recordData as $field=>$value){
                        $table = $table
                            . self::$RowHalfStart;

                        // ECRC: Row Title
                        $table = $table
                               . self::$CellTitleStart
                               . $field
                               . self::$CellTitleEnd;

                        $posMoneySymbol = strpos($value, '$');

                        $cell = self::$CellStartMoney;
                        if($posMoneySymbol !== 0){
                            $cell = self::$CellStart;
                        }

                        $table = $table
                            . $cell
                            . $value
                            . self::$CellEnd;

                        $table = $table
                            . self::$RowEnd;
                    }
                }

                $table = $table
                    . self::$TableTotalsEnd;

                break; // list
        }


        return $table;
    } // generateTable

    public static function sendMail($pObjMail){
        Logger::enable(true,'sendMail');

        // ECRC: Preparando los Destinatarios
        Logger::write('Preparando los Destinatarios');
        foreach($pObjMail->destinatarios as $destinatario){
            Logger::write(json_encode($destinatario));

            self::$mail->addAddress($destinatario->email,$destinatario->nombre);
        }

        foreach($pObjMail->destinatariosCCO as $destinatario){
            Logger::write(json_encode($destinatario));

            self::$mail->addBCC($destinatario->email,$destinatario->nombre);
        }

        Logger::write('Antes de Prepara la Plantilla');
        self::$mail->Subject = $pObjMail->subject;
        self::$mail->Body = self::prepareTemplate($pObjMail);

        Logger::write('Despues de reparar la Plantilla');
        Logger::write(self::$mail->Body);

        $objMailResponse = new \stdClass();
        $objMailResponse->subject = $pObjMail->subject;


        Logger::write('Realizando el Envio');
        if(!self::$mail->send()) {
            $objMailResponse->success = false;
            $objMailResponse->response = 'Message could not be sent in a good way.' .
                                         'Mailer Error: ' . self::$mail->ErrorInfo;
        } else {
            $objMailResponse->success = true;
            $objMailResponse->response = 'Message has been sent!!!';
        }

        Logger::write('Finalizo el envio');
        return $objMailResponse;
    }

    public static function prepareTemplate($pObjMail){

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // WINDOWS
            $file = '../recurso/template/mailerTemplate.html';
        } else {
            // LINUX
            Logger::write('dirname(__FILE__).');
            Logger::write(dirname(__FILE__));
            $pathFile = str_replace('clase','template',dirname(__FILE__));
            $file = $pathFile . '/mailerTemplate.html';

            Logger::write($file);
            Logger::write(file_exists($file));
        }

        $dts = new DateTime();
        $fechaHora = strftime("%A",$dts->getTimestamp()) . ', '
            . strftime("%d",$dts->getTimestamp()) . ' de '
            . strftime("%B",$dts->getTimestamp()) . ' del '
            . strftime("%Y",$dts->getTimestamp()) . ' '
            . '[' . date('H:i:s') . ']';

        $notasAdicionales = 'Este correo es generado automáticamente, al responder a éste correo un Ejecutivo se pondrá en contacto con Usted.';

        $hookFields = array(
            empresa          => $pObjMail->empresa,
            logotipo         => $pObjMail->logotipo,
            tituloCorreo     => utf8_encode($pObjMail->tituloCorreo),
            remitente        => $pObjMail->sistema,
            fechaHora        => $fechaHora,
            cuerpoHTML       => utf8_encode($pObjMail->body),
            notasAdicionales => utf8_encode($notasAdicionales),
            piePagina        => $pObjMail->sistema
        );

        $keys = array();
        $data = array();
        foreach($hookFields as $key => $value){
            array_push($keys, '[$'. $key .']');
            array_push($data,  $value );
        }

        $template = file_get_contents($file);
        $template = str_replace($keys, $data, $template);

        return $template;
    }

}
