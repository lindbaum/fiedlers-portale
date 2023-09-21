<?php
/**
 *    Autor        : Ren? Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
require_once('includes/auth.php'); // incl. session_start();
if($_SESSION['user_level'] >= 3) define('SECURE', true);
require_once('includes/dbconfig.php');
define("L_LANG", "de_DE"); // Kalender-Sprache (DE)

// Funktions-Bibliothek
require_once('functions/main_functions.php');

// Dokumentkopf setzen
setDocumentHead();

// Startwerte setzen
(isset($_POST['srch_abgelaufen'])) ? $srch_abgelaufen = $_POST['srch_abgelaufen']: $srch_abgelaufen = 0;
(isset($_POST['srch_eingeloest'])) ? $srch_eingeloest = $_POST['srch_eingeloest']: $srch_eingeloest = 0;

(isset($_POST['sort_field'])) ? $sortierung = $_POST['sort_field']: $sortierung = "A.gutschein_nummer";
(isset($_POST['reihenfolge'])) ? $reihenfolge = $_POST['reihenfolge']: $reihenfolge = "ASC";

// heutiges Datum
$heute = mktime(0,0,0,date('m'),date('d'),date('Y'));

// Formularauswertung
$updated = false;
if(isset($_POST['multiedit'])) {
    $error='';
    
    if(!isset($_POST['multi_check_rechnungsnummer']) && !isset($_POST['multi_check_berechnungsart']) && !isset($_POST['multi_check_befristung'])) $error = 'Zum Speichern bitte mindestens ein Feld anw&auml;hlen.';
    
    // Datum checken
    if(isset($_POST['multi_check_befristung'])) {
        
    }
    
    if(empty($error)) { // Mindestens eine Auswahl ist erfolgt...
        $multi_ids = substr($_POST['multi_ids'], 0, -1); // IDs bereinigen
        
        // Rechnungsnummer
        if(isset($_POST['multi_check_rechnungsnummer']) && $_POST['multi_check_rechnungsnummer'] == 'on') {
            $multi_rechnungsnummer = trim(htmlspecialchars($_POST['multi_rechnungsnummer']));
            
            $sql = "UPDATE gsv_gutschein SET rechnungs_nummer = ? WHERE gutschein_nummer IN ($multi_ids)";
            //print $sql;
                
            $kommando = $db->prepare($sql);
            $kommando->bind_param('s', $multi_rechnungsnummer);
            $kommando->execute();
            
            $updated = true;
            
            if($kommando->error) $error = '<img src="images/cross.png" align="top" />&nbsp;Beim Speichern der Daten ist ein Fehler aufgetreten!';
            else $success = '<img src="images/tick.png" align="top" />&nbsp;Datensatz wurde erfolgreich gespeichert.';
        }
        
        // Berechnungsart
        if(isset($_POST['multi_check_berechnungsart']) && $_POST['multi_check_berechnungsart'] == 'on') {
            $multi_berechnungsart = trim(htmlspecialchars($_POST['multi_berechnungsart']));
            
            $sql = "UPDATE gsv_gutschein SET gsv_berechnungsart_bere_id = ? WHERE gutschein_nummer IN ($multi_ids)";
            //print $sql;
                
            $kommando = $db->prepare($sql);
            $kommando->bind_param('i', $multi_berechnungsart);
            $kommando->execute();
            
            $updated = true;
            
            if($kommando->error) $error = '<img src="images/cross.png" align="top" />&nbsp;Beim Speichern der Daten ist ein Fehler aufgetreten!';
            else $success = '<img src="images/tick.png" align="top" />&nbsp;Datensatz wurde erfolgreich gespeichert.';
        }
        
        // Befristung
        if(isset($_POST['multi_check_befristung']) && $_POST['multi_check_befristung'] == 'on') {
            if(isset($_POST['multi_befristung']) && trim($_POST['multi_befristung']) != '' && $_POST['multi_befristung'] != '0000-00-00') {
                $multi_befristung = strtotime($_POST['multi_befristung']); // eigene Funktion
                $multi_befristung = mktime(0,0,0,date('m',$multi_befristung), date('d',$multi_befristung), date('Y',$multi_befristung));
            } else {
                $multi_befristung = '';
            }
            
            // Kalender 2 Variante!!!
            /*
            if(isset($_POST['multi_befristung']) && trim($_POST['multi_befristung']) != '' && $_POST['multi_befristung'] != '00,00,0000') {
                $multi_befristung = manualStrtotime($_POST['multi_befristung']); // eigene Funktion
                $multi_befristung = mktime(0,0,0,date('m',$multi_befristung), date('d',$multi_befristung), date('Y',$multi_befristung));
            } else {
                $multi_befristung = '';
            }
            */
            
            $sql = "UPDATE gsv_gutschein SET end_datum = ? WHERE gutschein_nummer IN ($multi_ids)";
            //print $sql;
                
            $kommando = $db->prepare($sql);
            $kommando->bind_param('s', $multi_befristung);
            $kommando->execute();
            
            $updated = true;
            
            if($kommando->error) $error = '<img src="images/cross.png" align="top" />&nbsp;Beim Speichern der Daten ist ein Fehler aufgetreten!';
            else $success = '<img src="images/tick.png" align="top" />&nbsp;Datensatz wurde erfolgreich gespeichert.';
            
            // Stati der Gutscheine aktualisieren
            if($multi_befristung < $heute && !empty($multi_befristung)) {
                // Nun abgelaufene Gutscheine deaktivieren
                $sql = "UPDATE gsv_gutschein SET ist_abgelaufen=1, gsv_einloesungsort_einl_id=5 WHERE ist_eingeloest=0 AND gutschein_nummer IN ($multi_ids)";
                
                $kommando = $db->prepare($sql);
                $kommando->execute();
            }
            
            if($multi_befristung >= $heute || empty($multi_befristung)) {
                // Abgelaufene Gutscheine, deren Befristungen leer sind oder in der Zukunft liegen, wieder aktivieren
                $sql = "UPDATE gsv_gutschein SET ist_abgelaufen=0, gsv_einloesungsort_einl_id=1 WHERE ist_eingeloest=0 AND gutschein_nummer IN ($multi_ids)";
                
                $kommando = $db->prepare($sql);
                $kommando->execute();
            }
        }
    }
}

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
            
            <?php
            if(isset($error) && !empty($error)) { 
                printf('<div class="contentBoxRed">%s</div>', $error);
            }
            
            if(isset($success) && !empty($success)) { 
                printf('<div class="contentBox">%s</div>', $success);
            }
            ?>
        
            <div class="contentBox">
                <h2>Gutschein&uuml;bersicht</h2>
            </div>
            
            <div class="contentBox">
                <h4>Filter</h4><br />
                <form name="filter" id="filter" method="post">
                        
                    <div>
                        <table border="0">
                            <thead>
                                <tr>
                                    <th>Filter</th>
                                    <th></th>
                                    <th></th>
                                    <th>Sortierung 
                                        (<input type="radio" name="reihenfolge" value="ASC" <?php if(isset($reihenfolge) && $reihenfolge == 'ASC') echo 'checked="checked"'; ?> /> auf /
                                        <input type="radio" name="reihenfolge" value="DESC" <?php if(isset($reihenfolge) && $reihenfolge == 'DESC') echo 'checked="checked"'; ?> /> ab)</th>
                                    <th>Filter an / aus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Gutscheinnummer (von / bis)</th>
                                    <td><input class="text_field" type="text" name="srch_gsnummer_von" placeholder="Gutscheinnummer von"
                                        <?php if(isset($_POST['srch_gsnummer_von']) && trim($_POST['srch_gsnummer_von']) != '') echo 'value="'.htmlspecialchars($_POST['srch_gsnummer_von']).'"'; ?> /></td>
                                    <td><input class="text_field" type="text" name="srch_gsnummer_bis" placeholder="Gutscheinnummer bis"
                                        <?php if(isset($_POST['srch_gsnummer_bis']) && trim($_POST['srch_gsnummer_bis']) != '') echo 'value="'.htmlspecialchars($_POST['srch_gsnummer_bis']).'"'; ?> /></td>
                                    <td><input type="radio" name="sort_field" value="A.gutschein_nummer" <?php if(isset($sortierung) && $sortierung == 'A.gutschein_nummer') echo 'checked="checked"'; ?> /></td>
                                    <td><input type="checkbox" name="check_gutscheinnummer" <?php if(isset($_POST['check_gutscheinnummer']) && $_POST['check_gutscheinnummer'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                                <tr>
                                    <th>Rechnungsnummer (von / bis)</th>
                                    <td><input class="text_field" type="text" name="srch_rgnummer_von" placeholder="RG-Nr. von"
                                        <?php if(isset($_POST['srch_rgnummer_von']) && trim($_POST['srch_rgnummer_von']) != '') echo 'value="'.htmlspecialchars($_POST['srch_rgnummer_von']).'"'; ?> /></td>
                                    <td><input class="text_field" type="text" name="srch_rgnummer_bis" placeholder="RG-Nr. bis"
                                        <?php if(isset($_POST['srch_rgnummer_bis']) && trim($_POST['srch_rgnummer_bis']) != '') echo 'value="'.htmlspecialchars($_POST['srch_rgnummer_bis']).'"'; ?> /></td>
                                    <td><input type="radio" name="sort_field" value="A.rechnungs_nummer" <?php if(isset($sortierung) && $sortierung == 'A.rechnungs_nummer') echo 'checked="checked"'; ?> /></td>
                                    <td><input type="checkbox" name="check_rechnungsnummer" <?php if(isset($_POST['check_rechnungsnummer']) && $_POST['check_rechnungsnummer'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                                <tr>
                                    <th>Rechnungsnummer (Textsuche)</th>
                                    <td><input class="text_field" type="text" name="srch_rgnummer_text" placeholder="RG-Nr. Text"
                                        <?php if(isset($_POST['srch_rgnummer_text']) && trim($_POST['srch_rgnummer_text']) != '') echo 'value="'.htmlspecialchars($_POST['srch_rgnummer_text']).'"'; ?> /></td>
                                    <td></td>
                                    <td></td>
                                    <td><input type="checkbox" name="check_rechnungsnummer_text" <?php if(isset($_POST['check_rechnungsnummer_text']) && $_POST['check_rechnungsnummer_text'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                                <tr>
                                    <th>Ausgabedatum (von / bis)</th>
                                    <td>
                                        <?php
                                        require_once("calendar/tc_calendar.php");
                                        
                                        //instantiate class and set properties
                                        $myCalendar = new tc_calendar("ausgabe_von", true, false);
                                        $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                                        $myCalendar->setPath("calendar/");
                                        if(isset($_POST['ausgabe_von']) && $_POST['ausgabe_von'] != '0000-00-00') $myCalendar->setDate(date("d", strtotime($_POST['ausgabe_von'])), date("m", strtotime($_POST['ausgabe_von'])), date("Y", strtotime($_POST['ausgabe_von'])));
                                        $myCalendar->setYearInterval('2011', date("Y", strtotime('+ 3 year')));
                                        $myCalendar->showWeeks(true);
                                        $myCalendar->setAlignment("right", "bottom");
                                        $myCalendar->zindex = 106;
                                        
                                        //output the calendar
                                        $myCalendar->writeScript();	  
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        require_once("calendar/tc_calendar.php");
                                        
                                        //instantiate class and set properties
                                        $myCalendar = new tc_calendar("ausgabe_bis", true, false);
                                        $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                                        $myCalendar->setPath("calendar/");
                                        if(isset($_POST['ausgabe_bis']) && $_POST['ausgabe_bis'] != '0000-00-00') $myCalendar->setDate(date("d", strtotime($_POST['ausgabe_bis'])), date("m", strtotime($_POST['ausgabe_bis'])), date("Y", strtotime($_POST['ausgabe_bis'])));
                                        $myCalendar->setYearInterval('2011', date("Y", strtotime('+ 3 year')));
                                        $myCalendar->showWeeks(true);
                                        $myCalendar->setAlignment("right", "bottom");
                                        $myCalendar->zindex = 105;
                                        
                                        //output the calendar
                                        $myCalendar->writeScript();	  
                                        ?>
                                    </td>
                                    <td><input type="radio" name="sort_field" value="A.ausgabe_datum" <?php if(isset($sortierung) && $sortierung == 'A.ausgabe_datum') echo 'checked="checked"'; ?> /></td>
                                    <td><input type="checkbox" name="check_ausgabedatum" <?php if(isset($_POST['check_ausgabedatum']) && $_POST['check_ausgabedatum'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                                <tr>
                                    <th>Einl&ouml;sedatum (von / bis)</th>
                                    <td>
                                        <?php
                                        require_once("calendar/tc_calendar.php");
                                        
                                        //instantiate class and set properties
                                        $myCalendar = new tc_calendar("einloese_von", true, false);
                                        $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                                        $myCalendar->setPath("calendar/");
                                        if(isset($_POST['einloese_von']) && $_POST['einloese_von'] != '0000-00-00') $myCalendar->setDate(date("d", strtotime($_POST['einloese_von'])), date("m", strtotime($_POST['einloese_von'])), date("Y", strtotime($_POST['einloese_von'])));
                                        $myCalendar->setYearInterval('2011', date("Y", strtotime('+ 3 year')));
                                        $myCalendar->showWeeks(true);
                                        $myCalendar->setAlignment("right", "bottom");
                                        $myCalendar->zindex = 104;
                                        
                                        //output the calendar
                                        $myCalendar->writeScript();	  
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        require_once("calendar/tc_calendar.php");
                                        
                                        //instantiate class and set properties
                                        $myCalendar = new tc_calendar("einloese_bis", true, false);
                                        $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                                        $myCalendar->setPath("calendar/");
                                        if(isset($_POST['einloese_bis']) && $_POST['einloese_bis'] != '0000-00-00') $myCalendar->setDate(date("d", strtotime($_POST['einloese_bis'])), date("m", strtotime($_POST['einloese_bis'])), date("Y", strtotime($_POST['einloese_bis'])));
                                        $myCalendar->setYearInterval('2011', date("Y", strtotime('+ 3 year')));
                                        $myCalendar->showWeeks(true);
                                        $myCalendar->setAlignment("right", "bottom");
                                        $myCalendar->zindex = 103;
                                        
                                        //output the calendar
                                        $myCalendar->writeScript();	  
                                        ?>
                                    </td>
                                    <td><input type="radio" name="sort_field" value="A.einloese_datum" <?php if(isset($sortierung) && $sortierung == 'A.einloese_datum') echo 'checked="checked"'; ?> /></td>
                                    <td><input type="checkbox" name="check_einloesedatum" <?php if(isset($_POST['check_einloesedatum']) && $_POST['check_einloesedatum'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                                <tr>
                                    <th>Versanddatum (von / bis)</th>
                                    <td>
                                        <?php
                                        require_once("calendar/tc_calendar.php");
                                        
                                        //instantiate class and set properties
                                        $myCalendar = new tc_calendar("versand_von", true, false);
                                        $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                                        $myCalendar->setPath("calendar/");
                                        if(isset($_POST['versand_von']) && $_POST['versand_von'] != '0000-00-00') $myCalendar->setDate(date("d", strtotime($_POST['versand_von'])), date("m", strtotime($_POST['versand_von'])), date("Y", strtotime($_POST['versand_von'])));
                                        $myCalendar->setYearInterval('2011', date("Y", strtotime('+ 3 year')));
                                        $myCalendar->showWeeks(true);
                                        $myCalendar->setAlignment("right", "bottom");
                                        $myCalendar->zindex = 102;
                                        
                                        //output the calendar
                                        $myCalendar->writeScript();	  
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        require_once("calendar/tc_calendar.php");
                                        
                                        //instantiate class and set properties
                                        $myCalendar = new tc_calendar("versand_bis", true, false);
                                        $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                                        $myCalendar->setPath("calendar/");
                                        if(isset($_POST['versand_bis']) && $_POST['versand_bis'] != '0000-00-00') $myCalendar->setDate(date("d", strtotime($_POST['versand_bis'])), date("m", strtotime($_POST['versand_bis'])), date("Y", strtotime($_POST['versand_bis'])));
                                        $myCalendar->setYearInterval('2011', date("Y", strtotime('+ 3 year')));
                                        $myCalendar->showWeeks(true);
                                        $myCalendar->setAlignment("right", "bottom");
                                        $myCalendar->zindex = 101;
                                        
                                        //output the calendar
                                        $myCalendar->writeScript();	  
                                        ?>
                                    </td>
                                    <td><input type="radio" name="sort_field" value="A.wunsch_versand_datum" <?php if(isset($sortierung) && $sortierung == 'A.wunsch_versand_datum') echo 'checked="checked"'; ?> /></td>
                                    <td><input type="checkbox" name="check_versanddatum" <?php if(isset($_POST['check_versanddatum']) && $_POST['check_versanddatum'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                                <tr>
                                    <th>Befristung (von / bis)</th>
                                    <td>
                                        <?php
                                        require_once("calendar/tc_calendar.php");
                                        
                                        //instantiate class and set properties
                                        $myCalendar = new tc_calendar("befristung_von", true, false);
                                        $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                                        $myCalendar->setPath("calendar/");
                                        if(isset($_POST['befristung_von']) && $_POST['befristung_von'] != '0000-00-00') $myCalendar->setDate(date("d", strtotime($_POST['befristung_von'])), date("m", strtotime($_POST['befristung_von'])), date("Y", strtotime($_POST['befristung_von'])));
                                        $myCalendar->setYearInterval('2011', date("Y", strtotime('+ 3 year')));
                                        $myCalendar->showWeeks(true);
                                        $myCalendar->setAlignment("right", "bottom");
                                        $myCalendar->zindex = 100;
                                        
                                        //output the calendar
                                        $myCalendar->writeScript();	  
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        require_once("calendar/tc_calendar.php");
                                        
                                        //instantiate class and set properties
                                        $myCalendar = new tc_calendar("befristung_bis", true, false);
                                        $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                                        $myCalendar->setPath("calendar/");
                                        if(isset($_POST['befristung_bis']) && $_POST['befristung_bis'] != '0000-00-00') $myCalendar->setDate(date("d", strtotime($_POST['befristung_bis'])), date("m", strtotime($_POST['befristung_bis'])), date("Y", strtotime($_POST['befristung_bis'])));
                                        $myCalendar->setYearInterval('2011', date("Y", strtotime('+ 3 year')));
                                        $myCalendar->showWeeks(true);
                                        $myCalendar->setAlignment("right", "bottom");
                                        $myCalendar->zindex = 99;
                                        
                                        //output the calendar
                                        $myCalendar->writeScript();	  
                                        ?>
                                    </td>
                                    <td><input type="radio" name="sort_field" value="A.end_datum" <?php if(isset($sortierung) && $sortierung == 'A.end_datum') echo 'checked="checked"'; ?> /></td>
                                    <td><input type="checkbox" name="check_befristung" <?php if(isset($_POST['check_befristung']) && $_POST['check_befristung'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                                <tr>
                                    <th>Pr&auml;sent (von / bis)</th>
                                    <td><select name="praesent" class="select_field">
                                        <?php
                                        
                                        $sql = "SELECT prae_id, prae_bezeichnung FROM gsv_praesent WHERE prae_id NOT IN (1) ORDER BY prae_bezeichnung ASC";
                                        
                                        $kommando = $db->prepare($sql);
                                        $kommando->execute();
                                        $kommando->store_result();
                                        $kommando->bind_result($praeid, $praesent);
                                        
                                        while($kommando->fetch()) {
                                            if(isset($_POST['praesent']) && $_POST['praesent'] == $praeid) printf('<option value="%s" selected>%s</option>', $praeid, $praesent);
                                            else printf('<option value="%s">%s</option>', $praeid, $praesent);
                                        }
                                        
                                        ?>
                                        </select>
                                    </td>
                                    <td></td>
                                    <td><input type="radio" name="sort_field" value="A.gsv_praesent_prae_id" <?php if(isset($sortierung) && $sortierung == 'A.gsv_praesent_prae_id') echo 'checked="checked"'; ?> /></td>
                                    <td><input type="checkbox" name="check_praesent" <?php if(isset($_POST['check_praesent']) && $_POST['check_praesent'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                                <tr>
                                    <th>Verschenker (von / bis)</th>
                                    <td><select name="verschenker" class="select_field">
                                        <?php
                                        
                                        //$sql = "SELECT vers_id, kunden_name FROM gsv_verschenker WHERE vers_id NOT IN (1) ORDER BY kunden_name ASC" ;
                                        $sql = "SELECT vers_id, kunden_name FROM gsv_verschenker ORDER BY kunden_name ASC" ;
                                        
                                        $kommando = $db->prepare($sql);
                                        $kommando->execute();
                                        $kommando->store_result();
                                        $kommando->bind_result($versid, $kundenname);
                                        
                                        while($kommando->fetch()) {
                                            if(isset($_POST['verschenker']) && $_POST['verschenker'] == $versid) printf('<option value="%s" selected>%s</option>', $versid, $kundenname);
                                            else printf('<option value="%s">%s</option>', $versid, $kundenname);
                                        }
                                        
                                        ?>
                                        </select>
                                    </td>
                                    <td></td>
                                    <td><input type="radio" name="sort_field" value="A.gsv_verschenker_vers_id" <?php if(isset($sortierung) && $sortierung == 'A.gsv_verschenker_vers_id') echo 'checked="checked"'; ?> /></td>
                                    <td><input type="checkbox" name="check_verschenker" <?php if(isset($_POST['check_verschenker']) && $_POST['check_verschenker'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                                <tr>
                                    <th>Berechnungsart</th>
                                    <td><select name="berechnungsart" class="select_field">
                                        <?php
                                        
                                        $sql = "SELECT bere_id, bere_bezeichnung FROM gsv_berechnungsart WHERE bere_id NOT IN (1) ORDER BY bere_id ASC" ;
                                        
                                        $kommando = $db->prepare($sql);
                                        $kommando->execute();
                                        $kommando->store_result();
                                        $kommando->bind_result($bereid, $berechnung);
                                        
                                        while($kommando->fetch()) {
                                            if(isset($_POST['berechnungsart']) && $_POST['berechnungsart'] == $bereid) printf('<option value="%s" selected>%s</option>', $bereid, $berechnung);
                                            else printf('<option value="%s">%s</option>', $bereid, $berechnung);
                                        }
                                        
                                        ?>
                                        </select>
                                    </td>
                                    <td></td>
                                    <td><input type="radio" name="sort_field" value="A.gsv_berechnungsart_bere_id" <?php if(isset($sortierung) && $sortierung == 'A.gsv_berechnungsart_bere_id') echo 'checked="checked"'; ?> /></td>
                                    <td><input type="checkbox" name="check_berechnungsart" <?php if(isset($_POST['check_berechnungsart']) && $_POST['check_berechnungsart'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                                <tr>
                                    <th>Einl&ouml;sungsort</th>
                                    <td><select name="einloesungsort" class="select_field">
                                        <?php
                                        
                                        $sql = "SELECT einl_id, einl_bezeichnung FROM gsv_einloesungsort WHERE einl_id NOT IN (1) ORDER BY einl_id ASC" ;
                                        
                                        $kommando = $db->prepare($sql);
                                        $kommando->execute();
                                        $kommando->store_result();
                                        $kommando->bind_result($einlid, $ort);
                                        
                                        while($kommando->fetch()) {
                                            if(isset($_POST['einloesungsort']) && $_POST['einloesungsort'] == $einlid) printf('<option value="%s" selected>%s</option>', $einlid, $ort);
                                            else printf('<option value="%s">%s</option>', $einlid, $ort);
                                        }
                                        
                                        ?>
                                        </select>
                                    </td>
                                    <td></td>
                                    <td><input type="radio" name="sort_field" value="A.gsv_einloesungsort_einl_id" <?php if(isset($sortierung) && $sortierung == 'A.gsv_einloesungsort_einl_id') echo 'checked="checked"'; ?> /></td>
                                    <td><input type="checkbox" name="check_einloesungsort" <?php if(isset($_POST['check_einloesungsort']) && $_POST['check_einloesungsort'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                                <tr>
                                    <th>abgelaufen</th>
                                    <td><input type="radio" name="srch_abgelaufen" value="1" <?php if(isset($srch_abgelaufen) && $srch_abgelaufen == '1') echo 'checked="checked"'; ?> /> ja</td>
                                    <td><input type="radio" name="srch_abgelaufen" value="0" <?php if(isset($srch_abgelaufen) && $srch_abgelaufen == '0') echo 'checked="checked"'; ?> /> nein</td>
                                    <td><input type="radio" name="sort_field" value="A.ist_abgelaufen" <?php if(isset($sortierung) && $sortierung == 'A.ist_abgelaufen') echo 'checked="checked"'; ?> /></td>
                                    <td><input type="checkbox" name="check_abgelaufen" <?php if(isset($_POST['check_abgelaufen']) && $_POST['check_abgelaufen'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                                <tr>
                                    <th>eingel&ouml;st</th>
                                    <td><input type="radio" name="srch_eingeloest" value="1" <?php if(isset($srch_eingeloest) && $srch_eingeloest == '1') echo 'checked="checked"'; ?> /> ja</td>
                                    <td><input type="radio" name="srch_eingeloest" value="0" <?php if(isset($srch_eingeloest) && $srch_eingeloest == '0') echo 'checked="checked"'; ?> /> nein</td>
                                    <td><input type="radio" name="sort_field" value="A.ist_eingeloest" <?php if(isset($sortierung) && $sortierung == 'A.ist_eingeloest') echo 'checked="checked"'; ?> /></td>
                                    <td><input type="checkbox" name="check_eingeloest" <?php if(isset($_POST['check_eingeloest']) && $_POST['check_eingeloest'] == 'on') echo 'checked="checked"'; ?> /></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5">
                                        <input type="submit" name="submit" class="submit_btn" value="Filtern" />
                                        <input class="submit_btn" type="button" value="Zur&uuml;cksetzen" onclick="setLocation('coupons.php')" /></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
            </div>
            
            <?php
            // Formular auswerten
            if (isset($_POST['submit']) || isset($_POST['multiedit'])) {
                
                echo '<div class="contentBox">';
                
                /* ALT
                $sql = "SELECT A.gutschein_nummer, A.ausgabe_datum, B.prae_bezeichnung, C.kunden_name, A.end_datum, A.rechnungs_nummer, D.bere_bezeichnung, E.einl_bezeichnung, A.einloese_datum, A.ist_abgelaufen, A.ist_eingeloest 
                        FROM gsv_gutschein A, gsv_praesent B, gsv_verschenker C, gsv_berechnungsart D, gsv_einloesungsort E 
                        WHERE ist_ausgegeben = 1 
                        AND A.gsv_praesent_prae_id = B.prae_id
                        AND A.gsv_verschenker_vers_id = C.vers_id
                        AND A.gsv_berechnungsart_bere_id = D.bere_id
                        AND A.gsv_einloesungsort_einl_id = E.einl_id";
                */
                
                $sql = "SELECT A.gutschein_nummer, A.ausgabe_datum, B.prae_bezeichnung, C.kunden_name, A.end_datum, A.rechnungs_nummer, D.bere_bezeichnung, E.einl_bezeichnung, A.einloese_datum, A.ist_abgelaufen, A.ist_eingeloest,
                        A.empf_nachname, A.empf_ort, A.empf_land, A.wunsch_versand_datum
                        FROM gsv_gutschein A, gsv_praesent B, gsv_verschenker C, gsv_berechnungsart D, gsv_einloesungsort E 
                        WHERE ist_ausgegeben = 1 
                        AND A.gsv_praesent_prae_id = B.prae_id
                        AND A.gsv_verschenker_vers_id = C.vers_id
                        AND A.gsv_berechnungsart_bere_id = D.bere_id
                        AND A.gsv_einloesungsort_einl_id = E.einl_id";
                
                if(isset($_POST['check_gutscheinnummer']) && $_POST['check_gutscheinnummer'] == 'on') {
                    if(isset($_POST['srch_gsnummer_von']) && trim($_POST['srch_gsnummer_von']) != '') $sql .= ' AND A.gutschein_nummer >= "' . trim($_POST['srch_gsnummer_von']) . '"';
                    if(isset($_POST['srch_gsnummer_bis']) && trim($_POST['srch_gsnummer_bis']) != '') $sql .= ' AND A.gutschein_nummer <= "' . trim($_POST['srch_gsnummer_bis']) . '"';
                }
                
                if(isset($_POST['check_rechnungsnummer']) && $_POST['check_rechnungsnummer'] == 'on') {
                    if(isset($_POST['srch_rgnummer_von']) && trim($_POST['srch_rgnummer_von']) != '') $sql .= ' AND A.rechnungs_nummer >= "' . trim($_POST['srch_rgnummer_von']) . '"';
                    if(isset($_POST['srch_rgnummer_bis']) && trim($_POST['srch_rgnummer_bis']) != '') $sql .= ' AND A.rechnungs_nummer <= "' . trim($_POST['srch_rgnummer_bis']) . '"';
                }
                
                if(isset($_POST['check_rechnungsnummer_text']) && $_POST['check_rechnungsnummer_text'] == 'on') {
                    if(isset($_POST['srch_rgnummer_text']) && trim($_POST['srch_rgnummer_text']) != '') $sql .= ' AND A.rechnungs_nummer LIKE "%' . trim($_POST['srch_rgnummer_text']) . '%"';
                }
                
                if(isset($_POST['check_ausgabedatum']) && $_POST['check_ausgabedatum'] == 'on') {
                    if(isset($_POST['ausgabe_von']) && $_POST['ausgabe_von'] != '0000-00-00') $sql .= ' AND A.ausgabe_datum >= "' . strtotime($_POST['ausgabe_von']) . '"';
                    if(isset($_POST['ausgabe_bis']) && $_POST['ausgabe_bis'] != '0000-00-00') $sql .= ' AND A.ausgabe_datum <= "' . strtotime($_POST['ausgabe_bis']) . '"';
                }
                
                if(isset($_POST['check_einloesedatum']) && $_POST['check_einloesedatum'] == 'on') {
                    if(isset($_POST['einloese_von']) && $_POST['einloese_von'] != '0000-00-00') $sql .= ' AND A.einloese_datum >= "' . strtotime($_POST['einloese_von']) . '"';
                    if(isset($_POST['einloese_bis']) && $_POST['einloese_bis'] != '0000-00-00') $sql .= ' AND A.einloese_datum <= "' . strtotime($_POST['einloese_bis']) . '"';
                }
                
                if(isset($_POST['check_versanddatum']) && $_POST['check_versanddatum'] == 'on') {
                    if(isset($_POST['versand_von']) && $_POST['versand_von'] != '0000-00-00') $sql .= ' AND A.wunsch_versand_datum >= "' . strtotime($_POST['versand_von']) . '"';
                    if(isset($_POST['versand_bis']) && $_POST['versand_bis'] != '0000-00-00') $sql .= ' AND A.wunsch_versand_datum <= "' . strtotime($_POST['versand_bis']) . '"';
                }
                
                if(isset($_POST['check_befristung']) && $_POST['check_befristung'] == 'on') {
                    if(isset($_POST['befristung_von']) && $_POST['befristung_von'] != '0000-00-00') $sql .= ' AND A.end_datum >= "' . strtotime($_POST['befristung_von']) . '"';
                    if(isset($_POST['befristung_bis']) && $_POST['befristung_bis'] != '0000-00-00') $sql .= ' AND A.end_datum <= "' . strtotime($_POST['befristung_bis']) . '"';
                }
                
                if(isset($_POST['check_praesent']) && $_POST['check_praesent'] == 'on') {
                    if(isset($_POST['praesent'])) $sql .= ' AND A.gsv_praesent_prae_id = ' . $_POST['praesent'];
                }
                
                if(isset($_POST['check_verschenker']) && $_POST['check_verschenker'] == 'on') {
                    if(isset($_POST['verschenker'])) $sql .= ' AND A.gsv_verschenker_vers_id = ' . $_POST['verschenker'];
                }
                
                if(isset($_POST['check_berechnungsart']) && $_POST['check_berechnungsart'] == 'on') {
                    if(isset($_POST['berechnungsart'])) $sql .= ' AND A.gsv_berechnungsart_bere_id = ' . $_POST['berechnungsart'];
                }
                
                if(isset($_POST['check_einloesungsort']) && $_POST['check_einloesungsort'] == 'on') {
                    if(isset($_POST['einloesungsort'])) $sql .= ' AND A.gsv_einloesungsort_einl_id = ' . $_POST['einloesungsort'];
                }
                
                if(isset($_POST['check_abgelaufen']) && $_POST['check_abgelaufen'] == 'on') {
                    $sql .= ' AND A.ist_abgelaufen = ' . $_POST['srch_abgelaufen'];
                }
                
                if(isset($_POST['check_eingeloest']) && $_POST['check_eingeloest'] == 'on') {
                    $sql .= ' AND A.ist_eingeloest = ' . $_POST['srch_eingeloest'];
                }
                
                if(isset($_POST['check_empf_name']) && $_POST['check_empf_name'] == 'on') {
                    if(isset($_POST['srch_empf_name']) && trim($_POST['srch_empf_name']) != '') $sql .= ' AND A.empf_nachname LIKE "%' . trim($_POST['srch_empf_name']) . '%"';
                }
                
                if(isset($_POST['check_empf_ort']) && $_POST['check_empf_ort'] == 'on') {
                    if(isset($_POST['srch_empf_ort']) && trim($_POST['srch_empf_ort']) != '') $sql .= ' AND A.empf_ort LIKE "%' . trim($_POST['srch_empf_ort']) . '%"';
                }
                
                if(isset($_POST['check_empf_land']) && $_POST['check_empf_land'] == 'on') {
                    if(isset($_POST['srch_empf_land']) && trim($_POST['srch_empf_land']) != '') $sql .= ' AND A.empf_land LIKE "%' . trim($_POST['srch_empf_land']) . '%"';
                }
               
                $sql .= " ORDER BY " . $sortierung . " " . $reihenfolge ;
                
                //print $sql;
                
                $kommando = $db->prepare($sql);
                $kommando->execute();
                $kommando->store_result();
                $kommando->bind_result($gutscheinnummer, $ausgabedatum, $praesent, $kundenname, $enddatum, $rechnungsnummer, $berechnung, $einloesungsort, $einloesedatum, $ist_abgelaufen, $ist_eingeloest,
                                       $empf_nachname, $empf_ort, $empf_land, $versanddatum);
                
                
                new_tbl('100%', 1);
                
                echo '<thead><tr>';
                echo '<th>GS-Nr.'; if($sortierung=='A.gutschein_nummer') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                echo '<th>Ausg.Datum'; if($sortierung=='A.ausgabe_datum') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                echo '<th>Pr&auml;sent'; if($sortierung=='A.gsv_praesent_prae_id') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                echo '<th>Verschenker'; if($sortierung=='A.gsv_verschenker_vers_id') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                echo '<th>Befristung'; if($sortierung=='A.end_datum') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                echo '<th>RG-Nr.'; if($sortierung=='A.rechnungs_nummer') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                echo '<th>Berechnung'; if($sortierung=='A.gsv_berechnungsart_bere_id') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                echo '<th>Einl.Ort'; if($sortierung=='A.gsv_einloesungsort_einl_id') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                echo '<th>Einl.Datum'; if($sortierung=='A.einloese_datum') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                echo '<th>Vers.-Datum'; if($sortierung=='A.wunsch_versand_datum') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                echo '<th>abgel.'; if($sortierung=='A.ist_abgelaufen') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                echo '<th>eingel.'; if($sortierung=='A.ist_eingeloest') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                //echo '<th>Name'; if($sortierung=='A.empf_nachname') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                //echo '<th>Ort'; if($sortierung=='A.empf_ort') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                //echo '<th>Land'; if($sortierung=='A.empf_land') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                //echo '<th>Vers.Datum'; if($sortierung=='A.wunsch_versand_datum') echo '<img src="images/' . $reihenfolge . '.png" align="top" />'; echo '</th>';
                echo '<th>Edit</th>';
                echo '</tr></thead>';
                
                tbl_body_open();
                
                $gs_ids = array();
                
                while($kommando->fetch()) {
                    if(!empty($enddatum)) $enddatum = date('d.m.Y', $enddatum);
                    if(!empty($einloesedatum)) $einloesedatum = date('d.m.Y', $einloesedatum);
                    if(!empty($ausgabedatum)) $ausgabedatum = date('d.m.Y', $ausgabedatum);
//                    if(!empty($versanddatum)) $versanddatum = date('d.m.Y', $versanddatum);

                    if ($_SESSION['user_name'] == 'lindbaum') {
                        if (!empty($versanddatum)) {
                            $versanddatum = $versanddatum.' '.date('d.m.Y', $versanddatum);
                        }
                    } else {
                        if (!empty($versanddatum)) {
                            $versanddatum = date('d.m.Y', $versanddatum);
                        }
                    }

                    ($ist_eingeloest == 1) ? $str_ist_eingeloest = '<img src="images/tick.png" align="top" alt="ja" />' : $str_ist_eingeloest = '';
                    ($ist_abgelaufen == 1) ? $str_ist_abgelaufen = '<img src="images/tick.png" align="top" alt="ja" />' : $str_ist_abgelaufen = '';
                    /*
                    printf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                           $gutscheinnummer, $ausgabedatum, $praesent, $kundenname, $enddatum, $rechnungsnummer, $berechnung, $einloesungsort,
                           $einloesedatum, $str_ist_abgelaufen, $str_ist_eingeloest, $empf_nachname, $empf_ort, $empf_land, $versanddatum);
                    */
                    
                    printf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                           $gutscheinnummer, $ausgabedatum, $praesent, $kundenname, $enddatum, $rechnungsnummer, $berechnung, $einloesungsort,
                           $einloesedatum, $versanddatum, $str_ist_abgelaufen, $str_ist_eingeloest, '<a href="coupons_edit.php?edit=' . $gutscheinnummer . '&redeem=' . $ist_eingeloest . '">Edit</a>');
                    
                    array_push($gs_ids, $gutscheinnummer);
                }
                
                tbl_body_close();
                
                ?>
    
                <tfoot>
                    <tr>
                        <td colspan="12"><?php printf('%s Eintr&auml;ge gefunden.', $kommando->num_rows); ?></td>
                    </tr>
                </tfoot>
    
                <?php
                
                end_tbl();
                
                echo '</div>';
                
                if($kommando->num_rows > 0) {
                    
                ?>
                
                <div class="contentBox">
                    <form name="update" id="update" method="post">
                        <table width="420" border="0">
                            <thead>
                                <tr>
                                    <th colspan="3">Multi Edit <a class="info" href="#"><img src="images/info.jpg" align="top" />
                                    <span>Die eingegebenen Werte der ausgew&auml;hlten Felder werden f&uuml;r alle obigen, gefilterten Gutscheine &uuml;bernommen!</span></a></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="col">Rechnungsnummer</th>
                                    <td><input class="text_field" type="text" name="multi_rechnungsnummer" /></td>
                                    <td><input type="checkbox" name="multi_check_rechnungsnummer" /></td>
                                </tr>
                                <tr>
                                    <th scope="col">Berechnung</th>
                                    <td>
                                        <select name="multi_berechnungsart" class="select_field">
                                        <?php
                                        
                                        $sql = "SELECT bere_id, bere_bezeichnung FROM gsv_berechnungsart WHERE bere_id NOT IN (1) ORDER BY bere_id ASC" ;
                                        
                                        $kommando = $db->prepare($sql);
                                        $kommando->execute();
                                        $kommando->store_result();
                                        $kommando->bind_result($bereid, $berechnung);
                                        
                                        while($kommando->fetch()) {
                                            printf('<option value="%s">%s</option>', $bereid, $berechnung);
                                        }
                                        
                                        ?>
                                        </select>
                                    </td>
                                    <td><input type="checkbox" name="multi_check_berechnungsart" /></td>
                                </tr>
                                
                                
                                 <tr>
                                    <th scope="col">Befristung</th>
                                    <td>
                                    <?php
                                    //get class into the page
                                    require_once("calendar/tc_calendar.php");
                                    
                                    //instantiate class and set properties
                                    $myCalendar = new tc_calendar("multi_befristung", true, false);
                                    $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                                    $myCalendar->setPath("calendar/");
                                    //$myCalendar->setYearInterval(date("Y"), date("Y", strtotime('+ 3 year')));
                                    $myCalendar->setYearInterval('2011', date("Y", strtotime('+ 3 year')));
                                    //$myCalendar->dateAllow(date("Y-m-d"), date("Y-m-d", strtotime('+ 3 year')));
                                    $myCalendar->showWeeks(true);
                                    $myCalendar->setAlignment("right", "bottom");
                                    
                                    //output the calendar
                                    $myCalendar->writeScript();	  
                                    ?>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="multi_check_befristung" />
                                        <input type="hidden" name="multi_ids" value="<?php foreach ($gs_ids as $key => $value) {echo $value .',';} ?>" />
                                    </td>
                                </tr>
                                
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3"><input type="submit" name="multiedit" class="submit_btn" value="Speichern" /></td>
                            </tfoot>
                        </table>
                        
                        <input type="hidden" name="sort_field" value="<?php print $sortierung; ?>" />
                        <input type="hidden" name="reihenfolge" value="<?php print $reihenfolge; ?>" />
                        <input type="hidden" name="srch_gsnummer_von" value="<?php print htmlspecialchars($_POST['srch_gsnummer_von']); ?>" />
                        <input type="hidden" name="srch_gsnummer_bis" value="<?php print htmlspecialchars($_POST['srch_gsnummer_bis']); ?>" />
                        <input type="hidden" name="check_gutscheinnummer" value="<?php echo !empty($_POST['check_gutscheinnummer'])?$_POST['check_gutscheinnummer']:''; ?>" />
                        <input type="hidden" name="srch_rgnummer_von" value="<?php print htmlspecialchars($_POST['srch_rgnummer_von']); ?>" />
                        <input type="hidden" name="srch_rgnummer_bis" value="<?php print htmlspecialchars($_POST['srch_rgnummer_bis']); ?>" />
                        <input type="hidden" name="check_rechnungsnummer" value="<?php echo !empty($_POST['check_rechnungsnummer'])?$_POST['check_rechnungsnummer']:''; ?>" />
                        <input type="hidden" name="srch_rgnummer_text" value="<?php print htmlspecialchars($_POST['srch_rgnummer_text']); ?>" />
                        <input type="hidden" name="check_rechnungsnummer_text" value="<?php echo !empty($_POST['check_rechnungsnummer_text'])?$_POST['check_rechnungsnummer_text']:''; ?>" />
                        <input type="hidden" name="ausgabe_von" value="<?php print $_POST['ausgabe_von']; ?>" />
                        <input type="hidden" name="ausgabe_bis" value="<?php print $_POST['ausgabe_bis']; ?>" />
                        <input type="hidden" name="check_ausgabedatum" value="<?php echo !empty($_POST['check_ausgabedatum'])?$_POST['check_ausgabedatum']:''; ?>" />
                        <input type="hidden" name="einloese_von" value="<?php print $_POST['einloese_von']; ?>" />
                        <input type="hidden" name="einloese_bis" value="<?php print $_POST['einloese_bis']; ?>" />
                        <input type="hidden" name="check_einloesedatum" value="<?php echo !empty($_POST['check_einloesedatum'])?$_POST['check_einloesedatum']:''; ?>" />
                        <input type="hidden" name="versand_von" value="<?php print $_POST['versand_von']; ?>" />
                        <input type="hidden" name="versand_bis" value="<?php print $_POST['versand_bis']; ?>" />
                        <input type="hidden" name="check_versanddatum" value="<?php print $_POST['check_versanddatum']; ?>" />
                        <input type="hidden" name="befristung_von" value="<?php print $_POST['befristung_von']; ?>" />
                        <input type="hidden" name="befristung_bis" value="<?php print $_POST['befristung_bis']; ?>" />
                        <input type="hidden" name="check_befristung" value="<?php echo !empty($_POST['check_befristung'])?$_POST['check_befristung']:''; ?>" />
                        <input type="hidden" name="praesent" value="<?php print $_POST['praesent']; ?>" />
                        <input type="hidden" name="check_praesent" value="<?php echo !empty($_POST['check_praesent'])?$_POST['check_praesent']:''; ?>" />
                        <input type="hidden" name="verschenker" value="<?php print $_POST['verschenker']; ?>" />
                        <input type="hidden" name="check_verschenker" value="<?php echo !empty($_POST['check_verschenker'])?$_POST['check_verschenker']:''; ?>" />
                        <input type="hidden" name="berechnungsart" value="<?php echo !empty($_POST['berechnungsart'])?$_POST['berechnungsart']:''; ?>" />
                        <input type="hidden" name="check_berechnungsart" value="<?php echo !empty($_POST['check_berechnungsart'])?$_POST['check_berechnungsart']:''; ?>" />
                        <input type="hidden" name="einloesungsort" value="<?php echo !empty($_POST['einloesungsort'])?$_POST['einloesungsort']:''; ?>" />
                        <input type="hidden" name="check_einloesungsort" value="<?php echo !empty($_POST['check_einloesungsort'])?$_POST['check_einloesungsort']:''; ?>" />
                        <input type="hidden" name="srch_abgelaufen" value="<?php echo !empty($_POST['srch_abgelaufen'])?$_POST['srch_abgelaufen']:''; ?>" />
                        <input type="hidden" name="check_abgelaufen" value="<?php echo !empty($_POST['check_abgelaufen'])?$_POST['check_abgelaufen']:''; ?>" />
                        <input type="hidden" name="srch_eingeloest" value="<?php print $srch_eingeloest; ?>" />
                        <input type="hidden" name="check_eingeloest" value="<?php echo !empty($_POST['check_eingeloest'])?$_POST['check_eingeloest']:''; ?>" />
                        <input type="hidden" name="srch_empf_name" value="<?php echo !empty($_POST['srch_empf_name'])?$_POST['srch_empf_name']:''; ?>" />
                        <input type="hidden" name="check_empf_name" value="<?php echo !empty($_POST['check_empf_name'])?$_POST['check_empf_name']:''; ?>" />
                        <input type="hidden" name="srch_empf_ort" value="<?php echo !empty($_POST['srch_empf_ort'])?$_POST['srch_empf_ort']:''; ?>" />
                        <input type="hidden" name="check_empf_ort" value="<?php echo !empty($_POST['check_empf_ort'])?$_POST['check_empf_ort']:''; ?>" />
                        <input type="hidden" name="srch_empf_land" value="<?php echo !empty($_POST['srch_empf_land'])?$_POST['srch_empf_land']:''; ?>" />
                        <input type="hidden" name="check_empf_land" value="<?php echo !empty($_POST['check_empf_land'])?$_POST['check_empf_land']:''; ?>" />
                    </form>
                </div>
                
                <?php
                
                }
                
            } ?>
            
        </div>
    </div>
    
    <div id="footer">
        <?php include('includes/footer.php'); ?>
    </div>
    
</div>

<?php

// Datenbank schliessen
$db->close();

// Dokument-Abschluss
setDocumentFooter();

?>