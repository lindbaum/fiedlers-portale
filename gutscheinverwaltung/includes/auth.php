<?php
/**
 *    Autor        : RenŽ Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user_name'])) {
    //die('Sie sind nicht angemeldet! <a href="login.php">[Login]</a>');
    header('Location: login.php');
    exit();
}

?>