<?php
/**
 *    Autor        : RenŽ Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
require_once('includes/auth.php'); // incl. session_start();
if($_SESSION['user_level'] >= 2) define('SECURE', true);
require_once('includes/dbconfig.php');

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
                <h2>Verschenkerliste</h2>
            </div>
            
            <div class="contentBox">
                <h4>Liste f&uuml;r Verschenker</h4><br />
                <form method="post">
                    <select class="select_field" name="verschenker">
                        <?php
                        
                        $sql = "SELECT vers_id, kunden_name FROM gsv_verschenker ORDER BY kunden_name ASC";

                        $kommando = $db->prepare($sql);
                        $kommando->execute();
                        $kommando->store_result();
                        $kommando->bind_result($vers_id, $vers_name);
                        
                        while($kommando->fetch()) {
                            (isset($_POST['verschenker']) && $_POST['verschenker'] == $vers_id) ? printf('<option value="%s" selected>%s</option>', $vers_id, $vers_name) : printf('<option value="%s">%s</option>', $vers_id, $vers_name);
                        }
                    
                        ?>
                    </select>
                    <select name="zvon" class="select_field">
                        <option value="">Ausgabezeitraum von</option>
                        <?php
                        
                        $heute = mktime(0,0,0,date('m'),date('d'),date('Y'));
                        $jahr = date('Y',$heute);
                        
                        for($a = -1; $a <= 1; $a++) {
                            for($b = 1; $b <= 12; $b++) {
                                (strlen((string)$b) < 2) ? $bb = '0'.$b : $bb = $b;
                                $vonvalue = mktime(0,0,0,$b,1,($jahr+$a));
                                ($vonvalue == $_POST['zvon']) ? printf('<option value="%s" selected>%s/%s</option>', $vonvalue, $bb, ($jahr+$a)) : printf('<option value="%s">%s/%s</option>', $vonvalue, $bb, ($jahr+$a));
                            }
                        }
                        
                        ?>
                    </select>
                    <select name="zbis" class="select_field">
                        <option value="">Ausgabezeitraum bis</option>
                        <?php
                        
                        for($a = -1; $a <= 1; $a++) {
                            for($b = 1; $b <= 12; $b++) {
                                (strlen((string)$b) < 2) ? $bb = '0'.$b : $bb = $b;
                                $maxd = date('t',mktime(0, 0, 0, $b, 1, ($jahr+$a)));
                                $bisvalue = mktime(0,0,0,$b,$maxd,($jahr+$a));
                                ($bisvalue == $_POST['zbis']) ? printf('<option value="%s" selected>%s/%s</option>', $bisvalue, $bb, ($jahr+$a)) : printf('<option value="%s">%s/%s</option>', $bisvalue, $bb, ($jahr+$a));
                            }
                        }
                        
                        ?>
                    </select>
                    <input class="submit_btn" type="submit" value="Anzeigen" />
                </form>
            </div>
            
            <?php
            
            if(isset($_POST['verschenker'])) {
                
                echo '<div class="contentBox">';
                
                $sql = "SELECT A.gutschein_nummer, B.prae_bezeichnung, A.end_datum, A.empf_nachname, A.empf_vorname, A.empf_ort, A.einloese_datum, A.ist_eingeloest, A.ist_abgelaufen
                        FROM gsv_gutschein A, gsv_praesent B
                        WHERE A.gsv_verschenker_vers_id = ? AND A.gsv_praesent_prae_id = B.prae_id AND A.ist_ausgegeben = 1";
                        
                if(isset($_POST['zvon']) && !empty($_POST['zvon'])) $sql .= " AND ausgabe_datum >= '" . $_POST['zvon'] . "'";
                if(isset($_POST['zbis']) && !empty($_POST['zbis'])) $sql .= " AND ausgabe_datum <= '" . $_POST['zbis'] . "'";
               
                $sql .= " ORDER BY gutschein_nummer ASC";
                
                $kommando = $db->prepare($sql);
                $kommando->bind_param('i', $_POST['verschenker']);
                $kommando->execute();
                $kommando->store_result();
                $kommando->bind_result($gutscheinnummer, $praesent, $enddatum, $name, $vorname, $ort, $einloesedatum, $ist_eingeloest, $ist_abgelaufen);
                
                new_tbl('100%', 1);
                
                $tbl_header = array('GS-Nr.', 'Pr&auml;sent', 'Befristung', 'Name', 'Vorname', 'Ort', 'Einl&ouml;sedatum', 'Status');
                tbl_header($tbl_header, false, false);
                tbl_body_open();
                
                while($kommando->fetch()) {
                    if(!empty($einloesedatum)) $einloesedatum = date('d.m.Y', $einloesedatum);
                    if(!empty($enddatum)) $enddatum = date('d.m.Y', $enddatum);
                    if($ist_eingeloest == 1) $status = 'eingel&ouml;st';
                    if($ist_abgelaufen == 1) $status = 'nicht eingel&ouml;st';
                    if($ist_abgelaufen == 0 && $ist_eingeloest == 0) $status = 'offen';
                    
                    printf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $gutscheinnummer, $praesent, $enddatum, $name, $vorname, $ort, $einloesedatum, $status);
                }
                
                tbl_body_close();
                ?>

                <tfoot>
                    <tr>
                        <td colspan="8"><?php printf('%s Eintr&auml;ge gefunden.', $kommando->num_rows); ?></td>
                    </tr>
                </tfoot>
    
                <?php
                
                end_tbl();
                
                echo '</div>';
                
                printf('<div class="contentBox"><a href="report_verschenker_csv.php?vid=%s&zvon=%s&zbis=%s" target="_blank">
                       <img src="images/save_csv.png" align="middle" alt="Speichern als"></a>&nbsp;CSV-Export</div>', $_POST['verschenker'], $_POST['zvon'], $_POST['zbis']);
                
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