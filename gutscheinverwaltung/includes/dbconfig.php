<?php
/**
 *    Autor        : Ren� Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
defined('SECURE') or die('Keine Zugriffsberechtigung!');
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    set_magic_quotes_runtime(0);
}

/**
 *    DB-Zugangsdaten
 */
/*
$config['db_hostname'] = 'localhost';      //MySQL-Server
$config['db_username'] = 'root';           //Benutzername
$config['db_password'] = '';               //Kennwort
$config['db_database'] = 'fiedler_gsv2';   //Datenbank
*/
if ($_SERVER['SERVER_NAME'] === 'fiedlers-fischmarkt.test') {
    $config['db_hostname'] = 'localhost';//MySQL-Server
    $config['db_username'] = 'lindbaum';//Benutzername
    $config['db_password'] = 'lindbaum123'; //Kennwort
    $config['db_database'] = 'fiedler_gutscheinverwaltung';//Datenbank
} else {
    $config['db_hostname'] = 'localhost';//MySQL-Server
    $config['db_username'] = 'db-user-2';//Benutzername
    $config['db_password'] = 'Kd7jFUh6bmwPv0pcagm6'; //Kennwort
    $config['db_database'] = 'db-2';//Datenbank
}

/**
 *    Fehlerbehandlung
 */
error_reporting(E_ALL);
//ini_set('display_errors', false);


/**
 *    Verbindungsaufbau
 */
$db = new MySQLi($config['db_hostname'], $config['db_username'], $config['db_password'], $config['db_database']);

if(mysqli_connect_errno() != 0 || !$db->set_charset('utf8'))
//if(mysqli_connect_errno() != 0)
{
    die('<strong>ERROR:</strong> Es konnte keine Verbindung mit dem Datenbank-Server hergestellt werden!');
}

?>
