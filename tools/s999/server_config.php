<?php

$dateTime = new DateTime;

// NEED CHANGES THIS
define('SERVER_AGENT',      'TQC');
define('SERVER_ID',         's999');
define('SERVER_TITLE',      'Tam Quốc Chế - S999');
define('SERVER_OPEN_TIME',  $dateTime->setDate(2015, 10, 4)->getTimestamp());
define('GAME_PORT',         '8090');

// STABLE
define('SERVER_DEBUG','0');
define('DEFAULT_LANG','zh-cn');
define('SERVER_DEPENDENCE','');
define('GM_HOST','42.112.20.26');
define('GAME_HOST','42.112.20.26');
define('BL_PORT',10041+(GAME_PORT-8000)/10*50);
define('GM_PORT',10042+(GAME_PORT-8000)/10*50);
?>