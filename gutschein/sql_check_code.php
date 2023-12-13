<?php
/**
 *    Autor        : Ren� Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
define('SECURE', true);
//require_once('includes/dbconfig.php');
session_start();

if (isset($_POST['nummer']) && trim($_POST['nummer']) != '' && isset($_POST['code']) && trim($_POST['code']) != '') {
    
    sleep(4); // Script verz�gern...
    
    $error = preg_match('/^([0-9]{5,6})$/', $_POST['nummer']) ? '' : '<br />Bitte geben Sie eine korrekte Gutscheinnummer ein.';
    $error .= preg_match('/^([A-Z0-9]{12})$/', $_POST['code']) ? '' : '<br />Bitte geben Sie einen korrekten Code ein.';
    
    $heute = mktime(0,0,0, date('m'), date('d'), date('Y'));
    
    if($error == '') { // Gutschein auf G�ltigkeit pr�fen
        
        $nummer = $_POST['nummer'];
        $code = $_POST['code'];
        
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
            $message = '<br />Ihre Eingaben konnten nicht &uuml;berpr&uuml;ft werden.' . $error;
        }
    } else {
        $message = '<br />Ihre Eingaben konnten nicht &uuml;berpr&uuml;ft werden.' . $error;
    }
    
    // Datenbank schliessen
    $db->close();    
}

?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" />
<title>Pr&auml;sentgutscheine | Fiedlers Fischmarkt - feinste Meeresdelikatessen aus Bremerhaven</title>
<link rel="stylesheet" href="css/main.css" type="text/css" />
<link rel="stylesheet" href="css/form.css" type="text/css" />
</head>

<body>

<div id="container">
    <table width="360" border="0" cellspacing="6">
          <tr>
            <td class="headline">&nbsp;</td>
          </tr>
          <tr>
            <td class="headline">Sie m&ouml;chten einen Gutschein einl&ouml;sen?</td>
          </tr>
          <tr>
            <td class="fliesstext">Lorem ipsum dolor sit amet, consectetuer adipiscing elit.  Aenean  commodo ligula eget dolor.  Aenean massa.  Cum sociis natoque penatibus  et magnis dis parturient montes, nascetur ridiculus mus.  Donec quam  felis, ultricies nec, pellentesque eu, pretium quis, sem.  Nulla  consequat massa quis enim.</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table>
        
    <form method="post" autocomplete="off">
        <table class="tbl_1">
            <tbody>
                <tr>
                  <th scope="col">Gutschein-Nr.:</th>
                  <td><input type="number" name="nummer" min="5" max="5" step="1" placeholder="Gutscheinnummer" required class="text_field" maxlength="10" /></td>
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

<div id="message">
    <span><?php if(isset($message)) print $message; ?></span>
</div>

</body>
</html>