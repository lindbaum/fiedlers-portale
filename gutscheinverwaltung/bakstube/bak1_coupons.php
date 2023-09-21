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

// Sortierung auslesen 
$strfeld = isset($_GET['feld']) ? $_GET['feld'] : 'gutschein_nummer'; 
$strsort = isset($_GET['sort']) ? $_GET['sort'] : 'asc'; 
$ordnen = "desc";
$sortpic = "arrow_up.png";
    
if($strsort == "desc"){
    $sortpic = "arrow_down.png";
    $ordnen= "asc";
}  

$srch_gsnummer = isset($_GET['srch_gsnummer']) ? $_GET['srch_gsnummer'] : '';
$zvon = isset($_GET['zvon']) ? $_GET['zvon'] : '';
$zbis = isset($_GET['zbis']) ? $_GET['zbis'] : ''; 

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
                <h2>Gutschein&uuml;bersicht</h2>
            </div>
            
            <div class="contentBox">
                <h4>Filter</h4><br />
                <form method="get" autocomplete="off">
                    <input class="text_field" type="text" name="srch_gsnummer" placeholder="Gutscheinnummer" <?php if(isset($_GET['srch_gsnummer']) && trim($_GET['srch_gsnummer']) != '') echo 'value="'.htmlspecialchars($_GET['srch_gsnummer']).'"'; ?> />//
                    <select name="zvon" class="select_field">
                        <option value="">Ausgabezeitraum von</option>
                        <?php
                        
                        $heute = mktime(0,0,0,date('m'),date('d'),date('Y'));
                        $jahr = date('Y',$heute);
                        
                        for($a = -1; $a <= 1; $a++) {
                            for($b = 1; $b <= 12; $b++) {
                                (strlen((string)$b) < 2) ? $bb = '0'.$b : $bb = $b;
                                $vonvalue = mktime(0,0,0,$b,1,($jahr+$a));
                                ($vonvalue == $_GET['zvon']) ? printf('<option value="%s" selected>%s/%s</option>', $vonvalue, $bb, ($jahr+$a)) : printf('<option value="%s">%s/%s</option>', $vonvalue, $bb, ($jahr+$a));
                            }
                        }
                        
                        ?>
                    </select>
                    <select name="zbis" class="select_field">
                        <option value="">Ausgabezeitraum bis</option>
                        <?php
                        
                        for($a = -1; $a <= 1; $a++) {
                            for($b = 1; $b <= 12; $b++) {
                                (strlen((string)$b) < 2) ? $bb = '0'.$b : $bb = $b;
                                $maxd = date('t',mktime(0, 0, 0, $b, 1, ($jahr+$a)));
                                $bisvalue = mktime(0,0,0,$b,$maxd,($jahr+$a));
                                ($bisvalue == $_GET['zbis']) ? printf('<option value="%s" selected>%s/%s</option>', $bisvalue, $bb, ($jahr+$a)) : printf('<option value="%s">%s/%s</option>', $bisvalue, $bb, ($jahr+$a));
                            }
                        }
                        
                        ?>
                    </select>
                    <input class="submit_btn" type="submit" value="Filtern" />
                    <input class="submit_btn" type="button" value="Alle anzeigen" onclick="setLocation('coupons.php')" />
                </form>
            </div>
            
            <div class="contentBox">
            <?php
            
            $sql = "SELECT A.gutschein_nummer, A.ausgabe_datum, B.prae_bezeichnung, C.kunden_name, A.end_datum, A.rechnungs_nummer, D.bere_bezeichnung, E.einl_bezeichnung, A.einloese_datum, A.ist_abgelaufen, A.ist_eingeloest 
                    FROM gsv_gutschein A, gsv_praesent B, gsv_verschenker C, gsv_berechnungsart D, gsv_einloesungsort E 
                    WHERE ist_ausgegeben = 1 
                    AND A.gsv_praesent_prae_id = B.prae_id
                    AND A.gsv_verschenker_vers_id = C.vers_id
                    AND A.gsv_berechnungsart_bere_id = D.bere_id
                    AND A.gsv_einloesungsort_einl_id = E.einl_id";
            
            if(isset($_GET['srch_gsnummer']) && trim($_GET['srch_gsnummer']) != '') $sql .= " AND gutschein_nummer LIKE '%" . trim($_GET['srch_gsnummer']) . "%'";
            
            if(isset($_GET['zvon']) && !empty($_GET['zvon'])) $sql .= " AND ausgabe_datum >= '" . $_GET['zvon'] . "'";
            if(isset($_GET['zbis']) && !empty($_GET['zbis'])) $sql .= " AND ausgabe_datum <= '" . $_GET['zbis'] . "'";
           
            $sql .= " ORDER BY " . $strfeld . " " . $strsort ;
            
            $kommando = $db->prepare($sql);
            $kommando->execute();
            $kommando->store_result();
            $kommando->bind_result($gutscheinnummer, $ausgabedatum, $praesent, $kundenname, $enddatum, $rechnungsnummer, $berechnung, $einloesungsort, $einloesedatum, $ist_abgelaufen, $ist_eingeloest);
            
            new_tbl('100%', 1);
            
            echo '<thead><tr>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=gutschein_nummer&srch_gsnummer=' . $srch_gsnummer . '&zvon=' . $zvon . '&zbis=' . $zbis . '">GS-Nr.</a>';
            if($strfeld=='gutschein_nummer') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=ausgabe_datum&srch_gsnummer=' . $srch_gsnummer . '&zvon=' . $zvon . '&zbis=' . $zbis . '">Ausg.Datum</a>';
            if($strfeld=='ausgabe_datum') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=prae_bezeichnung&srch_gsnummer=' . $srch_gsnummer . '&zvon=' . $zvon . '&zbis=' . $zbis . '">Pr&auml;sent</a>';
            if($strfeld=='prae_bezeichnung') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=kunden_name&srch_gsnummer=' . $srch_gsnummer . '&zvon=' . $zvon . '&zbis=' . $zbis . '">Verschenker</a>';
            if($strfeld=='kunden_name') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=end_datum&srch_gsnummer=' . $srch_gsnummer . '&zvon=' . $zvon . '&zbis=' . $zbis . '">Befristung</a>';
            if($strfeld=='end_datum') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=rechnungs_nummer&srch_gsnummer=' . $srch_gsnummer . '&zvon=' . $zvon . '&zbis=' . $zbis . '">RG-Nr.</a>';
            if($strfeld=='rechnungs_nummer') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=bere_bezeichnung&srch_gsnummer=' . $srch_gsnummer . '&zvon=' . $zvon . '&zbis=' . $zbis . '">Berechnung</a>';
            if($strfeld=='bere_bezeichnung') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=einl_bezeichnung&srch_gsnummer=' . $srch_gsnummer . '&zvon=' . $zvon . '&zbis=' . $zbis . '">Einl.Ort</a>';
            if($strfeld=='einl_bezeichnung') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=einloese_datum&srch_gsnummer=' . $srch_gsnummer . '&zvon=' . $zvon . '&zbis=' . $zbis . '">Einl.Datum</a>';
            if($strfeld=='einloese_datum') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=ist_abgelaufen&srch_gsnummer=' . $srch_gsnummer . '&zvon=' . $zvon . '&zbis=' . $zbis . '">abgel.</a>';
            if($strfeld=='ist_abgelaufen') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $ordnen . '&feld=ist_eingeloest&srch_gsnummer=' . $srch_gsnummer . '&zvon=' . $zvon . '&zbis=' . $zbis . '">eingel.</a>';
            if($strfeld=='ist_eingeloest') echo '<img src="images/' . $sortpic . '" align="top" />'; echo '</th>';
            echo '<th>Edit</th>';
            echo '</tr></thead>';
            
            tbl_body_open();
            
            while($kommando->fetch()) {
                if(!empty($enddatum)) $enddatum = date('d.m.Y', $enddatum);
                if(!empty($einloesedatum)) $einloesedatum = date('d.m.Y', $einloesedatum);
                if(!empty($ausgabedatum)) $ausgabedatum = date('d.m.Y', $ausgabedatum);
                ($ist_eingeloest == 1) ? $str_ist_eingeloest = '<img src="images/tick.png" align="top" alt="ja" />' : $str_ist_eingeloest = '';
                ($ist_abgelaufen == 1) ? $str_ist_abgelaufen = '<img src="images/tick.png" align="top" alt="ja" />' : $str_ist_abgelaufen = '';
                printf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                       $gutscheinnummer, $ausgabedatum, $praesent, $kundenname, $enddatum, $rechnungsnummer, $berechnung, $einloesungsort, $einloesedatum, $str_ist_abgelaufen, $str_ist_eingeloest, '<a href="coupons_edit.php?edit=' . $gutscheinnummer . '&redeem=' . $ist_eingeloest . '">Edit</a>');
            }
            
            tbl_body_close();
            
            ?>

            <tfoot>
                <tr>
                    <td colspan="12"><?php printf('%s Eintr&auml;ge gefunden.', $kommando->num_rows); ?></td>
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