<?php
/**
 *    Autor        : Ren� Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
define('SECURE', true);
require_once('includes/dbconfig.php');
require_once('includes/auth.php'); // incl. session_start();

// Funktions-Bibliothek
require_once('functions/main_functions.php');

// Formular auswerten
if(isset($_POST['submit'])) {
    $error = '';
    (isset($_POST['nummer']) && preg_match('/^([0-9]{5,6})$/', $_POST['nummer'])) ? $nummer = $_POST['nummer'] : $error .= '<br />Bitte geben Sie eine korrekte Gutscheinnummer ein.';
    (isset($_POST['code']) && preg_match('/^([A-Z0-9]{12})$/', $_POST['code'])) ? $code = $_POST['code'] : $error .= '<br />Bitte geben Sie einen korrekten Code ein.';
    
    if(empty($error)) {
        // Gutschein auf G�ltigkeit pr�fen
        
        $nummer = $_POST['nummer'];
        $code = $_POST['code'];
        
        $sql = "SELECT end_datum, ist_abgelaufen FROM gsv_gutschein WHERE gutschein_nummer=? AND gen_code=?";
        
        $kommando = $db->prepare($sql);
        $kommando->bind_param('is', $nummer, $code);
        $kommando->execute();
        $kommando->store_result();
        $kommando->bind_result($enddatum, $status);
        
        if($kommando->num_rows() == 1) {
            while($kommando->fetch()) {
                if($status == 1) $error = '<img src="images/cross.png" align="top" /> Der eingegebene Gutschein ist am ' . date('d.m.Y', $enddatum) . ' abgelaufen!';
                if($status == 0) {
                    (empty($enddatum)) ? $success = '<img src="images/tick.png" align="top" /> Der eingegebene Gutschein ist ohne Begrenzung g&uuml;ltig.' : $success = '<img src="images/tick.png" align="top" /> Der eingegebene Gutschein ist bis zum ' . date('d.m.Y', $enddatum) . ' g&uuml;ltig.';
                }
            }
        } else {
            $error = '<br />Die von Ihnen eingegebene Kombination konnte nicht ermittelt werden.<br /><br />Gutscheinnummer: ' . $nummer . '<br />Code: ' . $code;
        }   
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
                <h2>Gutschein pr&uuml;fen</h2>
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
                
                <form method="post" autocomplete="off">
                    <table width="500" border="0">
                        <thead>
                            <tr>
                                <th colspan="2">Gutschein pr&uuml;fen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="col">Gutscheinnummer</th>
                                <td><input class="text_field" type="number" name="nummer" minlength="1" maxlength="10" step="1" placeholder="Gutscheinnummer" required="required" /></td>
                            </tr>
                            <tr>
                                <th scope="col">Code</th>
                                <td><input class="text_field" type="text" name="code" placeholder="Code" required="required" /></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"><input class="submit_btn" type="submit" name="submit" value="Pr&uuml;fen" /></td>
                            </tr>
                        </tfoot>
                    </table>
                </form>
                
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