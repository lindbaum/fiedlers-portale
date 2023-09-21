<?php
/**
 *    Autor        : René Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
define('SECURE', true);
require_once('includes/dbconfig.php');
require_once('includes/auth.php'); // incl. session_start();

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
                <h2>Willkommen</h2>
            </div>
            
            <?php
            
            if($_SESSION['user_level'] >= 3) {
                
                echo '<div class="contentBox">';
            
                $heute = mktime(0,0,0,date('m'),date('d'),date('Y'));
               
                // Aufräumarbeiten durchführen
                // ***************************
                
                // Abgelaufene Sperrtage löschen
                $sql = "DELETE FROM gsv_sonder_versandtage WHERE sonder_datum < ?";
    
                $kommando = $db->prepare($sql);
                $kommando->bind_param('s', $heute);
                $kommando->execute();
                
                // Abgelaufene Gutscheine deaktivieren
                $sql = "UPDATE gsv_gutschein SET ist_abgelaufen=1, gsv_einloesungsort_einl_id=5 WHERE end_datum <> '' AND end_datum < ? AND ist_eingeloest=0";
    
                $kommando = $db->prepare($sql);
                $kommando->bind_param('s', $heute);
                $kommando->execute();
                
                printf('<h4>Es wurden %s abgelaufene Gutscheine ausgetragen.</h4>', $kommando->affected_rows);
                
                // Statistiken ausgeben
                // ***************************
                
                //Gutscheine zum Versand (heute)
                $sql = "SELECT gutschein_nummer FROM gsv_gutschein WHERE wunsch_versand_datum = '".$heute."'";
                
                $kommando = $db->prepare($sql);
                $kommando->execute();
                $kommando->store_result();
                $kommando->bind_result($gutscheinnummer);
                
                printf('<br /><h4>Heute stehen %s Gutscheine zum Versand an.</h4>', $kommando->num_rows);
                
                //Gutscheine eingel. via Internet
                $sql = "SELECT gutschein_nummer FROM gsv_gutschein WHERE einloese_datum = '".$heute."' AND gsv_einloesungsort_einl_id = 2";
                
                $kommando = $db->prepare($sql);
                $kommando->execute();
                $kommando->store_result();
                $kommando->bind_result($gutscheinnummer);
                
                printf('<br /><h4>Heute wurden %s Gutscheine via Internet eingel&ouml;st.</h4>', $kommando->num_rows);
                
                echo '</div>';
                
            }
            
            ?>
            
        </div>
    </div>
    
    <div id="footer">
        <?php include('includes/footer.php'); ?>
    </div>
    
</div>

<?php
setDocumentFooter();

$db->close();
?>