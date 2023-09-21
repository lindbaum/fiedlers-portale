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
    (isset($_POST['nummer']) && preg_match('/^([0-9]{5})$/', $_POST['nummer'])) ? header('Location:coupons_redeem_edit.php?edit=' . $_POST['nummer']) : $error .= '<br />Bitte geben Sie eine korrekte Gutscheinnummer an.'; 
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
            
            ?>
            
            <div class="contentBox">
                <h4>Auswahl Gutschein</h4><br />
                <form method="post" autocomplete="off">
                    <input type="number" class="text_field" name="nummer" placeholder="Gutscheinnummer" reqired="required" />
                    <input class="submit_btn" type="submit" name="submit" value="OK" />
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