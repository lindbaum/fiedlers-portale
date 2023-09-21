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
if(isset($_GET['edit']) && preg_match('/^[0-9]{5,6}$/', $_GET['edit'])) $edit = $_GET['edit'];

// Formularauswertung
$updated = false;
if(isset($_POST['submit'])) {
    $error='';
    
    /* Variante mit Pflichtfeldern (required-Attribute im Formular wieder setzen!)
    (isset($_POST['vorname']) && trim($_POST['vorname']) != '') ? $vorname = trim(htmlspecialchars($_POST['vorname'])) : $error .= '<br />Bitte einen Vornamen angeben.';
    (isset($_POST['name']) && trim($_POST['name']) != '') ? $name = trim(htmlspecialchars($_POST['name'])) : $error .= '<br />Bitte einen Namen angeben.';
    (isset($_POST['strasse']) && trim($_POST['strasse']) != '') ? $strasse = trim(htmlspecialchars($_POST['strasse'])) : $error .= '<br />Bitte eine Strasse angeben.';
    (isset($_POST['hnr']) && trim($_POST['hnr']) != '') ? $hnr = trim(htmlspecialchars($_POST['hnr'])) : $error .= '<br />Bitte eine Hausnummer angeben.';
    (isset($_POST['plz']) && trim($_POST['plz']) != '') ? $plz = trim(htmlspecialchars($_POST['plz'])) : $error .= '<br />Bitte eine Postleitzahl angeben.';
    (isset($_POST['ort']) && trim($_POST['ort']) != '') ? $ort = trim(htmlspecialchars($_POST['ort'])) : $error .= '<br />Bitte einen Ort angeben.';
    (isset($_POST['land']) && trim($_POST['land']) != '') ? $land = trim(htmlspecialchars($_POST['land'])) : $error .= '<br />Bitte ein Land angeben.';
    (isset($_POST['email']) && trim($_POST['email']) != '') ? $email = trim(htmlspecialchars($_POST['email'])) : $error .= '<br />Bitte eine Email angeben.';
    (isset($_POST['telefon']) && trim($_POST['telefon']) != '') ? $telefon = trim(htmlspecialchars($_POST['telefon'])) : $error .= '<br />Bitte ein Telefon angeben.';
    */
    
    $vorname = trim(htmlspecialchars($_POST['vorname']));
    $name = trim(htmlspecialchars($_POST['name']));
    $strasse = trim(htmlspecialchars($_POST['strasse']));
    $firma = trim(htmlspecialchars($_POST['firma']));
    $hnr = trim(htmlspecialchars($_POST['hnr']));
    $plz = trim(htmlspecialchars($_POST['plz']));
    $ort = trim(htmlspecialchars($_POST['ort']));
    $land = trim(htmlspecialchars($_POST['land']));
    $email = trim(htmlspecialchars($_POST['email']));
    $telefon = trim(htmlspecialchars($_POST['telefon']));
    
    
    // Datumseingabe prüfen...
    
    if(isset($_POST['versanddatum']) && trim($_POST['versanddatum']) != '' && $_POST['versanddatum'] != '00.00.0000') {
        $versand = str_replace(",", ".", $_POST['versanddatum']);
        
        $regex = '/^\d{1,2}\.\d{1,2}\.(\d{2}|\d{4})$/ ';
        
        if(preg_match($regex, $versand) == true) { //gültiges Datumsformat?
            
            // Datum zerlegen
            $mdate = explode(".", $versand);
            
            if(checkdate($mdate[1], $mdate[0], $mdate[2]) == true) { // korrektes Datum?
                // Datum in Zeitstempel umwandeln...
                if(strlen($mdate[2])==2) $mdate[2] = "20".$mdate[2];
                $versand = strtotime($mdate[0].".".$mdate[1].".".$mdate[2]);
            } else $error .= '<br />Bitte ein korrektes Versanddatum angeben.';
        
        } else $error .= '<br />Bitte ein korrektes Versanddatum angeben.';
        
    } else $error .= '<br />Bitte ein Versanddatum angeben.';
    
        
    $anrede = $_POST['anrede'];
    $einloesungsort = $_POST['einloesungsort'];
    
    if(empty($error)) { // Daten sind korrekt und können gespeichert werden...
        
        $heute = mktime(0,0,0, date('m'), date('d'), date('Y'));
        $flag = 1;
        
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
                ist_eingeloest=?,
                ist_abgelaufen=0,
                gsv_einloesungsort_einl_id=?
                WHERE gutschein_nummer=?";
        
        $kommando = $db->prepare($sql);
        $kommando->bind_param('sssssssssssssiii', $anrede, $name, $vorname, $firma, $strasse, $hnr, $plz, $ort, $land, $email, $telefon, $versand, $heute, $flag, $einloesungsort, $edit);
        $kommando->execute();
        
        $updated = true;
        
        if($kommando->affected_rows == 1) $success = '<img src="images/tick.png" align="top" />&nbsp;Datensatz wurde erfolgreich gespeichert.';
        if($kommando->error) $error = '<img src="images/cross.png" align="top" />&nbsp;Beim Speichern der Daten ist ein Fehler aufgetreten!';
    }
}

// Dokumentkopf setzen
setDocumentHead();

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
                <h2>Gutschein einl&ouml;sen</h2>
            </div>
            
            <?php
            
            if(isset($error) && !empty($error)) { 
                printf('<div class="contentBoxRed">%s</div>', $error);
            }
            
            if(isset($success) && !empty($success)) { 
                printf('<div class="contentBox">%s</div>', $success);
                print('<div class="contentBox"><a href="coupons_redeem.php">Zur&uuml;ck</a></div>');
            }
            
            if(isset($edit) && $updated === false) {
                // Alles gut
                $fehler = false;
                
                $sql = "SELECT A.gutschein_nummer, A.end_datum, A.rechnungs_nummer,
                        B.kunden_name, C.prae_bezeichnung, D.bere_bezeichnung, A.ist_ausgegeben, A.ist_eingeloest, A.ist_abgelaufen
                        FROM gsv_gutschein A, gsv_verschenker B, gsv_praesent C, gsv_berechnungsart D
                        WHERE A.gutschein_nummer = ? AND A.gsv_verschenker_vers_id = B.vers_id AND A.gsv_praesent_prae_id = C.prae_id AND A.gsv_berechnungsart_bere_id = D.bere_id";
                
                $kommando = $db->prepare($sql);
                $kommando->bind_param('i', $edit);
                $kommando->execute();
                $kommando->store_result();
                $kommando->bind_result($wert1, $wert2, $wert3, $wert4, $wert5, $wert6, $wert7, $wert8, $wert9);
                
                while($kommando->fetch()) {
                    $gutscheinnummer = $wert1;
                    $befristung = $wert2;
                    $rechnungsnummer = $wert3;
                    $verschenker = $wert4;
                    $praesent = $wert5;
                    $berechnung = $wert6;
                    
                    if($wert7 == 0) {
                        print('<div class="contentBoxRed">Dieser Gutschein wurde noch nicht ausgegeben!</div>');
                        $fehler = true;
                    }
                    if($wert8 == 1) {
                        print('<div class="contentBoxRed">Dieser Gutschein wurde bereits eingel&ouml;st!</div>');
                        $fehler = true;
                    }
                    /*
                    if($wert9 == 1) {
                        print('<div class="contentBoxRed">Dieser Gutschein ist abgelaufen!</div>');
                        $fehler = true;
                    }
                    */
                }
                
            } else {
                if($updated === false) print('<div class="contentBoxRed">Keine g&uuml;ltige ID gesetzt.</div>');
            }
            
            if(isset($kommando) && $kommando->num_rows == 1 && $fehler === false) {
            
            ?>
            
                <div class="contentBox">
                    
                    
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
                                <tr>
                                    <th scope="col">Befristung</th>
                                    <td><input class="text_field" type="text" name="befristung" value="<?php if(isset($befristung) && !empty($befristung)) echo date('d.m.Y', $befristung); ?>" readonly /></td>
                                </tr>
                                <tr>
                                    <th scope="col">Verschenker</th>
                                    <td><input class="text_field" type="text" name="verschenker" value="<?php if(isset($verschenker)) echo $verschenker; ?>" readonly /></td>
                                </tr>
                                <tr>
                                    <th scope="col">Pr&auml;sent</th>
                                    <td><input class="text_field" type="text" name="praesent" value="<?php if(isset($praesent)) echo $praesent; ?>" readonly /></td>
                                </tr>
                                <tr>
                                    <th scope="col">Rechnungsnummer</th>
                                    <td><input class="text_field" type="text" name="rechnungsnummer" value="<?php if(isset($rechnungsnummer)) echo $rechnungsnummer; ?>" readonly /></td>
                                </tr>
                                <tr>
                                    <th scope="col">Berechnungsart</th>
                                    <td><input class="text_field" type="text" name="berechnung" value="<?php if(isset($berechnung)) echo $berechnung; ?>" readonly /></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <br />
                        <form method="post" autocomplete="off">
                        
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
                                            <option value="3" <? if(isset($einloesungsort) && $einloesungsort == 3) echo 'selected'; ?>>Post</option>
                                            <option value="4" <? if(isset($einloesungsort) && $einloesungsort == 4) echo 'selected'; ?>>Gesch&auml;ft</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
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
                                    <td><input type="text" name="vorname" class="text_field" placeholder="Vorname" value="<?php if(isset($vorname)) echo $vorname; ?>" /></td>
                                </tr>
                                 
                                <tr>
                                    <th scope="col">Name</th>
                                    <td><input type="text" name="name" class="text_field" placeholder="Name" value="<?php if(isset($name)) echo $name; ?>" /></td>
                                </tr>
                                  
                                <tr>
                                    <th scope="col">Strasse</th>
                                    <td><input type="text" name="strasse" class="text_field" placeholder="Strasse" value="<?php if(isset($strasse)) echo $strasse; ?>" /></td>
                                </tr>
                                
                                <tr>
                                    <th scope="col">Nr.</th>
                                    <td><input type="text" name="hnr" class="text_field" placeholder="Hausnummer" value="<?php if(isset($hnr)) echo $hnr; ?>" /></td>
                                </tr>
                                
                                <tr>
                                    <th scope="col">PLZ</th>
                                    <td><input type="text" name="plz" class="text_field" placeholder="PLZ" value="<?php if(isset($plz)) echo $plz; ?>" /></td>
                                </tr>
                                
                                <tr>
                                    <th scope="col">Ort</th>
                                    <td><input type="text" name="ort" class="text_field" placeholder="Ort" value="<?php if(isset($ort)) echo $ort; ?>" /></td>
                                </tr>
                                
                                <tr>
                                    <th scope="col">Land</th>
                                    <td><input type="text" name="land" class="text_field" placeholder="Land" value="<?php if(isset($land)) echo $land; else echo 'Deutschland'; ?>" /></td>
                                </tr>
                                
                                <tr>
                                    <th scope="col">Email</th>
                                    <td><input type="email" name="email" class="text_field" placeholder="mail@example.com" value="<?php if(isset($email)) echo $email; ?>" /></td>
                                </tr>
                                
                                <tr>
                                    <th scope="col">Telefon</th>
                                    <td><input type="text" name="telefon" class="text_field" placeholder="Telefon" value="<?php if(isset($telefon)) echo $telefon; ?>" /></td>
                                </tr>
                                
                                <tr>
                                    <th scope="col">Versanddatum</th>
                                    <td>
                                    <?php
                                        //get class into the page
                                        require_once("calendar_manual/tc_calendar.php");
                                        
                                        //instantiate class and set properties
                                        $myCalendar = new tc_calendar("versanddatum", true, false);
                                        $myCalendar->setIcon("calendar_manual/images/iconCalendar.gif");
                                        $myCalendar->setPath("calendar_manual/");
                                        
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
                            
                            <tfoot>
                                <tr>
                                    <td colspan="2">
                                        <input type="submit" name="submit" class="submit_btn" value="Speichern" />
                                        <input type="button" name="back" class="submit_btn" value="Abbrechen" onclick="setLocation('coupons_redeem.php')" />
                                    </td>
                                </tr>
                            </tfoot>
                            
                        </table>
                    </form>
                
                </div>
    
            <?php
            
            } else {
                if(isset($edit) && $updated === false && $fehler === false) print('<div class="contentBoxRed">Keinen Gutschein mit dieser Nummer gefunden.</div>');
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