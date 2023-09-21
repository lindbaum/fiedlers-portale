<?php
/**
 *    Autor        : RenŽ Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
require_once('includes/auth.php'); // incl. session_start();
if($_SESSION['user_level'] >= 3) define('SECURE', true);
require_once('includes/dbconfig.php');
define("L_LANG", "de_DE"); // Kalender-Sprache (DE)

// Funktions-Bibliothek
require_once('functions/main_functions.php');

if(isset($_POST['zeitraum'])) {
    $error = '';
    $success = '';
    (isset($_POST['date1']) && trim($_POST['date1']) != '' && $_POST['date1'] != '0000-00-00') ? $starttag = strtotime($_POST['date1']) : $error .= 'Bitte einen Starttag angeben.<br />';
    (isset($_POST['date2']) && trim($_POST['date2']) != '' && $_POST['date2'] != '0000-00-00') ? $endtag = strtotime($_POST['date2']) : $error .= 'Bitte einen Endtag angeben.<br />';
    if($_POST['date1'] >= $_POST['date2']) $error .= 'Bitte einen Starttag angeben, der nach dem Endtag liegt.<br />';
    
    if(empty($error)) {
        
        while($starttag <= $endtag) {
            $sql = "INSERT INTO gsv_sonder_versandtage (sonder_datum, ist_versand) VALUES (?, 0)
                    ON DUPLICATE KEY UPDATE ist_versand = 0";
    
            $kommando = $db->prepare($sql);
            $kommando->bind_param('s', $starttag);
            $kommando->execute();
            
            if($kommando->affected_rows == 1) $success .= '<img src="images/tick.png" align="top" />&nbsp;Datensatz wurde erfolgreich gespeichert.';
            if($kommando->error) $error .= '<img src="images/cross.png" align="top" />&nbsp;Beim Speichern der Daten ist ein Fehler aufgetreten!';
            
            //$starttag = strtotime(date('d.m.Y', mktime(0, 0, 0, date('m', $starttag), date('d', $starttag) + 1)));
            $starttag = $starttag + (24 * 60 * 60);
        }
    }
}

if(isset($_POST['einzeltag'])) {
    $error = '';
    (isset($_POST['date3']) && trim($_POST['date3']) != '' && $_POST['date3'] != '0000-00-00') ? $einzaltag = strtotime($_POST['date3']) : $error .= 'Bitte einen Tag angeben.<br />';
    
    if(empty($error)) {
        $sql = "INSERT INTO gsv_sonder_versandtage (sonder_datum, ist_versand) VALUES (?, 0)
                ON DUPLICATE KEY UPDATE ist_versand = 0";

        $kommando = $db->prepare($sql);
        $kommando->bind_param('s', $einzaltag);
        $kommando->execute();
        
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
                <h2>Versandtage sperren</h2>
            </div>
            
            <?php
        
            if(isset($error) && !empty($error)) { 
                printf('<div class="contentBoxRed">%s</div>', $error);
            }
            
            //get class into the page
                    require_once("calendar/tc_calendar.php");
                    
            ?>
            
            <div class="contentBox">
                <h4>Zeitraum sperren</h4><br />
                <form method="post">
                    
                    <?php
                    //get class into the page
                    require_once("calendar/tc_calendar.php");
                    
                    $myCalendar = new tc_calendar("date1", true, false);
                    $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                    
                    $myCalendar->setPath("calendar/");
                    $myCalendar->setYearInterval(date("Y"), date("Y", strtotime('+ 5 year')));
                    $myCalendar->setAlignment("left", "bottom");
                    $myCalendar->setDatePair("date1", "date2");
                    $myCalendar->dateAllow(date("Y-m-d"), date("Y-m-d", strtotime('+ 5 year')));
                    $myCalendar->zindex = 10;
                    $myCalendar->writeScript();
                    
                    $myCalendar = new tc_calendar("date2", true, false);
                    $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                    
                    $myCalendar->setPath("calendar/");
                    $myCalendar->setYearInterval(date("Y"), date("Y", strtotime('+ 5 year')));
                    $myCalendar->dateAllow(date("Y-m-d"), date("Y-m-d", strtotime('+ 5 year')));
                    $myCalendar->setAlignment("right", "bottom");
                    $myCalendar->setDatePair("date1", "date2");
                    $myCalendar->writeScript();
                    ?>
                    &nbsp;&nbsp;<input class="submit_btn" type="submit" name="zeitraum" value="Speichern" />
                </form>
            </div>
            
            <div class="contentBox">
                <h4>Tag sperren</h4><br />
                <form method="post">
                    
                    <?php
                    
                    //instantiate class and set properties
                    $myCalendar = new tc_calendar("date3", true, false);
                    $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                    $myCalendar->setPath("calendar/");
                    $myCalendar->setYearInterval(date("Y"), date("Y", strtotime('+ 5 year')));
                    $myCalendar->zindex = 9;
                    $myCalendar->dateAllow(date("Y-m-d"), date("Y-m-d", strtotime('+ 5 year')));
                    $myCalendar->showWeeks(true);
                    $myCalendar->setAlignment("left", "bottom");
                    
                    //output the calendar
                    $myCalendar->writeScript();	  
                    ?>
                    
                    &nbsp;&nbsp;<input class="submit_btn" type="submit" name="einzeltag" value="Speichern" />
                </form>
            </div>
            
            <div class="contentBox">
            <?php
            $heute = mktime(0,0,0,date('m'),date('d'),date('Y'));
            $sql = "SELECT sonder_datum FROM gsv_sonder_versandtage WHERE sonder_datum >= " . $heute . " ORDER BY sonder_datum ASC";
            
            $kommando = $db->prepare($sql);
            $kommando->execute();
            $kommando->store_result();
            $kommando->bind_result($datum);
            
            $tbl_header = array('Datum');
            
            new_tbl('600', 1);
            
            tbl_header($tbl_header, false, true);
            
            tbl_body_open();
            
            $letzter_monat = '';
            while($kommando->fetch()) {
                $wochentage = array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');
                $monate = array('', 'Januar', 'Februar', 'M&auml;rz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
                
                $wday = date('w', $datum);
                $month = date('n', $datum);
                $format_datum = date('d.m.Y', $datum) . ', ' . $wochentage[$wday];
                
                //multi_tbl_data($kommando->field_count, $datum, false, true, '', 'deltag.php');
                // Monat als Headline setzen
                $aktueller_monat = date('n', $datum);
                if($aktueller_monat != $letzter_monat) printf('<tr><th colspan="2"><b>%s %s</b></th></tr>', $monate[$aktueller_monat], date('Y', $datum));
                $letzter_monat = $aktueller_monat;
                printf('<tr><td>%s</td><td><a href="shipping_days_special_delete.php?delete=%s">Delete</a></td></tr>', $format_datum, $datum);
            }
            
            tbl_body_close();
            
            ?>

            <tfoot>
                <tr>
                    <td colspan="2"><?php printf('%s Eintr&auml;ge gefunden.', $kommando->num_rows); ?></td>
                </tr>
            </tfoot>

            <?php
            
            end_tbl();
            
            ?>
            </div>
            
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