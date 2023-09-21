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

// ParamterŸbergabe
if(isset($_GET['delete']) && preg_match('/^[0-9]{10}$/', $_GET['delete'])) $delete = $_GET['delete'];

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
            
            <?php
            
            if(isset($delete)) {
                // Alles gut
                $sql = "DELETE FROM gsv_sonder_versandtage WHERE sonder_datum = ?";
                
                $kommando = $db->prepare($sql);
                $kommando->bind_param('i', $delete);
                $kommando->execute();
                
                if($kommando->error) print('<div class="contentBoxRed">Beim L&ouml;schen der Daten ist ein Fehler aufgetreten!</div>');
                if($kommando->affected_rows == 1) print('<div class="contentBox"><img src="images/tick.png" align="top" alt="OK" />&nbsp;Datensatz erfolgreich gel&ouml;scht!</div>');
            } else {
                print('<div class="contentBoxRed">Keine g&uuml;ltige ID gesetzt.</div>');
            }
            
            print('<div class="contentBox"><a href="shipping_days_special.php">Zur&uuml;ck</a></div>');
            
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