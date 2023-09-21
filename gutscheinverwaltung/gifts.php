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

// Sortierung auslesen 
$strfeld = isset($_GET['feld']) ? $_GET['feld'] : 'prae_bezeichnung'; 
$strsort = isset($_GET['sort']) ? $_GET['sort'] : 'asc'; 
$ordnen = "desc";
$sortpic = "arrow_up.png";
    
if($strsort == "desc"){
    $sortpic = "arrow_down.png";
    $ordnen= "asc";
}  

$srch_bez = isset($_GET['srch_bez']) ? $_GET['srch_bez'] : '';

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
                <form method="get" autocomplete="off">
                <input class="text_field" type="text" name="srch_bez" placeholder="Bezeichnung" <?php if(isset($_GET['srch_bez']) && trim($_GET['srch_bez']) != '') echo 'value="'.htmlspecialchars($_GET['srch_bez']).'"'; ?> />
                <input class="submit_btn" type="submit" value="Filtern" />
                <input class="submit_btn" type="button" value="Alle anzeigen" onclick="setLocation('gifts.php')" />
                </form>
            </div>
            
            <div class="contentBox">
            <?php
            
            $sql = "SELECT prae_id, prae_bezeichnung, prae_gewicht FROM gsv_praesent WHERE prae_id NOT IN (1)";
            
            if(isset($_GET['srch_bez']) && trim($_GET['srch_bez']) != '') $sql .= " AND prae_bezeichnung LIKE '%" . trim($_GET['srch_bez']) . "%'";
            
            $sql .= " ORDER BY " . $strfeld . " " . $strsort ;
            
            $kommando = $db->prepare($sql);
            $kommando->execute();
            $kommando->store_result();
            $kommando->bind_result($praeid, $praesent, $gewicht);
           
            new_tbl('600', 1);
            
            echo '<thead><tr>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=prae_bezeichnung&srch_bez=' . $srch_bez . '">Pr&auml;sent</a>';
            if($strfeld=='prae_bezeichnung') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=prae_gewicht&srch_bez=' . $srch_bez . '">Gewicht</a>';
            if($strfeld=='prae_gewicht') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th>Edit</th>';
            echo '<th>Delete</th>';
            echo '</tr></thead>';
            
            tbl_body_open();
            
            while($kommando->fetch()) {
                printf('<tr><td>%s</td><td>%s</td><td><a href="gifts_edit.php?edit=%s">Edit</a></td><td><a href="#" onclick="link(\'gifts_delete.php?delete=%s\');">Delete</a></td></tr>',
                       $praesent, $gewicht, $praeid, $praeid);
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
        <?php include('includes/footer.php'); ?>
    </div>
    
</div>

<?php

// Datenbank schliessen
$db->close();

// Dokument-Abschluss
setDocumentFooter();

?>