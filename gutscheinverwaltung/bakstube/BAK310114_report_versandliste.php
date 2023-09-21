<?php
/**
 *    Autor        : RenŽ Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
require_once('includes/auth.php'); // incl. session_start();
if($_SESSION['user_level'] >= 2) define('SECURE', true);
require_once('includes/dbconfig.php');
define("L_LANG", "de_DE"); // Kalender-Sprache (DE)

// Funktions-Bibliothek
require_once('functions/main_functions.php');

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
                <h2>Versandliste f&uuml;r Easylog</h2>
            </div>
            
            <div class="contentBox">
                <h4>Versandtag ausw&auml;hlen</h4><br />
                <form method="post">
                    <?php
                    require_once("calendar/tc_calendar.php");
                    
                    //instantiate class and set properties
                    $myCalendar = new tc_calendar("date1", true, false);
                    $myCalendar->setIcon("calendar/images/iconCalendar.gif");
                    $myCalendar->setPath("calendar/");
                    if(isset($_POST['date1'])) $myCalendar->setDate(date("d", strtotime($_POST['date1'])), date("m", strtotime($_POST['date1'])), date("Y", strtotime($_POST['date1'])));
                    $myCalendar->setYearInterval(date("Y", strtotime('- 1 year')), date("Y", strtotime('+ 3 year')));
                    //$myCalendar->dateAllow(date("Y-m-d"), date("Y-m-d", strtotime('+ 3 year')));
                    $myCalendar->showWeeks(true);
                    $myCalendar->setAlignment("left", "bottom");
                    
                    //output the calendar
                    $myCalendar->writeScript();	  
                    ?>
                    <input class="submit_btn" type="submit" value="Anzeigen" />
                </form>
            </div>
            
            <?php
            
            if(isset($_POST['date1']) && $_POST['date1'] != '0000-00-00') {
                
                $datum = strtotime($_POST['date1']);
                
                echo '<div class="contentBox">';
                
                $sql = "SELECT A.empf_anrede, A.empf_vorname, A.empf_nachname, A.empf_firma, A.empf_strasse, A.empf_hausnummer,
                        A.empf_plz, A.empf_ort, A.empf_land, A.empf_email, A.empf_telefon, A.gutschein_nummer, B.prae_bezeichnung, B.prae_gewicht, A.einloese_datum, A.wunsch_versand_datum
                        FROM gsv_gutschein A, gsv_praesent B
                        WHERE A.gsv_praesent_prae_id = B.prae_id
                        AND A.wunsch_versand_datum = ?
                        ORDER BY A.einloese_datum ASC, A.gutschein_nummer ASC";
                
                $kommando = $db->prepare($sql);
                $kommando->bind_param('i', $datum);
                $kommando->execute();
                $kommando->store_result();
                $kommando->bind_result($anrede, $vorname, $nachname, $firma, $strasse, $hnr, $plz, $ort, $land, $email, $telefon, $gsnummer, $praesent, $gewicht, $einloesedatum, $versanddatum);
                
                new_tbl('100%', 1);
                
                $tbl_header = array('Anrede', 'Vorname', 'Name', 'Firma', 'Strasse', 'Nr.', 'PLZ', 'Ort', 'Land', 'Email', 'Telefon', 'GS-Nr.', 'Pr&auml;sent', 'Gewicht', 'Einl.-Datum', 'Vers.-Datum');
                tbl_header($tbl_header, false, false);
                tbl_body_open();
                
                while($kommando->fetch()) {
                    printf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                           $anrede, $vorname, $nachname, $firma, $strasse, $hnr, $plz, $ort, $land, $email, $telefon, $gsnummer, $praesent, $gewicht, date('d.m.Y', $einloesedatum), date('d.m.Y', $versanddatum));
                }
                
                tbl_body_close();
                ?>

                <tfoot>
                    <tr>
                        <td colspan="16"><?php printf('%s Eintr&auml;ge gefunden.', $kommando->num_rows); ?></td>
                    </tr>
                </tfoot>
    
                <?php
                
                end_tbl();
                
                echo '</div>';
                
                printf('<div class="contentBox"><a href="report_versandliste_csv.php?date=%s" target="_blank"><img src="images/save_csv.png" align="middle" alt="Speichern als"></a>&nbsp;CSV-Export</div>', $datum);
                
            }
            
            ?>
            
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