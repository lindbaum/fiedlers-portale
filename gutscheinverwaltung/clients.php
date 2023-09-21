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
$strfeld = isset($_GET['feld']) ? $_GET['feld'] : 'kunden_name'; 
$strsort = isset($_GET['sort']) ? $_GET['sort'] : 'asc'; 
$ordnen = "desc";
$sortpic = "arrow_up.png";
    
if($strsort == "desc"){
    $sortpic = "arrow_down.png";
    $ordnen= "asc";
}  

$srch_nummer = isset($_GET['srch_nummer']) ? $_GET['srch_nummer'] : '';
$srch_name = isset($_GET['srch_name']) ? $_GET['srch_name'] : '';

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
                <h2>Bestandskunden</h2>
            </div>
            
            <div class="contentBox">
                <form name="form_new" action="clients_new.php" method="POST">
                    <h4>Neuen Kunden anlegen</h4><br />
                    <input class="submit_btn" type="submit" name="neukunde" value="Anlegen" />
                </form>
            </div>
            
            <div class="contentBox">
                <h4>Filter</h4><br />
                <form method="get" autocomplete="off">
                <input class="text_field" type="text" name="srch_nummer" placeholder="Kundennummer" <?php if(isset($_GET['srch_nummer']) && trim($_GET['srch_nummer']) != '') echo 'value="'.htmlspecialchars($_GET['srch_nummer']).'"'; ?> />//
                <input class="text_field" type="text" name="srch_name" placeholder="Kundenname" <?php if(isset($_GET['srch_name']) && trim($_GET['srch_name']) != '') echo 'value="'.htmlspecialchars($_GET['srch_name']).'"'; ?> />
                <input class="submit_btn" type="submit" value="Filtern" />
                <input class="submit_btn" type="button" value="Alle anzeigen" onclick="setLocation('clients.php')" />
                </form>
            </div>
            
            <div class="contentBox">
            <?php
            
            $sql = "SELECT vers_id, kunden_nummer, kunden_name FROM gsv_verschenker WHERE vers_id NOT IN (1)";
            
            if(isset($_GET['srch_nummer']) && trim($_GET['srch_nummer']) != '') $sql .= " AND kunden_nummer LIKE '%" . trim($_GET['srch_nummer']) . "%'";
            if(isset($_GET['srch_name']) && trim($_GET['srch_name']) != '') $sql .= " AND kunden_name LIKE '%" . trim($_GET['srch_name']) . "%'";
            
            $sql .= " ORDER BY " . $strfeld . " " . $strsort ;
            
            $kommando = $db->prepare($sql);
            $kommando->execute();
            $kommando->store_result();
            $kommando->bind_result($versid, $kundennummer, $kundenname);
           
            new_tbl('600', 1);
            
            echo '<thead><tr>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=kunden_nummer&srch_nummer=' . $srch_nummer . '&srch_name=' . $srch_name . '">Kundennummer</a>';
            if($strfeld=='kunden_nummer') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=kunden_name&srch_nummer=' . $srch_nummer . '&srch_name=' . $srch_name . '">Kundenname</a>';
            if($strfeld=='kunden_name') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th>Edit</th>';
            echo '<th>Delete</th>';
            echo '</tr></thead>';
            
            tbl_body_open();
            
            while($kommando->fetch()) {
                printf('<tr><td>%s</td><td>%s</td><td><a href="clients_edit.php?edit=%s">Edit</a></td><td><a href="#" onclick="link(\'clients_delete.php?delete=%s\');">Delete</a></td></tr>',
                       $kundennummer, $kundenname, $versid, $versid);
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