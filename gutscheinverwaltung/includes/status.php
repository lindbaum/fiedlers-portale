<div><h2>H.-J. Fiedler Meeresdelikatessen GmbH - Gutscheinverwaltung</h2><br /></div>
<div style="float:left;">
    <?php printf('<span>Sie sind angemeldet als <b>%s</b> und haben Berechtigungsstufe %s. </span>', $_SESSION['user_name'], $_SESSION['user_level']); ?>
</div>
<div style="float:right; text-align:right;">
    <?php
        //print('<span><a href="login.php?logout" class="link">Logout</a></span>&nbsp;&nbsp;|&nbsp;&nbsp;');
        print('<span><input type="button" class="submit_btn" onClick="setLocation(\'login.php?logout\')" value="Logout" /></span>&nbsp;&nbsp;|&nbsp;&nbsp;');
        printf('<span>Heute ist der %s</span>', date('d.m.Y'));
    ?>
</div>
<div style="clear:both;"></div>