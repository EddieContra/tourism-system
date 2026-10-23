<?php
/** Unit-test bootstrap: loads the pure helper functions without a database or session. */
define('APP_ROOT', dirname(__DIR__));
define('APP_NAME', 'Five Star Tours');
define('COMPANY_EMAIL', 'info@fivestartours.test');

require APP_ROOT . '/vendor/autoload.php';
require APP_ROOT . '/includes/env.php';
require APP_ROOT . '/includes/helpers.php';
