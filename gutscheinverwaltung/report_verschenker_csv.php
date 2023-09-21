<?php
/**
 *    Autor        : RenŽ Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
require_once('includes/auth.php'); // incl. session_start();
if($_SESSION['user_level'] >= 2) define('SECURE', true);
require_once('includes/dbconfig.php');

if(isset($_GET['vid']) && preg_match('/^[0-9]+$/', $_GET['vid'])) {

    $sql = "SELECT A.gutschein_nummer, B.prae_bezeichnung, A.end_datum, A.empf_nachname, A.empf_vorname, A.empf_ort, A.einloese_datum, A.ist_eingeloest, A.ist_abgelaufen
            FROM gsv_gutschein A, gsv_praesent B
            WHERE A.gsv_verschenker_vers_id = ? AND A.gsv_praesent_prae_id = B.prae_id";
    
    if(isset($_GET['zvon']) && !empty($_GET['zvon'])) $sql .= " AND ausgabe_datum >= '" . $_GET['zvon'] . "'";
    if(isset($_GET['zbis']) && !empty($_GET['zbis'])) $sql .= " AND ausgabe_datum <= '" . $_GET['zbis'] . "'";
   
    $sql .= " ORDER BY gutschein_nummer ASC";
    
    $kommando = $db->prepare($sql);
    $kommando->bind_param('i', $_GET['vid']);
    $kommando->execute();
    $kommando->store_result();
    $kommando->bind_result($gutscheinnummer, $praesent, $enddatum, $name, $vorname, $ort, $einloesedatum, $ist_eingeloest, $ist_abgelaufen);
    
    $csv_output = "GS-Nr.; Praesent; Befristung; Name; Vorname; Ort; Einloesedatum; Status; \n";
    
    while($kommando->fetch()) {
        if(!empty($einloesedatum)) $einloesedatum = date('d.m.Y', $einloesedatum);
        if(!empty($enddatum)) $enddatum = date('d.m.Y', $enddatum);
        if($ist_eingeloest == 1) $status = 'eingel.';
        if($ist_abgelaufen == 1) $status = 'nicht eingel.';
        if($ist_abgelaufen == 0 && $ist_eingeloest == 0) $status = 'offen';
        
        $csv_output .= $gutscheinnummer."; ".$praesent."; ".$enddatum."; ".$name."; ".$vorname."; ".$ort."; ".$einloesedatum."; ".$status."; ";
        $csv_output .= "\n";
    }
    
    $filename = "verschenker_".date("d-m-Y",time());
    header("Content-type: application/vnd.ms-excel");
    header("Content-disposition: csv" . date("d.m.Y") . ".csv");
    header("Content-disposition: filename=".$filename.".csv");
    print $csv_output;
    
    // Datenbank schliessen
    $db->close();
}

?>