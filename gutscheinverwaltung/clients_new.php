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
    if(isset($_POST['kundennummer'])) $kundennummer = trim(htmlspecialchars($_POST['kundennummer']));
    (isset($_POST['kundenname']) && trim($_POST['kundenname']) != '') ? $kundenname = trim(htmlspecialchars($_POST['kundenname'])) : $error .= '<br />Bitte geben Sie einen Kundennamen an.';
    
    if(empty($error)) {
        $sql = "INSERT INTO gsv_verschenker (kunden_nummer, kunden_name) VALUES (?, ?)";

        $kommando = $db->prepare($sql);
        $kommando->bind_param('ss', $_POST['kundennummer'], $_POST['kundenname']);
        $kommando->execute();
        
        // Variablen lšschen
        unset($kundenname);
        unset($kundennummer);
        
        if($kommando->affected_rows == 1) $success = '<img src="images/tick.png" align="top" />&nbsp;Datensatz wurde erfolgreich gespeichert.';
        if($kommando->error) $error = '<img src="images/cross.png" align="top" />&nbsp;Beim Speichern der Daten ist ein Fehler aufgetreten!';
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
                <h2>Bestandskunden - Neu</h2>
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
                    <table width="400" border="0">
                        <thead>
                            <tr>
                                <th colspan="2">Neuen Kunden anlegen</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="col">Kundennummer</th>
                                <td><input class="text_field" type="text" name="kundennummer" placeholder="Kundennummer" value="<?php if(isset($kundennummer)) echo htmlspecialchars($kundennummer); ?>" maxlength="25" /></td>
                            </tr>
                            <tr>
                                <th scope="col">Kundenname</th>
                                <td><input class="text_field" type="text" name="kundenname" placeholder="Kundenname" value="<?php if(isset($kundenname)) echo htmlspecialchars($kundenname); ?>" maxlength="100" required="required" /></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <td colspan="2">
                                <input class="submit_btn" type="submit" name="submit" value="Speichern" />
                                <input type="button" name="back" class="submit_btn" value="Abbrechen" onclick="setLocation('clients.php')" />
                            </td>
                        </tfoot>
                    </table>
                </form>
            
            </div>
            
        </div>
    </div>
    
    <div id="footer">
        <h3>Footer</h3>
        <?php include('includes/footer.php'); ?>
    </div>
    
</div>

<?php

// Dokument-Abschluss
setDocumentFooter();

// Datenbank schliessen und Variablen lšschen
$db->close();

?>