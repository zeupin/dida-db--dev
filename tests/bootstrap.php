<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
 */

require('D:/Projects/github/dida-autoloader--dev/src/Autoloader.php');
require('D:/Projects/github/dida-debug--dev/src/Debug/Debug.php');
\Dida\Autoloader::init();
\Dida\Autoloader::addPsr4('Dida\\', 'D:/Projects/github/dida-db--dev/src/Dida');

require('D:/Projects/github/composer/vendor/autoload.php');
