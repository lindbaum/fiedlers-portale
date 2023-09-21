<?php
/**
 *    Autor        : RenŽ Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
session_start();
if (isset($_SESSION['nummer']) && isset($_SESSION['code'])) define(SECURE, true);
require_once('includes/dbconfig.php');
define("L_LANG", "de_DE"); // Kalender-Sprache (DE)

if(isset($_POST['issent'])) {
    $error='';
    (isset($_POST['vorname']) && trim($_POST['vorname']) != '') ? $vorname = trim(htmlspecialchars($_POST['vorname'])) : $error .= '<br />Bitte einen Vornamen angeben.';
    (isset($_POST['name']) && trim($_POST['name']) != '') ? $name = trim(htmlspecialchars($_POST['name'])) : $error .= '<br />Bitte einen Namen angeben.';
    (isset($_POST['strasse']) && trim($_POST['strasse']) != '') ? $strasse = trim(htmlspecialchars($_POST['strasse'])) : $error .= '<br />Bitte eine Strasse angeben.';
    (isset($_POST['hnr']) && trim($_POST['hnr']) != '') ? $hnr = trim(htmlspecialchars($_POST['hnr'])) : $error .= '<br />Bitte eine Hausnummer angeben.';
    (isset($_POST['plz']) && trim($_POST['plz']) != '') ? $plz = trim(htmlspecialchars($_POST['plz'])) : $error .= '<br />Bitte eine Postleitzahl angeben.';
    (isset($_POST['ort']) && trim($_POST['ort']) != '') ? $ort = trim(htmlspecialchars($_POST['ort'])) : $error .= '<br />Bitte einen Ort angeben.';
    (isset($_POST['land']) && trim($_POST['land']) != '') ? $land = trim(htmlspecialchars($_POST['land'])) : $error .= '<br />Bitte ein Land angeben.';
    (isset($_POST['email']) && trim($_POST['email']) != '') ? $email = trim(htmlspecialchars($_POST['email'])) : $error .= '<br />Bitte eine Email angeben.';
    (isset($_POST['telefon']) && trim($_POST['telefon']) != '') ? $telefon = trim(htmlspecialchars($_POST['telefon'])) : $error .= '<br />Bitte ein Telefon angeben.';
    (isset($_POST['date3']) && trim($_POST['date3']) != '' && $_POST['date3'] != '0000-00-00') ? $versand = strtotime($_POST['date3']) : $error .= '<br />Bitte ein Versanddatum angeben.';
    (isset($_POST['firma']) && trim($_POST['firma']) != '') ? $firma = trim(htmlspecialchars($_POST['firma'])) : $firma = '';
        
    $anrede = $_POST['anrede'];
    $gsnummer = $_SESSION['nummer'];
    
    if(empty($error)) { // Daten sind korrekt und kšnnen gespeichert werden...
        
        $heute = mktime(0,0,0, date('m'), date('d'), date('Y'));
        
        $sql = "UPDATE gsv_gutschein
                SET empf_anrede=?,
                empf_nachname=?,
                empf_vorname=?,
		empf_firma=?,
                empf_strasse=?,
                empf_hausnummer=?,
                empf_plz=?,
                empf_ort=?,
                empf_land=?,
                empf_email=?,
                empf_telefon=?,
                wunsch_versand_datum=?,
                einloese_datum=?,
                ist_eingeloest=1,
                gsv_einloesungsort_einl_id=2
                WHERE gutschein_nummer=?";
        
        $kommando = $db->prepare($sql);
        $kommando->bind_param('sssssssssssssi', $anrede, $name, $vorname, $firma, $strasse, $hnr, $plz, $ort, $land, $email, $telefon, $versand, $heute, $gsnummer);
        $kommando->execute();
        
        $dbrows = $kommando->affected_rows;
        
        $db->close();
       
        if ($dbrows == 1) {
            include("success.php");
        } else {
            include("error.php");
        }
        
        // Session und Script beenden...
        session_destroy();
        exit;
    }
}

?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Fiedler Meeresdelikatessen | Pr&auml;sentgutscheine</title>
<link rel="stylesheet" href="css/main.css" type="text/css" />
<link rel="stylesheet" href="css/form.css" type="text/css" />
<script language="javascript" src="calendar/calendar.js"></script>

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

<form name="gutschein" method="post" onsubmit="activate();" autocomplete="off">
    <div id="container">
        <table width="360" border="0" cellspacing="6" cellpadding="0">
            <tr>
                <td class="headline">&nbsp;</td>
            </tr>
            <tr>
                <td class="headline">Ihre Kontaktdaten</td>
            </tr>
            <tr>
                <td class="fliesstext">Bitte geben Sie nun Ihre vollst&auml;ndigen Kontaktdaten ein und w&auml;hlen Sie einen Wunschversandtag f&uuml;r Ihr Pr&auml;sent aus.
                <br />Best&auml;tigen Sie anschliessend mit <i>Abschicken</i>, um die Gutscheineinl&ouml;sung abzuschliessen.</td>
            </tr>
            
            <?php
            if(isset($error)) { 
                //print('<tr><td>&nbsp;</td></tr>');
                printf('<tr><td class="fliesstextBoldRed">%s</td></tr>', $error);
            }
            ?>
            
            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
            
        <table class="tbl_1">
            <tbody>
                <tr>
                    <th scope="col">Anrede</th>
                    <td>
                        <select name="anrede" class="select_field">
                            <option value="Herr" <? if(isset($anrede) && $anrede == 'Herr') echo 'selected'; ?>>Herr</option>
                            <option value="Frau" <? if(isset($anrede) && $anrede == 'Frau') echo 'selected'; ?>>Frau</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="col">Vorname</th>
                    <td><input type="text" name="vorname" class="text_field" placeholder="Vorname" value="<?php if(isset($vorname)) echo $vorname; ?>" required="required" /></td>
                </tr>
                 
                <tr>
                    <th scope="col">Name</th>
                    <td><input type="text" name="name" class="text_field" placeholder="Name" value="<?php if(isset($name)) echo $name; ?>" required="required" /></td>
                </tr>
		
		<tr>
                    <th scope="col">Firma <span style="font-size:6pt;">(optional)</span></th>
                    <td><input type="text" name="firma" class="text_field" placeholder="Firma" value="<?php if(isset($firma)) echo $firma; ?>" /></td>
                </tr>
                  
                <tr>
                    <th scope="col">Strasse</th>
                    <td><input type="text" name="strasse" class="text_field" placeholder="Strasse" value="<?php if(isset($strasse)) echo $strasse; ?>" required="required" /></td>
                </tr>
                
                <tr>
                    <th scope="col">Nr.</th>
                    <td><input type="text" name="hnr" class="text_field" placeholder="Hausnummer" value="<?php if(isset($hnr)) echo $hnr; ?>" required="required" /></td>
                </tr>
                
                <tr>
                    <th scope="col">PLZ</th>
                    <td><input type="text" name="plz" class="text_field" placeholder="PLZ" value="<?php if(isset($plz)) echo $plz; ?>" required="required" /></td>
                </tr>
                
                <tr>
                    <th scope="col">Ort</th>
                    <td><input type="text" name="ort" class="text_field" placeholder="Ort" value="<?php if(isset($ort)) echo $ort; ?>" required="required" /></td>
                </tr>
                
                <tr>
                    <th scope="col">Land</th>
                    <td><input type="text" name="land" class="text_field" placeholder="Land" value="<?php if(isset($land)) echo $land; else echo 'Deutschland'; ?>" required="required" /></td>
                </tr>
                
                <tr>
                    <th scope="col">Email</th>
                    <td><input type="email" name="email" class="text_field" placeholder="Email" value="<?php if(isset($email)) echo $email; ?>" required="required" /></td>
                </tr>
                
                <tr>
                    <th scope="col">Telefon</th>
                    <td><input type="text" name="telefon" class="text_field" placeholder="Telefon" value="<?php if(isset($telefon)) echo $telefon; ?>" required="required" /></td>
                </tr>
                
                <tr>
                    <th scope="col">Versanddatum</th>
                    <td>
                    <?php
                    //get class into the page
                    require_once("calendar/tc_calendar.php");
                    
                    //instantiate class and set properties
                    $myCalendar = new tc_calendar("date3", true, false);
                    $myCalendar->setIcon("calendar/images/iconCalendar.png");
                    $myCalendar->setPath("calendar/");
                    
                    if(!empty($versand)) $myCalendar->setDate(date("d", $versand), date("m", $versand), date("Y", $versand));
                    
                    // Jahresintervall setzen
                    $start_jahr = date('Y');
		    $end_jahr = $start_jahr + 1;
		    
                    $myCalendar->setYearInterval($start_jahr, $end_jahr);
                    
                    // Zeitspanne setzen
                    // bis 7:00 buchbar am gleichen Tag, dann ab Folgetag!
                    (date('H',time()) < 7) ? $start_datum = mktime(0,0,0,date('m'),date('d'),date('Y')) : $start_datum = mktime(0,0,0,date('m'),date('d'),date('Y')) + (60 * 60 * 24);
		    $end_datum = mktime(0,0,0,date('m'),date('d'),date('Y', strtotime('+ 1 year'))); // 1 Jahr addieren
                    $start_datum = date('Y-m-d', $start_datum);
                    $end_datum = date('Y-m-d', $end_datum);
                    
                    $myCalendar->dateAllow($start_datum, $end_datum);
                    
                    // Standardversandtage ermitteln und Tage deaktivieren
                    $sql = "SELECT * FROM gsv_standard_versandtage ORDER BY wochentag ASC";
                    $kommando = $db->prepare($sql);
                    $kommando->execute();
                    $kommando->store_result();
                    $kommando->bind_result($wert1, $wert2);
                    
                    $werktage = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
                    while($kommando->fetch()) {
                        if($wert2 == 0) {
                            $myCalendar->disabledDay($werktage[$wert1]);
                        }
                    }
                    
                    // Sondertage sperren
                    $sql = "SELECT * FROM gsv_sonder_versandtage ORDER BY sonder_datum ASC";
                    $kommando = $db->prepare($sql);
                    $kommando->execute();
                    $kommando->store_result();
                    $kommando->bind_result($wert1, $wert2);
                    
                    while($kommando->fetch()) {
                        if($wert2 == 0) {
                            $myCalendar->setSpecificDate(array(date('Y-m-d', $wert1)), 0, '');
                        }
                    }
                    
                    $myCalendar->setAlignment("left", "bottom");
                    
                    //output the calendar
                    $myCalendar->writeScript();	  
                    ?>
                    </td>
                </tr>
		
		<tr>
                    <th scope="col" valign="top">Hinweis</th>
                    <td>Auslandsversand nur von Mo. - Mi.<br />Sollte ein anderer Tag ausgew&auml;hlt werden, wird der Versand von uns automatisch auf den n&auml;chsten m&ouml;glichen Versandtag ge&auml;ndert.</td>
                </tr>
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="2"><input type="submit" name="submit" class="submit_btn" value="Abschicken" />
                    <input type="hidden" id="issent" name="issent" value="1" /></td>
                </tr>
            </tfoot>
            
        </table>
    </div>

</form>

</body>
</html>