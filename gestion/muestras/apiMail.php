<?php
require '../recurso/clase/PHPMailer/PHPMailerAutoload.php';

echo "<div style ='font:11px/21px Courier;color:#ffd900;background-color: #0a2b1d'>";
$mail = new PHPMailer(false);
$mail->isSMTP();
//$mail->Host = 'host26.321hospedando.info';
$mail->Host       = '127.0.0.1';
$mail->Port       = 465;
$mail->SMTPSecure = 'ssl';
$mail->SMTPAuth   = true;
//$mail->Username   = 'cotizacion@hwtusados.com';
//$mail->Password   = 'HWT$C0t1Z4Ci0n';
//$mail->SetFrom('cotizacion@hwtusados.com', 'Cotizacion HWT Usados');

$mail->Username   = 'cotizacion@terian.com.mx';
$mail->Password   = 'C0t1z4CI0N$83';
$mail->SetFrom('cotizacion@terian.com.mx', 'Cotizacion HWT Usados');

$mail->addAddress('erick.rosales@outlook.com','Destinatario');

$mail->SMTPDebug  = 2;
$mail->Debugoutput = function($str, $level) {echo "</br><b>Debug level $level</b>; '::' message: $str";}; //$mail->Debugoutput = 'echo';
$mail->IsHTML(true);

$mail->Subject = 'Here is the subject';
$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

if(!$mail->send()) {
    echo '</br><font color=red>Message could not be sent, please check the configuration.</br>';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
    echo '</font>';
} else {
    echo '</br><font color=#90ee90><b>Message has been sent succesfully!!!</b></font>';
}

echo "</div>";