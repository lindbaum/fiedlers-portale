<?php
/**
 *    Autor        : Ren� Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
define('SECURE', true);
require_once('includes/dbconfig.php');
session_start();

if (isset($_POST['nummer']) && trim($_POST['nummer']) != '' && isset($_POST['code']) && trim($_POST['code']) != '') {
    
    sleep(3); // Script verzögern...
    
    $error = preg_match('/^([0-9]{5,6})$/', $_POST['nummer']) ? '' : '<br />Bitte geben Sie eine korrekte Gutschein-Nr. ein.';
    $error .= preg_match('/^([A-Z0-9]{12})$/', $_POST['code']) ? '' : '<br />Bitte geben Sie einen korrekten Code ein.';
    
    $heute = mktime(0,0,0, date('m'), date('d'), date('Y'));
    
    if($error == '') {
        $nummer = $_POST['nummer'];
        $code = $_POST['code'];
		
		//$code = str_replace("O", "Q", $code);
		//$code = str_replace("0", "Q", $code);
        
        $sql = "SELECT end_datum, ist_eingeloest FROM gsv_gutschein WHERE gutschein_nummer=? AND gen_code=?";
        
        $kommando = $db->prepare($sql);
        $kommando->bind_param('is', $nummer, $code);
        $kommando->execute();
        $kommando->store_result();
        $kommando->bind_result($enddatum, $status);

        if($kommando->num_rows() == 1) {
            while($kommando->fetch()) {
                if($enddatum != '') { // Gutschein mit Begrenzung
                    if($heute <= $enddatum && $status == 0) { // Gutschein g�ltig... Weiterleiten
                        $_SESSION['nummer'] = $nummer;
                        $_SESSION['code'] = $code;
                        header('Location: get_user_data.php');
                        exit;
                    } else { // Gutschein abgelaufen oder bereits eingel�st!
                        $message = '<br />Der von Ihnen eingegebene Gutschein ist nicht mehr g&uuml;ltig!';
                    }
                } else { // Gutschein ohne Begrenzung
                    if($status == 0) { // Gutschein noch nicht eingel�st... Weiterleiten
                        $_SESSION['nummer'] = $nummer;
                        $_SESSION['code'] = $code;
                        header('Location: get_user_data.php');
                        exit;
                    } else { // Gutschein wurde bereits eingel�st
                        $message = '<br />Der von Ihnen eingegebene Gutschein ist nicht mehr g&uuml;ltig!';
                    }
                }
            }
        } else { // Gutschein-Kombination nicht gefunden!
            $message = '<br /><br />Ihre Eingaben konnten nicht &uuml;berpr&uuml;ft werden.<br /><br />Haben Sie die Groß -Kleinschreibung beachtet? Haben Sie versehentlich ein Leerzeichen vor oder nach dem Code eingetippt?<br />' . $error;
        }
    } else {
        $message = '<br /><br />Ihre Eingaben konnten nicht &uuml;berpr&uuml;ft werden.<br /><br />Haben Sie die Groß -Kleinschreibung beachtet? Haben Sie versehentlich ein Leerzeichen vor oder nach dem Code eingetippt?<br />' . $error;
    }
    
    // Datenbank schliessen
    $db->close();    
}

?>
<!DOCTYPE HTML>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Fiedler Meeresdelikatessen | Pr&auml;sentgutscheine</title>
<link rel="stylesheet" href="css/main.css" type="text/css" />
<link rel="stylesheet" href="css/form.css" type="text/css" />
<style type="text/css">
a:link {
	color: #FC0D1B;
}
a:visited {
	color: #F91628;
}
a:hover {
	color: #E06564;
}
</style>
<noscript><meta http-equiv="refresh" content="0; URL=noscript.html" /></noscript>

<script type="text/javascript">
function activate() {
  
  with (document.forms['gutschein'])
	{
		elements['submit'].value = 'Bitte warten!';
		elements['submit'].disabled = true;
		submit();
	}

}
</script>

</head>

<body>

<div id="container">
    <table width="360" border="0" cellspacing="6">
        <tr>
            <td class="headline">Sie m&ouml;chten einen Gutschein einl&ouml;sen?</td>
        </tr>
        <tr>
            <td class="fliesstext"><p>Machen Sie bitte Ihr Rubbelfeld frei und folgen Sie den Anweisungen.<br/>Achtung:
                    Sobald das Rubbelfeld freigemacht wurde, entf&auml;llt die M&ouml;glichkeit den Gutschein per Post
                    oder pers&ouml;nlich einzul&ouml;sen. Ihr Gutschein ist nur noch online einl&ouml;sbar.</p>
                <p>&nbsp;</p>
<?php
/*
            <p><strong><font color="#d60000">Liebe Kunden,

leider war die letzte Produktion unserer Gutscheine fehlerhaft, daher lassen sich leider nicht alle Rubbelfelder problemlos freimachen. </font></strong></p>
            <p><strong><font color="#d60000"><br>
            </font></strong><font color="#d60000">Sollten Sie einen fehlerhaften Gutschein erhalten haben und ihren Code nicht lesen können, dann teilen Sie uns bitte Ihre Gutscheinnummer, sowie das was vom Code erkennbar ist per Mail mit oder rufen Sie uns an, wir teilen Ihnen dann den richtigen Code mit.<strong><br>
  <br>
            <a href="mailto:info@fiedlers-fischmarkt.de">info@fiedlers-fischmarkt.de</a><br>
            0471 9 32 23 - 0<br>
  <br>
            </strong>Wir hoffen, dass Sie uns diesen Fehler verzeihen und trotzdem viel Freude an Ihrem Präsent haben werden.<br>
            <br>
Ihr Team von Fiedlers Fischmarkt anno 1906</font></p>
*/
?>

</td>
        </tr>
            
            <?php
            if(isset($message)) { 
                printf('<tr><td class="fliesstextBoldRed">%s</td></tr>', $message);
            }
            ?>
        
        <tr>
            <td>&nbsp;</td>
        </tr>
    </table>
        
    <form name="gutschein" method="post" onsubmit="activate();" autocomplete="off">
        <table class="tbl_1">
            <tbody>
                <tr>
                    <th scope="col">Gutschein-Nr.:</th>
                    <td><input type="text" name="nummer" placeholder="Gutscheinnummer" required class="text_field" maxlength="10" /></td>
                </tr>
                <tr>
                    <th scope="col">Code:</th>
                    <td><input type="text" name="code" placeholder="Code" required class="text_field" maxlength="15" /></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="submit" value="Einl&ouml;sen" class="submit_btn" /></td>
                </tr>
            </tfoot>
        </table>
    </form>
</div>

</body>
</html>