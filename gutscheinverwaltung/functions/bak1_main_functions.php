<?php

// Dokument Funktionen
// *****************************************************************************

function setDocumentHead() {
    print('<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Fiedler Fisch- & Meeresdelikatessen | Gutscheinverwaltung v1.0</title>
<link rel="stylesheet" href="css/main.css" type="text/css" />
<link rel="stylesheet" href="css/form.css" type="text/css" />
<script language="javascript" src="calendar/calendar.js"></script>
<script type="text/javascript">
    //<![CDATA[
        function link(ziel) {
            if(confirm("Datensatz unwiderruflich entfernen?"))
            top.location.href = ziel;
        }
        
        function setLocation(url) {
            window.location.href = url;
        } 
    //]]>
</script>
</head>

<body>');
}

function setDocumentFooter() {
    print('</body>
</html>');
}


// Tabellen Funktionen
// *****************************************************************************

function new_tbl($width, $border) {
    printf('<table width=\'%s\' border=\'%s\'>', $width, $border);
}

function tbl_header($tbl_header, $edit=false, $delete=false) {
    print('
          <thead>
          <tr>
          ');
    
    for($a = 0; $a < count($tbl_header); $a++) {
        printf('<th scope="row">%s</th>', $tbl_header[$a]);
    }
    
    if ($edit===true) print('<th>Edit</th>');
    if ($delete===true) print('<th>Delete</th>');
    
    print('
          </tr>
          </thead>
          ');
}

function tbl_body_open() {
    print('
          <tbody>
          ');    
}

function tbl_body_close() {
    print('
          </tbody>
          ');    
}

function new_tbl_row() {
    print('<tr>');    
}

function end_tbl_row() {
    print('</tr>');    
}

function multi_tbl_data($i, $wert, $edit=false, $delete=false, $editscript='#', $deletescript='#') {
    print('<tr>');
    
    for($a = 0; $a < $i; $a++) {
        printf('<td>%s</td>', $wert[$a]);
    }
    
    if ($edit===true) print('<td><a href="' . $editscript . '?edit=' . $wert[0] . '">Edit</a></td>');
    if ($delete===true) print('<td><a href="' . $deletescript . '?delete=' . $wert[0] . '">Delete</a></td>');
    
    print('</tr>');
}

function end_tbl() {
    print('</table>');
}

// *****************************************************************************


?>