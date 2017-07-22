<?php
if (version_compare(PHP_VERSION, '5.3.1', '<'))
{
    die('Your host needs to use PHP 5.3.1 or higher to run this version of Joomla!');
}

define('_JEXEC', 1);
define('JPATH_BASE', $_SERVER['DOCUMENT_ROOT']); // define JPATH_BASE on the external file
define('DS', DIRECTORY_SEPARATOR);
$definesPhp = JPATH_BASE . DS . 'includes' . DS . 'defines.php';

require_once($definesPhp);
require_once(JPATH_BASE . DS . 'includes' . DS . 'framework.php');

