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

$timestamp_heute = mktime(0,0,0,date('m'),date('d'),date('Y'));

// Formularauswertung
$updated = false;
if(isset($_POST['submit'])) {
    $error = '';
    (isset($_POST['gutscheinnummer'])) ? $edit = $_POST['gutscheinnummer'] : $error .= '<br />Keine Gutscheinnummer angegeben! Dies ist ein interner Fehler, wenden Sie sich an Ihren Administrator.';
    (isset($_POST['rechnungsnummer']) && trim($_POST['rechnungsnummer']) != '') ? $rechnungsnummer = trim(htmlspecialchars($_POST['rechnungsnummer'])) : $error .= '<br />Bitte geben Sie eine Rechnungsnummer an.';
    (isset($_POST['praesent']) && $_POST['praesent'] != '1') ? $praesent = $_POST['praesent'] : $error .= '<br />Bitte w&auml;hlen Sie ein Pr&auml;sent aus.';
    
    if(isset($_POST['verschenker'])) $verschenker = $_POST['verschenker'];
    if(isset($_POST['berechnung'])) $berechnung = $_POST['berechnung'];
    (isset($_POST['date3']) && trim($_POST['date3']) != '' && $_POST['date3'] != '0000-00-00') ? $versand = strtotime($_POST['date3']) : $versand = '';
    
    if(empty($error)) {
        $sql = "UPDATE gsv_gutschein
                SET end_datum=?,
                rechnungs_nummer=?,
                gsv_berechnungsart_bere_id=?,
                gsv_praesent_prae_id=?,
                gsv_verschenker_vers_id=?,
                ausgabe_datum=?,
                ist_ausgegeben=1
                WHERE gutschein_nummer=?";

        $kommando = $db->prepare($sql);
        $kommando->bind_param('ssiiisi', $versand, $_POST['rechnungsnummer'], $_POST['berechnung'], $_POST['praesent'], $_POST['verschenker'], $timestamp_heute, $edit);
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
                <h2>Gutschein ausgeben</h2>
            </div>
            
            <?php
            
            if(isset($error) && !empty($error)) { 
                printf('<div class="contentBoxRed">%s</div>', $error);
            }
            
            if(isset($success) && !empty($success)) { 
                printf('<div class="contentBox">%s</div>', $success);
                print('<div class="contentBox"><a href="coupons_prepare.php">Zur&uuml;ck</a></div>');
            }
            
            if(isset($edit) && $updated === false) {
                // Alles gut
                $sql = "SELECT gutschein_nummer FROM gsv_gutschein WHERE gutschein_nummer = ?";
                
                $kommando = $db->prepare($sql);
                $kommando->bind_param('i', $edit);
                $kommando->execute();
                $kommando->store_result();
                $kommando->bind_result($wert1);
                
                while($kommando->fetch()) {
                    $gutscheinnummer = $wert1;
                }
                
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
                                    <th colspan="2">Gutschein ausgeben</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="col">Gutscheinnummer</th>
                                    <td><input class="text_field" type="text" name="gutscheinnummer" value="<?php if(isset($edit)) echo $edit; ?>" readonly /></td>
                                </tr>
                                <tr>
                                    <th scope="col">Befristung</th>
                                    <td>
                                    <?php
                                    //get class into the page
                                    require_once("calendar/tc_calendar.php");
                                    
                                    //instantiate class and set properties
                                    $myCalendar = new tc_calendar("date3", true, false);
                                    $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                                    $myCalendar->setPath("calendar/");
                                    $myCalendar->setYearInterval(date("Y"), date("Y", strtotime('+ 3 year')));
                                    $myCalendar->dateAllow(date("Y-m-d"), date("Y-m-d", strtotime('+ 3 year')));
                                    $myCalendar->showWeeks(true);
                                    $myCalendar->setAlignment("left", "bottom");
                                    
                                    //output the calendar
                                    $myCalendar->writeScript();	  
                                    ?>
                                    </td>
                                </tr>
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
                                    <td><input class="text_field" type="text" name="rechnungsnummer" placeholder="Rechnungsnummer" value="<?php if(isset($rechnungsnummer)) echo $rechnungsnummer; ?>" maxlength="45" required="required" /></td>
                                </tr>
                                <tr>
                                    <th scope="col">Berechnungsart</th>
                                    <td>
                                        <select class="select_field" name="berechnung">
                                            <?php
                                            
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
                            <tfoot>
                                <tr>
                                    <td colspan="2">
                                        <input class="submit_btn" type="submit" name="submit" value="Speichern" />
                                        <input type="button" name="back" class="submit_btn" value="Abbrechen" onclick="setLocation('coupons_prepare.php')" />
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