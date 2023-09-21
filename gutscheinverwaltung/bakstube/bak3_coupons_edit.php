<?php
/**
 *    Autor        : René Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
require_once('includes/auth.php'); // incl. session_start();
if($_SESSION['user_level'] >= 3) define('SECURE', true);
require_once('includes/dbconfig.php');
define("L_LANG", "de_DE"); // Kalender-Sprache (DE)

// Funktions-Bibliothek
require_once('functions/main_functions.php');

// Paramterübergabe
if(isset($_GET['edit']) && preg_match('/^[0-9]{5}$/', $_GET['edit'])) $edit = $_GET['edit'];
if(isset($_GET['redeem']) && preg_match('/^[0-9]{1}$/', $_GET['redeem'])) $redeem = $_GET['redeem'];

// Formularauswertung
$updated = false;
if(isset($_POST['submit'])) {
    $error = '';
    (isset($_POST['gutscheinnummer'])) ? $edit = $_POST['gutscheinnummer'] : $error .= '<br />Keine Gutscheinnummer angegeben! Dies ist ein interner Fehler, wenden Sie sich an Ihren Administrator.';
    (isset($_POST['rechnungsnummer']) && trim($_POST['rechnungsnummer']) != '') ? $rechnungsnummer = trim(htmlspecialchars($_POST['rechnungsnummer'])) : $error .= '<br />Bitte geben Sie eine Rechnungsnummer an.';
    (isset($_POST['praesent']) && $_POST['praesent'] != '1') ? $praesent = $_POST['praesent'] : $error .= '<br />Bitte w&auml;hlen Sie ein Pr&auml;sent aus.';
    if(isset($_POST['verschenker'])) $verschenker = $_POST['verschenker'];
    if(isset($_POST['berechnung'])) $berechnung = $_POST['berechnung'];
    (isset($_POST['date1']) && trim($_POST['date1']) != '' && $_POST['date1'] != '0000-00-00') ? $befristung = strtotime($_POST['date1']) : $befristung = '';
    
    if(isset($redeem) && $redeem == 1) {
        (isset($_POST['vorname']) && trim($_POST['vorname']) != '') ? $vorname = trim(htmlspecialchars($_POST['vorname'])) : $error .= '<br />Bitte einen Vornamen angeben.';
        (isset($_POST['name']) && trim($_POST['name']) != '') ? $name = trim(htmlspecialchars($_POST['name'])) : $error .= '<br />Bitte einen Namen angeben.';
        (isset($_POST['strasse']) && trim($_POST['strasse']) != '') ? $strasse = trim(htmlspecialchars($_POST['strasse'])) : $error .= '<br />Bitte eine Strasse angeben.';
        (isset($_POST['hnr']) && trim($_POST['hnr']) != '') ? $hnr = trim(htmlspecialchars($_POST['hnr'])) : $error .= '<br />Bitte eine Hausnummer angeben.';
        (isset($_POST['plz']) && trim($_POST['plz']) != '') ? $plz = trim(htmlspecialchars($_POST['plz'])) : $error .= '<br />Bitte eine Postleitzahl angeben.';
        (isset($_POST['ort']) && trim($_POST['ort']) != '') ? $ort = trim(htmlspecialchars($_POST['ort'])) : $error .= '<br />Bitte einen Ort angeben.';
        (isset($_POST['land']) && trim($_POST['land']) != '') ? $land = trim(htmlspecialchars($_POST['land'])) : $error .= '<br />Bitte ein Land angeben.';
        (isset($_POST['email']) && trim($_POST['email']) != '') ? $email = trim(htmlspecialchars($_POST['email'])) : $error .= '<br />Bitte eine Email angeben.';
        (isset($_POST['telefon']) && trim($_POST['telefon']) != '') ? $telefon = trim(htmlspecialchars($_POST['telefon'])) : $error .= '<br />Bitte ein Telefon angeben.';
        if(isset($_POST['date2']) && trim($_POST['date2']) != '' && $_POST['date2'] != '0000-00-00') {
            $versand = strtotime($_POST['date2']);
            $versand = mktime(0,0,0,date('m',$versand), date('d',$versand), date('Y',$versand));
        } else {
            $error .= '<br />Bitte ein Versanddatum angeben.';
        }
            
        $anrede = $_POST['anrede'];
        $firma = trim(htmlspecialchars($_POST['firma']));
        $einloesungsort = $_POST['einloesungsort'];
    } 
    
    if(empty($error)) {
        
        if(isset($redeem) && $redeem == 0) {
            $sql = "UPDATE gsv_gutschein
                    SET end_datum=?,
                    rechnungs_nummer=?,
                    gsv_berechnungsart_bere_id=?,
                    gsv_praesent_prae_id=?,
                    gsv_verschenker_vers_id=? 
                    WHERE gutschein_nummer=?";
    
            $kommando = $db->prepare($sql);
            $kommando->bind_param('ssiiii', $befristung, $rechnungsnummer, $berechnung, $praesent, $verschenker, $edit);
            $kommando->execute();
            
            $updated = true;
            
            if($kommando->affected_rows == 1) $success = '<img src="images/tick.png" align="top" />&nbsp;Datensatz wurde erfolgreich gespeichert.';
            if($kommando->error) $error = '<img src="images/cross.png" align="top" />&nbsp;Beim Speichern der Daten ist ein Fehler aufgetreten!';
        }
        
        elseif(isset($redeem) && $redeem == 1) {
    
            $sql = "UPDATE gsv_gutschein
                    SET end_datum=?,
                    rechnungs_nummer=?,
                    gsv_berechnungsart_bere_id=?,
                    gsv_praesent_prae_id=?,
                    gsv_verschenker_vers_id=?,
                    empf_anrede=?,
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
                    gsv_einloesungsort_einl_id=?
                    WHERE gutschein_nummer=?";
            
            $kommando = $db->prepare($sql);
            $kommando->bind_param('ssiiissssssssssssii', $befristung, $rechnungsnummer, $berechnung, $praesent, $verschenker, $anrede, $name, $vorname,
                                  $firma, $strasse, $hnr, $plz, $ort, $land, $email, $telefon, $versand, $einloesungsort, $edit);
            $kommando->execute();
            
            $updated = true;
            
            if($kommando->affected_rows == 1) $success = '<img src="images/tick.png" align="top" />&nbsp;Datensatz wurde erfolgreich gespeichert.';
            if($kommando->error) $error = '<img src="images/cross.png" align="top" />&nbsp;Beim Speichern der Daten ist ein Fehler aufgetreten!';
        }
    }
}

// Dokumentkopf setzen
setDocumentHead();

$heute = mktime(0,0,0, date('m'), date('d'), date('Y'));
$heute = date('Y-m-d', $heute);

?>

<div id="wrap">

    <div id="header">
        <?php include('includes/status.php'); ?>
    </div>
    
    <div id="main">
        <div id="sidebar">
            <?php include('includes/menu.php'); ?>
        </div>
        <div id="content">
            
            <div class="contentBox">
                <h2>Gutscheine - Editieren</h2>
            </div>
            
            <?php
            
            if(isset($error) && !empty($error)) { 
                printf('<div class="contentBoxRed">%s</div>', $error);
            }
            
            if(isset($success) && !empty($success)) { 
                printf('<div class="contentBox">%s</div>', $success);
                print('<div class="contentBox"><a href="coupons.php">Zur&uuml;ck</a></div>');
            }
            
            if(isset($edit) && $updated === false) {
                
                $fehler = false;
                
                $sql = "SELECT * FROM gsv_gutschein WHERE gutschein_nummer = ?";
                
                $kommando = $db->prepare($sql);
                $kommando->bind_param('i', $edit);
                $kommando->execute();
                $kommando->store_result();
                $kommando->bind_result($gutscheinnummer, $code, $befristung, $rechnungsnummer,
                                       $einloesedatum, $ist_eingeloest, $ist_abgelaufen, $anrede,
                                       $name, $vorname, $firma, $strasse, $hnr, $plz, $ort, $land,
                                       $email, $telefon, $versanddatum, $berechnung, $praesent,
                                       $verschenker, $einloesungsort, $ausgabedatum, $ist_ausgegeben);
                
                $kommando->fetch();
                
            } else {
                if($updated === false) print('<div class="contentBoxRed">Keine g&uuml;ltige ID gesetzt.</div>');
            }
           
            if(isset($kommando) && $kommando->num_rows == 1) {
            
            ?>
            
                <div class="contentBox">
                    
                    <form method="post" autocomplete="off">
                    
                        <table width="400" border="0">
                            <thead>
                                <tr>
                                    <th colspan="2">Gutschein Details</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th width="125" scope="col">Gutscheinnummer</th>
                                    <td><input class="text_field" type="text" name="gutscheinnummer" value="<?php if(isset($edit)) echo $edit; ?>" readonly /></td>
                                </tr>
                                
                                <?php if($ist_eingeloest == 0 && $ist_abgelaufen == 0) { ?>
                                
                                <tr>
                                    <th scope="col">Befristung</th>
                                    <td>
                                    <?php
                                    //get class into the page
                                    require_once("calendar/tc_calendar.php");
                                    
                                    //instantiate class and set properties
                                    $myCalendar = new tc_calendar("date1", true, false);
                                    $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                                    $myCalendar->setPath("calendar/");
                                    if(!empty($befristung)) $myCalendar->setDate(date("d", $befristung), date("m", $befristung), date("Y", $befristung));
                                    $myCalendar->setYearInterval(date("Y"), 2020);
                                    $myCalendar->dateAllow($heute, "2020-12-31");
                                    $myCalendar->showWeeks(true);
                                    $myCalendar->setAlignment("left", "bottom");
                                    
                                    //output the calendar
                                    $myCalendar->writeScript();	  
                                    ?></td>
                                </tr>
                                
                                <?php } ?>
                                
                                <tr>
                                    <th scope="col">Verschenker</th>
                                    <td>
                                        <select class="select_field" name="verschenker">
                                            <?php
                                            
                                            $sql = "SELECT vers_id, kunden_name FROM gsv_verschenker ORDER BY kunden_name ASC";
                
                                            $kommando = $db->prepare($sql);
                                            $kommando->execute();
                                            $kommando->store_result();
                                            $kommando->bind_result($wert1, $wert2);
                                            
                                            while($kommando->fetch()) {
                                                ($wert1 == $verschenker) ? printf('<option value="%s" selected>%s</option>', $wert1, $wert2) : printf('<option value="%s">%s</option>', $wert1, $wert2);
                                            }
                                            
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="col">Pr&auml;sent</th>
                                    <td>
                                        <select class="select_field" name="praesent">
                                            <?php
                                            
                                            $sql = "SELECT prae_id, prae_bezeichnung FROM gsv_praesent ORDER BY prae_bezeichnung ASC";
                
                                            $kommando = $db->prepare($sql);
                                            $kommando->execute();
                                            $kommando->store_result();
                                            $kommando->bind_result($wert1, $wert2);
                                            
                                            while($kommando->fetch()) {
                                                ($wert1 == $praesent) ? printf('<option value="%s" selected>%s</option>', $wert1, $wert2) : printf('<option value="%s">%s</option>', $wert1, $wert2);
                                            }
                                            
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="col">Rechnungsnummer</th>
                                    <td><input class="text_field" type="text" name="rechnungsnummer" value="<?php if(isset($rechnungsnummer)) echo $rechnungsnummer; ?>" /></td>
                                </tr>
                                <tr>
                                    <th scope="col">Berechnungsart</th>
                                    <td>
                                        <select class="select_field" name="berechnung">
                                            <?php
                                            
                                            (isset($_GET['redeem']) && $_GET['redeem'] == 1) ? $sql = "SELECT bere_id, bere_bezeichnung FROM gsv_berechnungsart ORDER BY bere_bezeichnung ASC" :
                                            $sql = "SELECT bere_id, bere_bezeichnung FROM gsv_berechnungsart WHERE bere_id IN (2,3) ORDER BY bere_bezeichnung ASC";
                
                                            $kommando = $db->prepare($sql);
                                            $kommando->execute();
                                            $kommando->store_result();
                                            $kommando->bind_result($wert1, $wert2);
                                            
                                            while($kommando->fetch()) {
                                                ($wert1 == $berechnung) ? printf('<option value="%s" selected>%s</option>', $wert1, $wert2) : printf('<option value="%s">%s</option>', $wert1, $wert2);
                                            }
                                            
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <?php if(isset($redeem) && $redeem == 1) { ?>
                            
                            <br />
                            <table width="400" border="0">
                                <thead>
                                    <tr>
                                        <th colspan="2">Pr&auml;sent Empf&auml;ngerdaten</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th width="125" scope="col">Firma</th>
                                        <td><input type="text" name="firma" class="text_field" placeholder="Firma" value="<?php if(isset($firma)) echo $firma; ?>" /></td>
                                    </tr>
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
                                        $myCalendar = new tc_calendar("date2", true, false);
                                        $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                                        $myCalendar->setPath("calendar/");
                                        
                                        if(!empty($versanddatum)) $myCalendar->setDate(date("d", $versanddatum), date("m", $versanddatum), date("Y", $versanddatum));
                                        
                                        // Jahresintervall setzen
                                        $start_jahr = date('Y') - 2;
                                        $end_jahr = date('Y') + 2;
                                        
                                        $myCalendar->setYearInterval($start_jahr, $end_jahr);
                                        
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
                                                // $myCalendar->setSpecificDate(array("2011-12-29"), 0, '');
                                            }
                                        }
                                        
                                        $myCalendar->setAlignment("left", "bottom");
                                        
                                        //output the calendar
                                        $myCalendar->writeScript();	  
                                        ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <br />
                            
                            <table width="400" border="0">
                                <thead>
                                    <tr>
                                        <th colspan="2">Einl&ouml;sungsort</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th width="125" scope="col">Einl&ouml;sungsort</th>
                                        <td>
                                            <select name="einloesungsort" class="select_field">
                                                <option value="2" <? if(isset($einloesungsort) && $einloesungsort == 2) echo 'selected'; ?>>Internet</option>
                                                <option value="3" <? if(isset($einloesungsort) && $einloesungsort == 3) echo 'selected'; ?>>Post</option>
                                                <option value="4" <? if(isset($einloesungsort) && $einloesungsort == 4) echo 'selected'; ?>>Gesch&auml;ft</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                        <?php } ?>
                        
                        <br />
                        <table width="400" border="0">
                            <tfoot>
                                <tr>
                                    <td colspan="2">
                                        <input type="submit" name="submit" class="submit_btn" value="Speichern" />
                                        <input type="button" name="back" class="submit_btn" value="Abbrechen" onclick="setLocation('coupons.php')" />
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        
                    </form>
                
                </div>
    
            <?php
            
            } else {
                if(isset($edit) && $updated === false) print('<div class="contentBoxRed">Keinen Gutschein mit dieser Nummer gefunden.</div>');
            }
            
            ?>
                
        </div>
    </div>
    
    <div id="footer">
        <?php include('includes/footer.php'); ?>
    </div>
    
</div>

<?php

// Dokument-Abschluss
setDocumentFooter();

// Datenbank schliessen
$db->close();

?>