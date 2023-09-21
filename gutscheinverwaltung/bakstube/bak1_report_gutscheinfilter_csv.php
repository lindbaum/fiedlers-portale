<?php
/**
 *    Autor        : RenŽ Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
require_once('includes/auth.php'); // incl. session_start();
if($_SESSION['user_level'] >= 2) define('SECURE', true);
require_once('includes/dbconfig.php');

if(isset($_GET['sql'])) {
    $sql = urldecode($_GET['sql']);
    
    $kommando = $db->prepare($sql);
    $kommando->execute();
    $kommando->store_result();
    $kommando->bind_result($gutscheinnummer, $ausgabedatum, $praesent, $kundenname, $enddatum, $rechnungsnummer, $berechnung, $einloesungsort, $einloesedatum, $ist_abgelaufen, $ist_eingeloest, $empf_nachname, $empf_ort, $empf_land, $versanddatum);
    
    $csv_output = "GS-Nr.; Ausg.Datum; Praesent; Verschenker; Befristung; RG-Nr.; Berechnung; Einl.Ort; Einl.Datum; abgel.; eingel.; Empf. Nachname; Empf. Ort; Empf. Land; Vers.Datum; \n";
    
    while($kommando->fetch()) {
        
        if(!empty($enddatum)) $enddatum = date('d.m.Y', $enddatum);
        if(!empty($einloesedatum)) $einloesedatum = date('d.m.Y', $einloesedatum);
        if(!empty($ausgabedatum)) $ausgabedatum = date('d.m.Y', $ausgabedatum);
        if(!empty($versanddatum)) $versanddatum = date('d.m.Y', $versanddatum);
        
        ($ist_eingeloest == 1) ? $ist_eingeloest = 'Ja' : $ist_eingeloest = 'Nein';
        ($ist_abgelaufen == 1) ? $ist_abgelaufen = 'ja' : $ist_abgelaufen = 'Nein';
        
        $csv_output .= $gutscheinnummer."; ".$ausgabedatum."; ".$praesent."; ".$kundenname."; ".$enddatum."; ".$rechnungsnummer."; ".$berechnung."; ".$einloesungsort."; ".$einloesedatum."; ".$ist_abgelaufen."; ".$ist_eingeloest."; ".
        $empf_nachname."; ".$empf_ort."; ".$empf_land."; ".$versanddatum."; ";
        $csv_output .= "\n";
    }
    
    $filename = "gutscheinliste";
    header("Content-type: application/vnd.ms-excel");
    header("Content-disposition: csv" . date("d.m.Y") . ".csv");
    header("Content-disposition: filename=".$filename.".csv");
    print $csv_output;
    
    // Datenbank schliessen
    $db->close();
}

?>