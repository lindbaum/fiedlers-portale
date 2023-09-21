<?php
/**
 *    Autor        : RenŽ Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
require_once('includes/auth.php'); // incl. session_start();
if($_SESSION['user_level'] >= 3) define('SECURE', true);
require_once('includes/dbconfig.php');

// Funktions-Bibliothek
require_once('functions/main_functions.php');

if(isset($_POST['submit'])) {
    $error = '';
    if(!isset($_POST['nummer_von']) || empty($_POST['nummer_von'])) $error .= 'Bitte geben Sie eine Gutscheinnummer f&uuml;r das "von"-Feld an.<br />';
    else (isset($_POST['nummer_von']) && preg_match('/^([0-9]{5})$/', $_POST['nummer_von'])) ? $nummer_von = $_POST['nummer_von'] : $error .= 'Bitte geben Sie eine korrekte Gutscheinnummer f&uuml;r das "von"-Feld an.<br />'; 
    if(isset($_POST['nummer_bis']) && !empty($_POST['nummer_bis'])) (preg_match('/^([0-9]{5})$/', $_POST['nummer_bis'])) ? $nummer_bis = $_POST['nummer_bis'] : $error .= 'Bitte geben Sie eine korrekte Gutscheinnummer f&uuml;r das "bis"-Feld an.<br />';
    if(isset($nummer_bis) && !isset($nummer_von)) $error .= 'Bitte geben Sie einen Startwert an.<br />';
    if(isset($nummer_bis) && isset($nummer_von) && ($nummer_bis <= $nummer_von)) $error .= 'Der Endwert ist gr&ouml;sser oder gleich dem Startwert.<br />';
}

if(isset($_POST['reset'])) {
    if(isset($_POST['reset_ids']) && !empty($_POST['reset_ids'])) $reset_ids = $_POST['reset_ids'];
    if(isset($reset_ids) && !empty($reset_ids)) {
        $reset_ids = substr($reset_ids, 0, -1);
        
        // Gutscheine auf NULL setzen
        $sql = "UPDATE gsv_gutschein
                SET end_datum=NULL,
                rechnungs_nummer=NULL,
                gsv_berechnungsart_bere_id=1,
                gsv_praesent_prae_id=1,
                gsv_verschenker_vers_id=1,
                empf_anrede=NULL,
                empf_nachname=NULL,
                empf_vorname=NULL,
                empf_strasse=NULL,
                empf_hausnummer=NULL,
                empf_plz=NULL,
                empf_ort=NULL,
                empf_land=NULL,
                empf_email=NULL,
                empf_telefon=NULL,
                wunsch_versand_datum=NULL,
                gsv_einloesungsort_einl_id=1,
                ausgabe_datum=NULL,
                ist_ausgegeben=0
                WHERE gutschein_nummer IN (" . $reset_ids . ")";
         
        $kommando = $db->prepare($sql);
        $kommando->execute();
        
        if($kommando->error) $error = '<img src="images/cross.png" align="top" />&nbsp;Beim Aktualisieren der Daten ist ein Fehler aufgetreten!';
        else $success = '<img src="images/tick.png" align="top" />&nbsp;Datens&auml;tze wurden erfolgreich aktualisiert.';
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
                <h2>Gutschein zur&uuml;cksetzen</h2>
            </div>
            
            <?php
        
            if(isset($error) && !empty($error)) { 
                printf('<div class="contentBoxRed">%s</div>', $error);
            }
            
            if(isset($success) && !empty($success)) { 
                printf('<div class="contentBox">%s</div>', $success);
            }
            
            ?>
            
            <div class="contentBox">
                <h4>Auswahl Gutschein(e)</h4><br />
                <form method="post" autocomplete="off">
                    <input type="text" class="text_field" name="nummer_von" placeholder="Gutscheinnummer" value="<?php if(isset($nummer_von)) echo $nummer_von; ?>" />
                    <span>( bis</span>&nbsp;<input type="text" class="text_field" name="nummer_bis" placeholder="Gutscheinnummer" value="<?php if(isset($nummer_bis)) echo $nummer_bis; ?>" /><span>)</span>
                    <input class="submit_btn" type="submit" name="submit" value="OK" />
                </form>
            </div>
            
            <?php
        
            if(isset($_POST['submit']) && empty($error)) { 
            
                ?>
                
                <div class="contentBox">
                    
                    <?php
                    
                    $sql = "SELECT A.gutschein_nummer, A.ausgabe_datum, B.prae_bezeichnung, C.kunden_name, A.end_datum, A.rechnungs_nummer, D.bere_bezeichnung, E.einl_bezeichnung, A.einloese_datum, A.ist_abgelaufen, A.ist_eingeloest 
                            FROM gsv_gutschein A, gsv_praesent B, gsv_verschenker C, gsv_berechnungsart D, gsv_einloesungsort E 
                            WHERE ist_ausgegeben = 1 
                            AND A.gsv_praesent_prae_id = B.prae_id
                            AND A.gsv_verschenker_vers_id = C.vers_id
                            AND A.gsv_berechnungsart_bere_id = D.bere_id
                            AND A.gsv_einloesungsort_einl_id = E.einl_id";
                    
                    (isset($nummer_bis)) ? $sql .= " AND gutschein_nummer >= '" . $nummer_von . "' AND gutschein_nummer <= '" . $nummer_bis . "' " : $sql .= " AND gutschein_nummer = '" . $nummer_von . "' ";
                    
                    $sql .= " ORDER BY gutschein_nummer ASC";
                    
                    $kommando = $db->prepare($sql);
                    $kommando->execute();
                    $kommando->store_result();
                    $kommando->bind_result($gutscheinnummer, $ausgabedatum, $praesent, $kundenname, $enddatum, $rechnungsnummer, $berechnung, $einloesungsort, $einloesedatum, $ist_abgelaufen, $ist_eingeloest);
                    
                    new_tbl('100%', 1);
                    $tbl_header = array('GS-Nr.', 'Pr&auml;sent', 'Verschenker', 'Befristung', 'RG-Nr.', 'Berechnung', 'eingel&ouml;st');
                    tbl_header($tbl_header, false, false);
                    tbl_body_open();
                    
                    $reset_nummers = array();
                    $reset_nummers_errors = array();
                    
                    while($kommando->fetch()) {
                        ($ist_eingeloest == 1) ? $str_ist_eingeloest = '<img src="images/tick.png" align="top" alt="ja" />' : $str_ist_eingeloest = '';
                        if($ist_eingeloest != 1) {
                            printf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                            $gutscheinnummer, $praesent, $kundenname, $enddatum, $rechnungsnummer, $berechnung, $str_ist_eingeloest);
                            array_push($reset_nummers, $gutscheinnummer);
                        } else {
                            printf('<tr><td class="fold">%s</td><td class="fold">%s</td><td class="fold">%s</td><td class="fold">%s</td><td class="fold">%s</td><td class="fold">%s</td><td class="fold">%s</td></tr>',
                            $gutscheinnummer, $praesent, $kundenname, $enddatum, $rechnungsnummer, $berechnung, $str_ist_eingeloest);
                            array_push($reset_nummers_errors, $gutscheinnummer);
                        }
                    }
                    
                    tbl_body_close();
        
                    echo '<tfoot><tr><td colspan="7"><form method="post"><input type="hidden" name="reset_ids" value="';
                                
                    for($x = 0; $x < count($reset_nummers); $x++) {
                        echo $reset_nummers[$x] . ","; 
                    }
                                
                    echo '" />';
                                
                    if($kommando->num_rows > 0) echo '<input type="submit" class="submit_btn" name="reset" value="Ja, zur&uuml;cksetzen" />';
                    else echo 'Keine ausgegebenen Gutscheine gefunden!';
                                
                    echo '</form></td></tr></tfoot>';
                        
                    end_tbl();
                    
                    // Gutscheine anzeigen, die bereits eingelšst wurden
                    if(count($reset_nummers_errors) > 0) {
                        echo '<br /><div class="contentBox"><h4>Achtung</h4><br /><span>Folgende Gutscheine k&ouml;nnen nicht mehr zur&uuml;ckgesetzt werden, da sie bereits eingel&ouml;st wurden:<br />';
                        for($x = 0; $x < count($reset_nummers_errors); $x++) {
                            echo "<br />Nr. " . $reset_nummers_errors[$x]; 
                        }
                        echo '</div>';
                    }
                    
                    ?>
                    
                </div>
                
                <?php
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