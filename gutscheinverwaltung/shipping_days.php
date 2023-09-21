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
                <h2>Standard Versandtage</h2>
            </div>
            
            <div class="contentBox">
                <?php
                
                if(isset($_POST['submit'])) {
                    for($a = 1; $a <= 7; $a++) {
                        $checked = isset($_POST['wtag'][$a]) ? '1':'0';
                        
                        $sql = "UPDATE gsv_standard_versandtage SET ist_standard=? WHERE wochentag=?";
                        $kommando = $db->prepare($sql);
                        $kommando->bind_param('ii', $checked, $a);
                        $kommando->execute();
                        
                        /*
                        if($kommando->affected_rows == 1) $success = '<img src="images/tick.png" align="top" />&nbsp;Datensatz wurde erfolgreich gespeichert.';
                        else $error = '<img src="images/cross.png" align="top" />&nbsp;Beim Speichern der Daten ist ein Fehler aufgetreten!';
                        */
                    }
                }
            
                if(isset($error) && !empty($error)) { 
                    printf('<div class="contentBoxRed">Es ist ein Fehler aufgetreten:%s</div>', $error);
                }
                
                if(isset($success) && !empty($success)) { 
                    printf('<div class="contentBox">%s</div>', $success);
                }
                
                $wochentage = array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');
                $sql = "SELECT * FROM gsv_standard_versandtage ORDER BY wochentag";
                
                $kommando = $db->prepare($sql);
                $kommando->execute();
                $kommando->store_result();
                $kommando->bind_result($wert[0], $wert[1]);
                
                $tbl_header = array('Wochentag', 'Standard');
                
                echo '<form method="post">';
                
                new_tbl('600', 1);
                tbl_header($tbl_header, false, false);
                tbl_body_open();
                
                while($kommando->fetch()) {
                    // multi_tbl_data($kommando->field_count, $wert, true, true);
                    if($wert[1] == 1) printf('<tr><td>%s</td><td><input type="checkbox" name="wtag[%s]" checked="checked" /></td></tr>', $wochentage[$wert[0]], $wert[0]);
                    else printf('<tr><td>%s</td><td><input type="checkbox" name="wtag[%s]" /></td></tr>', $wochentage[$wert[0]], $wert[0]);;
                }
                
                tbl_body_close();
                
                echo '<tfoot><tr><td colspan="2"><input type="submit" class="submit_btn" name="submit" value="Speichern" /></td></tr></tfoot>';
                
                end_tbl();
                
                echo '</form>';
                
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