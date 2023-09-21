<?php
if ($anrede == 'Herr') $mail_anrede = 'Sehr geehrter Herr ' . $name;
else $mail_anrede = 'Sehr geehrte Frau ' . $name;

$subject = "Ihr Gutschein . Fiedler's Fischmarkt";

$mail_text = "<span style='font-family:arial,verdana,times;font-size:13px;'>".$mail_anrede.",<br><br>Sie haben Ihren Gutschein auf www.fiedlers-fischmarkt.de erfolgreich eingel&ouml;st.<br><br>Vielen Dank!</span><br><br>";
$mail_text .= "<span style='color:#999999;font-size:11px;'><br><br>
H.-J. Fiedler Meeresdelikatessen GmbH<br><br>
Fiedler's Fischmarkt anno 1906<br><br>
An der Packhalle IV Nr. 34<br>
27572 Bremerhaven<br>
Telefon: 04 71/9 32 23-0<br>
Telefax: 04 71/9 32 23-32<br>
e-mail: info@fiedlers-fischmarkt.de<br>
www.fiedlers-fischmarkt.de<br>
www.fiedlers-fischerdorf.de<br><br>
Gesch&auml;ftsf&uuml;hrer: Hans-Joachim Fiedler<br>
HRG Bremerhaven 2116<br>
USt-Nr.: DE 114 706230<br>
EG-Nr.: DE-HB-00108<br></span>";

$headers   = array();
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-type: text/html; charset=utf-8";
$headers[] = "From: Fiedlers Fischmarkt <info@fiedlers-fischmarkt.de>";
$headers[] = "X-Mailer: PHP/".phpversion();

@mail($email, $subject, $mail_text, implode("\r\n", $headers));
?>