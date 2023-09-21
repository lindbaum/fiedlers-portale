<?php
/**
 *    Autor        : Ren Kaminsky
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
                <h2>Pr&auml;sente</h2>
            </div>
            
            <div class="contentBox">
                <form name="form_new" action="gifts_new.php">
                    <h4>Neues Pr&auml;sent anlegen</h4><br />
                    <input class="submit_btn" type="submit" name="newgift" value="Anlegen" />
                </form>
            </div>
            
            <div class="contentBox">
                <h4>Filter</h4><br />
                <form method="post" autocomplete="off">
                <input class="text_field" type="text" name="srch_bez" placeholder="Bezeichnung" />&nbsp;&nbsp;<input class="submit_btn" type="submit" value="Suchen" />
                </form>
            </div>
            
            <div class="contentBox">
            <?php
            
            $strsearch = '';    // Filterstring leeren
            $wert = array();    // neue Array fŸr Result-Werte
            
            $sql = "SELECT prae_id, prae_bezeichnung FROM gsv_praesent WHERE prae_id NOT IN (1) ORDER BY prae_bezeichnung ASC";
            //$sql = "SELECT prae_id, prae_bezeichnung FROM gsv_praesent ORDER BY " . $strfeld . " " . $strsort;
            
            if(isset($_POST['srch_bez']) && trim($_POST['srch_bez']) != '') {
                $strsearch = htmlspecialchars($_POST['srch_bez']);
                //$sql = "SELECT prae_id, prae_bezeichnung FROM gsv_praesent WHERE prae_bezeichnung LIKE '%" . $strsearch . "%' ORDER BY " . $strfeld . " " . $strsort;
                $sql = "SELECT prae_id, prae_bezeichnung FROM gsv_praesent WHERE prae_bezeichnung LIKE '%" . $strsearch . "%' ORDER BY prae_bezeichnung ASC";
            }
            
            $kommando = $db->prepare($sql);
            $kommando->execute();
            $kommando->store_result();
            $kommando->bind_result($wert[0], $wert[1]);
            
            $tbl_header = array('ID', 'Bezeichnung');
            //$tbl_header_sort = array('prae_id', 'prae_bezeichnung');
            
            new_tbl('500', 1);
            
            tbl_header($tbl_header, true, true);
            
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
                // multi_tbl_data($kommando->field_count, $wert, true, true, 'gifts_edit.php');
                printf('<tr><td>%s</td><td>%s</td><td><a href="gifts_edit.php?edit=%s">Edit</a></td><td><a href="#" onclick="link(\'gifts_delete.php?delete=%s\');">Delete</a></td></tr>',
                       $wert[0], $wert[1], $wert[0], $wert[0]);
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