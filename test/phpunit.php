<?php
/**
 * Just a small wrapper around $PHPRC/phpunit to
 * enable PHPUnit debugging from inside Eclipse.
 */
define('INDEX_FILE_LOCATION', dirname(dirname(__FILE__)).'/index.php');
chdir(dirname(INDEX_FILE_LOCATION));

// Configure PKP error handling for tests
define('DONT_DIE_ON_ERROR', true);

// Don't support sessions
define('SESSION_DISABLE_INIT', true);

// Show errors on the GUI
ini_set('display_errors', 'on');

// The server variable PHP_PEAR_BIN_DIR points to the PEAR base directory
include $_SERVER['PHP_PEAR_BIN_DIR'] . '/phpunit';
?>