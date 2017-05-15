<?php

ob_start();
phpinfo();
$content = ob_get_clean();
$content = str_replace('&nbsp;', ' ', $content);
$content = strip_tags($content);

echo $content;
