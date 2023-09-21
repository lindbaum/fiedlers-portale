<?php
/**
 *    Autor        : Ren� Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
session_start();
define('SECURE', true);
require_once('includes/dbconfig.php');

/**
 *    Abmeldevorgang
 */
if(isset($_GET['logout']))
{
    if(isset($_SESSION['user_id']) && isset($_SESSION['user_name']))
    {
        $_SESSION = array();
        session_destroy();
    }

    header('location: login.php');
    exit();
}

/**
 *    Anmeldevorgang
 */
if(isset($_POST['send'])) {
    if(isset($_POST['user_name']) && trim(htmlspecialchars($_POST['user_name'])) != '') $user_name = trim(htmlspecialchars($_POST['user_name']));
    if(isset($_POST['user_password']) && trim(htmlspecialchars($_POST['user_password'])) != '') $user_password = trim(htmlspecialchars($_POST['user_password']));
   
    if(isset($user_name) && !empty($user_name) && isset($user_password) && !empty($user_password)) {
        $query = $db->prepare('SELECT `user_id`, `user_name`, `user_level` FROM `gsv_user` WHERE `user_name` = ? AND `user_password` = ?');
        $query->bind_param('ss', $_POST['user_name'], md5($_POST['user_password']));
        $query->execute();
        $query->store_result();
        $query->bind_result($user_id, $user_name, $user_level);

        //Sind Benutzerdaten vorhanden und korrekt?
        if($query->num_rows == 1) {
            $query->fetch();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $user_name;
            $_SESSION['user_level'] = $user_level;
            header('location: index.php');
            exit();
        } else {
            $error = 'Ihre Anmeldedaten sind nicht korrekt.<br />Bitte wiederholen Sie Ihre Eingabe.';
        }
    } else {
        $error = 'Bitte f&uuml;llen Sie alle Felder korrekt aus.';
    }
} else {
    $error = NULL;
    $user_name = NULL;
}

?>
<!DOCTYPE HTML>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Fiedler Fisch- & Meeresdelikatessen | Gutscheinverwaltung v1.0</title>
<link rel="stylesheet" href="css/main.css" type="text/css" />
<link rel="stylesheet" href="css/form.css" type="text/css" />
</head>

<body>
    <div id="login">
        
        <form action="login.php" method="post" autocomplete="off">
        <table cellpadding="1" cellspacing="4">
            <thead>
                <tr>
                    <th colspan="2">Benutzeranmeldung</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>Benutzername:</th>
                    <td><input class="text_field" type="text" name="user_name" value="<?php if(isset($user_name)) echo $user_name; ?>" required="required" placeholder="Benutzername" maxlength="45" /></td>
                </tr>
                <tr>
                    <th>Passwort:</th>
                    <td><input class="text_field" type="password" name="user_password" required="required" placeholder="Passwort" maxlength="50" /></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><input class="submit_btn" type="submit" name="send" value="Login" /></td>
                </tr>
            </tfoot>
        </table>
        </form>
        
        <?php
        
        if(isset($error)) {
            printf('<div class="loginError">%s</div>', $error);
        }
        
        ?>
        
    </div>
</body>
</html>