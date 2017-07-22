<?php echo (extension_loaded('openssl')?'SSL loaded':'SSL not loaded')."\n"; ?>

<?php
require_once('../recurso/clase/PHPMailer/class.phpmailer.php');
require_once('../recurso/clase/PHPMailer/class.smtp.php');

function enviaMail($paramCuerpoCorreo){

    // Creación de la instancia
    $mailWeb = new PHPMailer();

    // Seteo del uso
    $mailWeb->isSMTP(); // Uso SMTP
    // Host
    $mailWeb->Host = 'smtp.office365.com';
    // Puerto
    $mailWeb->Port = 587;

    // Degug. Valores 1 -> errores y mensajes // 2 solo mensajes // 0 no informa nada
    $mailWeb->SMTPDebug = 2;
    //Ask for HTML-friendly debug output
    $mailWeb->Debugoutput = 'html';

    // Seteo de la seguridad
    $mailWeb->SMTPSecure = 'tls';
    $mailWeb->SMTPAuth = true;

    //$mailWeb->AuthType = 'LOGIN';
    //$mailWeb->SMTPAutoTLS = true;

    // Autenticación
    $mailWeb->Username = "erick.rosales@@copamex.com";
    // Contraseña
    $mailWeb->Password = "Copamex03";

    $mailWeb->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );



    /*
    // Usuario
    $mailWeb->From = "erick.rosales@copamex.com";
    // Quien envia
    $mailWeb->SetFrom("erick.rosales@copamex.com",'ed');
    // A quien se responderá
    $mailWeb->AddReplyTo("erick.rosales@copamex.com",'ed');
    // Asunto del email
    */

    $mailWeb->Subject = "Informe de embarque";
    // En caso de que la vista HTML no esté activida. Esto ya es muy poco probable
    $mailWeb->AltBody = "Para ver correctamente este mensaje use la vista de HTML";
    // El cuerpo del mensaje.
    $mail = '<html><title></title><body>Este es el cuerpo del Correo</body></html>';

    $mailWeb->isHTML(true);
    $mailWeb->MsgHTML($mail);
    //$mailWeb->Body = $paramCuerpoCorreo;

    // Dirección del destinatario
    $mailWeb->AddAddress("erick.rosales@copamex.com");

    // Enviar el correo
    /*
    $mailWeb->Send();
    */


    if(!$mailWeb->Send())
        echo "<br />Mensaje no enviado!  <br />PHPMailer Error: <br /><font color=red>" . $mailWeb->ErrorInfo;
    else
        echo "Envio exitoso.";

}

$paramCuerpoCorreo='<div style="width: auto; height: auto; padding: 10px;border-top-style: solid;
					  border-right-style: solid;
					  border-bottom-style: solid;
					  border-left-style: solid; border-radius: 10px;">
					<h1 align="center">Remisionado</h1>
					<form> 
					<table align="center">
					<table align="center">
					<tr>
					<td>Mes</td>
					<td>
					<input style="margin-left: 9%; background-color: #F2F2F2; text-align: center; border-radius: 5px; border-width:0;" type="text" value=" Abril 2017" readonly>
					</td>
					</tr>
					<tr>
					<td><br>Meta<br>facturaci&oacute;n</td>
					<td>
					<br>
					<br>
					<input style="margin-left: 9%; background-color: #F2F2F2; text-align: center; border-radius: 5px; border-width:0;" type="text" value="'.number_format($dato['cantidad']).' tons" readonly>
					</td>
					</tr>
					<tr>
					<td><br>Acumulado<br>embarque</td>
					<td>
					<br>
					<br>
					<input style="margin-left: 9%; background-color: #F2F2F2; text-align: center; border-radius: 5px; border-width:0;" type="text" value="'.number_format($acum).' tons" readonly>
					</td>
					</tr>
					<tr>
					<td><br>% Avance</td>
					<td>
					<br>
					<input style="margin-left: 9%; background-color: #F2F2F2; text-align: center; border-radius: 5px; border-width:0;" type="text" value="'.round($prc).'%" readonly>
					</td>
					</tr>
					<tr>
					<td><br>Diario<br>embarcado</td>
					<td>
					<br>
					<br>
					<input style="margin-left: 9%;  background-color: #F2F2F2; text-align: center; border-radius: 5px; border-width:0;" type="text" value="'.$row['diario'].' tons" readonly>
					</td>
					</tr>
					<tr>
					<td colspan="2" align="center"><b><br>Ultimo embarque <br></b></td>
					</tr>
					<tr>
					<td><br># Cliente</td>
					<td>
					<br>
					<input style="margin-left: 9%; background-color: #F2F2F2; text-align: center; border-radius: 5px; border-width:0;" type="text" value="'.$row3['num'].'" readonly>
					</td>
					</tr>
					<tr>
					<td><br>Cliente</td>
					<td>
					<br>
					<input style="margin-left: 9%; background-color: #F2F2F2; text-align: center; border-radius: 5px; border-width:0;" type="text" value="'.$row3['cliente'].'" readonly>
					</td>
					</tr>
					
					</table>
					</form>
					<br>
					</div>';



echo $paramCuerpoCorreo;

$paramCuerpoCorreo = 'El Correo';

enviaMail($paramCuerpoCorreo);

?>