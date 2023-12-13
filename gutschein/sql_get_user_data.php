<?php
/**
 *    Autor        : Ren� Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
session_start();
if (isset($_SESSION['nummer']) && isset($_SESSION['code'])) define('SECURE', true);
//defined('SECURE') or die('Keine Zugriffsberechtigung!');
define('SECURE', true);
//require_once('includes/dbconfig.php');
define("L_LANG", "de_DE"); // Kalender-Sprache (DE)

if(isset($_POST['submit'])) {
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
        
    $anrede = $_POST['anrede'];
    $gsnummer = $_SESSION['nummer'];
    
    if(empty($error)) { // Daten sind korrekt und k�nnen gespeichert werden...
        
        $heute = mktime(0,0,0, date('m'), date('d'), date('Y'));
        $flag = 1;
        $flag2 = 2;
        
        $sql = "UPDATE gsv_gutschein
                SET empf_anrede=?,
                empf_nachname=?,
                empf_vorname=?,
                empf_strasse=?,
                empf_hausnummer=?,
                empf_plz=?,
                empf_ort=?,
                empf_land=?,
                empf_email=?,
                empf_telefon=?,
                wunsch_versand_datum=?,
                einloese_datum=?,
                ist_eingeloest=?,
                gsv_einloesungsort_einl_id=?
                WHERE gutschein_nummer=?";
        
        $kommando = $db->prepare($sql);
        $kommando->bind_param('ssssssssssssiii', $anrede, $name, $vorname, $strasse, $hnr, $plz, $ort, $land, $email, $telefon, $versand, $heute, $flag, $flag2, $gsnummer);
        $kommando->execute();
        
        $dbflag = $kommando->affected_rows;
        
        $db->close();
        
        if ($dbflag == 1) {
            echo '<br />Vielen Dank! Ihre Daten wurden erfolgreich &uuml;bermittelt.';
        } else {
            echo '<br />Leider ist ein Fehler bei der &Uuml;bertragung Ihrer Daten aufgetreten! Bitte versuchen Sie es erneut oder rufen Sie uns an.<br />Vielen Dank f&uuml;r Ihr Verst&auml;ndnis!';
        }
        
        // Session und Script beenden...
        session_destroy();
        exit;
    }
    
    echo '<br />Der Vorgang konnte nicht abgeschlossen werden.<br />' . $error . '<br /><br />';
}

?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso8859-1" />
<title>Pr&auml;sentgutscheine | Fiedlers Fischmarkt - feinste Meeresdelikatessen aus Bremerhaven</title>
<link rel="stylesheet" href="css/main.css" type="text/css" />
<link rel="stylesheet" href="css/form.css" type="text/css" />
<script language="javascript" src="calendar/calendar.js"></script>
</head>

<body>

<div id="message">
    Bitte geben Sie nun Ihre vollst&auml;ndigen Kontaktdaten ein und w&auml;hlen Sie einen Wunschversandtag f&uuml;r Ihr Pr&auml;sent aus.
    <br />Best&auml;tigen Sie anschliessend mit <i>Abschicken</i>, um die Gutscheineinl&ouml;sung abzuschliessen.    
</div>

<form method="post" autocomplete="off">
    <div id="container">
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
                    <td><input type="email" name="email" class="text_field" placeholder="mail@example.com" value="<?php if(isset($email)) echo $email; ?>" required="required" /></td>
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
                    $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                    $myCalendar->setDate(date("d"), date("m"), date("Y")); // der n�chstm�gliche Tag!
                    $myCalendar->setPath("calendar/");
                    $myCalendar->setYearInterval(date("Y"), 2015);
                    $myCalendar->dateAllow("2011-09-30", "2015-01-01"); // bis 10.00 gleicher Tag, dann n�chster Tag!
                    $myCalendar->disabledDay("Sun");
                    $myCalendar->disabledDay("Sat");
                    $myCalendar->disabledDay("Fri");
                    $myCalendar->showWeeks(true);
                    
                    // $myCalendar->setSpecificDate(array("2011-11-02"), 0, '');
                    
                    /*
                    for($a = 0; $a < count($sonder_tage); $a++) {
                        $myCalendar->setSpecificDate(array($sonder_tage[$a]), 0, '');
                    }
                    */
                    
                    $myCalendar->setAlignment("left", "bottom");
                    
                    //output the calendar
                    $myCalendar->writeScript();	  
                    ?></td>
                </tr>
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="2"><input type="submit" name="submit" class="submit_btn" value="Abschicken" /></td>
                </tr>
            </tfoot>
            
        </table>

</form>

</body>
</html>