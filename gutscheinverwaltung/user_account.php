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
                <h2>Benutzerverwaltung</h2>
            </div>
            
            <div class="contentBox">
                <form name="form_new" action="user_account_new.php" method="POST">
                    <h4>Neues Benutzerkonto anlegen</h4><br />
                    <input class="submit_btn" type="submit" name="neukunde" value="Anlegen" />
                </form>
            </div>
            
            <div class="contentBox">
            <?php
            
            $strsearch = '';    // Filterstring leeren
            $wert = array();    // neue Array fŸr Result-Werte
            
            $sql = "SELECT user_id, user_name, user_level FROM gsv_user ORDER BY user_name ASC";
            
            $kommando = $db->prepare($sql);
            $kommando->execute();
            $kommando->store_result();
            $kommando->bind_result($wert[0], $wert[1], $wert[2]);
            
            $tbl_header = array('Benutzername', 'Ber.-Stufe');
            
            new_tbl('600', 1);
            
            tbl_header($tbl_header, true, true);
            
            tbl_body_open();
            
            while($kommando->fetch()) {
                printf('<tr><td>%s</td><td>%s</td><td><a href="user_account_edit.php?edit=%s">Edit</a></td><td><a href="#" onclick="link(\'user_account_delete.php?delete=%s\');">Delete</a></td></tr>',
                       $wert[1], $wert[2], $wert[0], $wert[0]);
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