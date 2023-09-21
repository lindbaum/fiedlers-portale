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

/*
// Sortierung auslesen 
$strfeld = isset($_GET['feld']) ? $_GET['feld'] : 'vers_id'; 
$strsort = isset($_GET['sort']) ? $_GET['sort'] : 'desc'; 
$ordnen = "asc";
$sortpic = "arrow_down.png";
    
if($strsort == "asc"){
    $sortpic = "arrow_up.png";
    $ordnen= "desc";
}  
*/

// Anzahl DatensŠtze
if(isset($_POST['srch_anzahl'])) $anzahl = $_POST['srch_anzahl'];
if(!isset($anzahl)) $anzahl = 10;

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
                <h2>Gutschein ausgeben</h2>
            </div>
            
            <div class="contentBox">
                <h4>Anzahl verf&uuml;gbarer Gutscheine</h4><br />
                <form method="post">
                    <input type="radio" name="srch_anzahl" value="10" <?php if(isset($anzahl) && $anzahl==10) echo 'checked '; ?>/>&nbsp;10&nbsp;&nbsp;
                    <input type="radio" name="srch_anzahl" value="50" <?php if(isset($anzahl) && $anzahl==50) echo 'checked '; ?>/>&nbsp;50&nbsp;&nbsp;
                    <input type="radio" name="srch_anzahl" value="100" <?php if(isset($anzahl) && $anzahl==100) echo 'checked '; ?>/>&nbsp;100&nbsp;&nbsp;
                    <input type="radio" name="srch_anzahl" value="all" <?php if(isset($anzahl) && $anzahl=="all") echo 'checked '; ?>/>&nbsp;Alle&nbsp;&nbsp;
                    <input class="submit_btn" type="submit" value="Anzeigen" />
                </form>
            </div>
            
            <div class="contentBox">
            <?php
            
            $strsearch = '';    // Filterstring leeren
            $wert = array();    // neue Array fŸr Result-Werte
            
            (isset($anzahl) && $anzahl != 'all') ? $limit='LIMIT ' . $anzahl : $limit=''; 
            $sql = "SELECT gutschein_nummer FROM `gsv_gutschein` WHERE ist_ausgegeben = 0 ORDER BY gutschein_nummer ASC " . $limit;
            
            $kommando = $db->prepare($sql);
            $kommando->execute();
            $kommando->store_result();
            $kommando->bind_result($wert[0]);
            
            $tbl_header = array('Gutscheinnummer');
            //$tbl_header_sort = array('prae_id', 'prae_bezeichnung');
            
            new_tbl('500', 1);
            
            tbl_header($tbl_header, true);
            
            /*
            print('<thead>
                    <tr>
                        <th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=vers_id">ID</a><img src="images/' . $sortpic . '" /></th>
                        <th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=kunden_nummer">Kundennummer</a><img src="images/' . $sortpic . '" /></th>
                        <th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=kunden_name">Kundenname</a><img src="images/' . $sortpic . '" /></th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                  </thead>');
            */
            
            tbl_body_open();
            
            while($kommando->fetch()) {
                multi_tbl_data($kommando->field_count, $wert, true, false, 'coupons_prepare_edit.php');
            }
            
            tbl_body_close();
            
            ?>

            <tfoot>
                <tr>
                    <td colspan="5"><?php printf('%s Eintr&auml;ge gefunden.', $kommando->num_rows); ?></td>
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