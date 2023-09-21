<?php
/**
 *    Autor        : Ren Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
define('SECURE', true);
require_once('includes/dbconfig.php');
require_once('includes/auth.php'); // incl. session_start();

// Funktions-Bibliothek
require_once('functions/main_functions.php');

// Dokumentkopf setzen
setDocumentHead();

// Sortierung auslesen 
$strfeld = isset($_GET['feld']) ? $_GET['feld'] : 'A.gutschein_nummer'; 
$strsort = isset($_GET['sort']) ? $_GET['sort'] : 'desc'; 
$ordnen = "asc";
$sortpic = "arrow_down.png";
    
if($strsort == "asc"){
    $sortpic = "arrow_up.png";
    $ordnen= "desc";
}  


?>

<div id="wrap">

    <div id="header">
        <h1>Startseite</h1>
        <?php include('includes/status.php'); ?>
    </div>
    
    <div id="main">
        <div id="sidebar">
            <?php include('includes/menu.php'); ?>
        </div>
        <div id="content">
            <div class="contentBox">
            <?php    
            $wert = array();
            $sql = "SELECT A.gutschein_nummer, B.bere_bezeichnung FROM gsv_gutschein A,
                    gsv_berechnungsart B WHERE A.gsv_berechnungsart_bere_id = B.bere_id
                    AND A.gutschein_nummer < 52130 ORDER BY " . $strfeld . " " . $strsort;
            
            $kommando = $db->prepare($sql);
            $kommando->execute();
            $kommando->store_result();
            $kommando->bind_result($wert[0], $wert[1]);
            
            $tbl_header = array('Gutscheinnummer', 'Bezeichnung');
            $tbl_header_sort = array('A.gutschein_nummer', 'B.bere_bezeichnung');
            
            new_tbl('400', 1);
            
            //tbl_header($tbl_header, true, true);
            print('<thead>
                    <tr>
                        <th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=A.gutschein_nummer">Gutscheinnummer</a><img src="images/' . $sortpic . '" /></th>
                        <th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=B.bere_bezeichnung">Bezeichnung</a><img src="images/' . $sortpic . '" /></th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                  </thead>');
            
            tbl_body_open();
            
            while($kommando->fetch()) {
                multi_tbl_data($kommando->field_count, $wert, true, true);
            }
            
            tbl_body_close();
            
            ?>

            <tfoot>
                <tr>
                    <td colspan="4"><?php printf('%s Eintr&auml;ge gefunden.', $kommando->num_rows); ?></td>
                </tr>
            </tfoot>

            <?php
            
            end_tbl();
            
            ?>
            </div>
        </div>
    </div>
    
    <div id="footer">
        <h3>Footer</h3>
        <?php include('includes/footer.php'); ?>
    </div>
    
</div>

<?php

// Datenbank schliessen
$db->close();

// Dokument-Abschluss
setDocumentFooter();

?>