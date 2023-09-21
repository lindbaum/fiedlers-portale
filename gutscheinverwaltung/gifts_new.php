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
    (isset($_POST['bezeichnung']) && trim($_POST['bezeichnung']) != '') ? $bezeichnung = trim(htmlspecialchars($_POST['bezeichnung'])) : $error .= '<br />Bitte geben Sie eine Bezeichnung an.';
    if(isset($_POST['gewicht']) && trim($_POST['gewicht']) != '') $gewicht = trim(htmlspecialchars($_POST['gewicht']));
    
    if(empty($error)) {
        $sql = "INSERT INTO gsv_praesent (prae_bezeichnung, prae_gewicht) VALUES (?,?)";

        $kommando = $db->prepare($sql);
        $kommando->bind_param('ss', $_POST['bezeichnung'], $_POST['gewicht']);
        $kommando->execute();
        
        // Variablen lšschen
        unset($bezeichnung);
        unset($gewicht);
        
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
                <h2>Pr&auml;sente - Neu</h2>
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
                                <th colspan="2">Neues Pr&auml;sent anlegen</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="col">Bezeichnung</th>
                                <td><input class="text_field" type="text" name="bezeichnung" placeholder="Bezeichnnung" value="<?php if(isset($bezeichnung)) echo htmlspecialchars($bezeichnung); ?>" maxlength="100" required="required" /></td>
                            </tr>
                            <tr>
                                <th scope="col">Gewicht</th>
                                <td><input class="text_field" type="text" name="gewicht" placeholder="Gewicht" value="<?php if(isset($gewicht)) echo htmlspecialchars($gewicht); ?>" maxlength="25" /></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <td colspan="2">
                                <input class="submit_btn" type="submit" name="submit" value="Speichern" />
                                <input type="button" name="back" class="submit_btn" value="Abbrechen" onclick="setLocation('gifts.php')" />
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

// Datenbank schliessen
$db->close();

?>