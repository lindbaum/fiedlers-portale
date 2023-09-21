<div id="menu7">

<?php

if($_SESSION['user_level'] >= 1) {
print('<ul>
    <li>&Uuml;berpr&uuml;fung</li>
    <li><a href=\'check_code.php\'>Gutschein pr&uuml;fen</a></li>
</ul>');
}

if($_SESSION['user_level'] >= 3) {
print('<br />
<ul>
    <li>Gutscheine</li>
    <li><a href=\'coupons.php\'>Gutschein&uuml;bersicht</a></li>
    <li><a href=\'coupons_prepare.php\'>Gutschein ausgeben</a></li>
    <li><a href=\'coupons_reset.php\'>Gutschein zur&uuml;cksetzen</a></li>
    <li><a href=\'coupons_redeem.php\'>Gutschein einl&ouml;sen</a></li>
</ul>');
}

if($_SESSION['user_level'] >= 2) {
print('<br />
<ul>
    <li>Reports</li>
    <li><a href=\'report_gutscheinfilter.php\'>Gutscheinliste</a></li>
    <li><a href=\'report_verschenker.php\'>Verschenkerliste</a></li>
    <li><a href=\'report_versandliste.php\'>Versandliste (Easylog)</a></li>
</ul>');
}

if($_SESSION['user_level'] >= 3) {
print('<br />
<ul>
    <li>Stammdaten</li>
    <li><a href=\'clients.php\'>Bestandskunden</a></li>
    <li><a href=\'gifts.php\'>Pr&auml;sente</a></li>
    <li><a href=\'shipping_days.php\'>Standard-Versandtage</a></li>
    <li><a href=\'shipping_days_special.php\'>Versandtage sperren</a></li>
    <li><a href=\'user_account.php\'>Benutzerverwaltung</a></li>
</ul>');
}

?>

</div>