<?php
/**
 *    Autor        : RenŽ Kaminsky
 *    Copyright    : (c) 2011 by media senses / brandcode
 */
require_once('includes/auth.php'); // incl. session_start();
if($_SESSION['user_level'] >= 2) define('SECURE', true);
require_once('includes/dbconfig.php');

if(isset($_GET['date']) && preg_match('/^[0-9]{10}$/', $_GET['date'])) {

    $sql = "SELECT A.empf_anrede, A.empf_vorname, A.empf_nachname, A.empf_firma, A.empf_strasse, A.empf_hausnummer,
            A.empf_plz, A.empf_ort, A.empf_email, A.gutschein_nummer, B.prae_bezeichnung, B.prae_gewicht, A.wunsch_versand_datum
            FROM gsv_gutschein A, gsv_praesent B
            WHERE A.gsv_praesent_prae_id = B.prae_id
            AND A.wunsch_versand_datum = ?
            ORDER BY A.gutschein_nummer ASC";
    
    $kommando = $db->prepare($sql);
    $kommando->bind_param('i', $_GET['date']);
    $kommando->execute();
    $kommando->store_result();
    $kommando->bind_result($anrede, $vorname, $nachname, $firma, $strasse, $hnr, $plz, $ort, $email, $gsnummer, $praesent, $gewicht, $versanddatum);
    
    //$csv_output = "Anrede; Vorname; Name; Firma; Strasse; Haus-Nr.; PLZ; Ort; \n";
    $csv_output = '';
    
    while($kommando->fetch()) {
        
        $csv_output .= $anrede."; ".$vorname."; ".$nachname."; ".$firma."; ".$strasse."; ".$hnr."; ".$plz."; ".$ort."; ".$email."; ".$gsnummer."; ".$praesent."; ".$gewicht."; ".date('d.m.Y',$versanddatum)."; ";
        $csv_output .= "\n";
    }
    
    $filename = "versandliste_easylog";
    header("Content-type: application/vnd.ms-excel");
    header("Content-disposition: csv" . date("d.m.Y") . ".csv");
    header("Content-disposition: filename=".$filename.".csv");
    print $csv_output;
    
    // Datenbank schliessen
    $db->close();
}

?>