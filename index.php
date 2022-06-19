<?php

/**
 * Request forwarder, look at common/index.php for the action and license
 * @package EDK
 */

// Enable custom error handling.
require_once ('common/includes/class.edkerror.php');

#set_error_handler(array('EDKError', 'handler'), E_ALL & ~(E_STRICT | E_NOTICE | E_USER_NOTICE | E_ERROR) );
#@error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_USER_NOTICE | E_ERROR));
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set("UTC");

// Set up include paths.
if (!substr_compare(PHP_OS, 'win', 0, 3, true))
{
    @set_include_path(get_include_path() . PATH_SEPARATOR . '.\\common\\includes');
}
else
{
    @set_include_path(get_include_path() . PATH_SEPARATOR . './common/includes');
}

// Party time!
include('common/index.php');
