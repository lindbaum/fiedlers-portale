<?php

set_magic_quotes_runtime(0);

print get_magic_quotes_gpc();

// for .htaccess file: php_value magic_quotes_gpc off


phpinfo();
?>