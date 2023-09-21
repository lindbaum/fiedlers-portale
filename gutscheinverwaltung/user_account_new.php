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
    (isset($_POST['benutzername']) && trim($_POST['benutzername']) != '') ? $benutzername = trim(htmlspecialchars($_POST['benutzername'])) : $error .= 'Bitte geben Sie einen Benutzernamen an.<br />';
    (isset($_POST['pw1']) && trim($_POST['pw1']) != '') ? $pw1 = trim(htmlspecialchars($_POST['pw1'])) : $error .= 'Bitte geben Sie ein Passwort an.<br />';
    (isset($_POST['pw2']) && trim($_POST['pw2']) != '') ? $pw2 = trim(htmlspecialchars($_POST['pw2'])) : $error .= 'Bitte wiederholen Sie das eingegebene Passwort.<br />';
    if(isset($pw1) && isset($pw2) && $pw1 != $pw2) $error .= 'Die angegebenen Passw&ouml;rter stimmen nicht &uuml;berein.<br />';
    if(isset($_POST['level'])) $level = $_POST['level'];
    
    if(empty($error)) {
        $pw = md5($_POST['pw1']);
        
        $sql = "INSERT INTO gsv_user (user_name, user_password, user_level) VALUES (?, ?, ?)";

        $kommando = $db->prepare($sql);
        $kommando->bind_param('ssi', $_POST['benutzername'], $pw, $_POST['level']);
        $kommando->execute();
        
        // Variablen lšschen
        unset($benutzername);
        unset($email);
        unset($level);
        
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
                <h2>Benutzerverwaltung - Neu</h2>
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
                                <th colspan="2">Neues Benutzerkonto anlegen</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="col">Benutzername</th>
                                <td><input class="text_field" type="text" name="benutzername" placeholder="Benutzername" value="<?php if(isset($benutzername)) echo htmlspecialchars($benutzername); ?>" maxlength="45" required="required" /></td>
                            </tr>
                            <tr>
                                <th scope="col">Berechtigungs-Level</th>
                                <td>
                                    <select class="select_field" name="level">
                                        <?php
                                        
                                        for($a = 1; $a <= 3; $a++) {
                                            ($a == $level) ? printf('<option value="%s" selected>%s</option>', $a, $a) : printf('<option value="%s">%s</option>', $a, $a);
                                        }
                                        
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="col">Passwort</th>
                                <td><input class="text_field" type="password" name="pw1" placeholder="Passwort" maxlength="80" required="required" /></td>
                            </tr>
                            <tr>
                                <th scope="col">Passwort wiederholen</th>
                                <td><input class="text_field" type="password" name="pw2" placeholder="Passwort wiederholen" maxlength="80" required="required" /></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">
                                    <input class="submit_btn" type="submit" name="submit" value="Speichern" />
                                    <input type="button" name="back" class="submit_btn" value="Abbrechen" onclick="setLocation('user_account.php')" />
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </form>
            
            </div>
            
        </div>
    </div>
    
    <div id="footer">
        <?php include('includes/footer.php'); ?>
    </div>
    
</div>

<?php

// Dokument-Abschluss
setDocumentFooter();

// Datenbank schliessen und Variablen lšschen
$db->close();

?>