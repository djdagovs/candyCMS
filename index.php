<?php

/**
 * Website entry.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @version 2.0
 * @since 1.0
 */

/**
 * Set how to handle PHP error messages.
 */
error_reporting(E_ALL);

/**
 * Override separator due to W3C compatibility.
 */
ini_set('arg_separator.output', '&amp;');

/**
 * Compress output.
 */
ini_set('zlib.output_compression_level', 9);

/**
 * Set standard timezone for PHP5.
 */
date_default_timezone_set('Europe/Berlin');

/**
 * Current version we are working with.
 */
define('VERSION', '20110714');

/**
 * Display error messages when in development mode.
 */
ini_set('display_errors', 1);

/*
 * Load main classes.
 */
try {
  if (!file_exists('app/models/Main.model.php') ||
      !file_exists('app/controllers/Main.controller.php') ||
      !file_exists('app/controllers/Session.controller.php') ||
      !file_exists('app/controllers/Index.controller.php') ||
      !file_exists('app/controllers/Log.controller.php') ||
      !file_exists('app/helpers/AdvancedException.helper.php') ||
      !file_exists('app/helpers/Section.helper.php') ||
      !file_exists('app/helpers/Helper.helper.php') ||
      !file_exists('lib/smarty/Smarty.class.php')
  )
    throw new Exception('Could not load required classes.');
  else {
    require_once 'app/models/Main.model.php';
    require_once 'app/controllers/Main.controller.php';
    require_once 'app/controllers/Session.controller.php';
    require_once 'app/controllers/Index.controller.php';
    require_once 'app/controllers/Log.controller.php';
    require_once 'app/helpers/AdvancedException.helper.php';
    require_once 'app/helpers/Section.helper.php';
    require_once 'app/helpers/Helper.helper.php';
    require_once 'lib/smarty/Smarty.class.php';
  }
}
catch (\CandyCMS\Helper\AdvancedException $e) {
  die($e->getMessage());
}

/*
 * Start user session.
 */
@session_start();

# Initialize software
$oIndex = new \CandyCMS\Controller\Index(array_merge($_POST, $_GET), $_SESSION, $_FILES, $_COOKIE);

$oIndex->loadConfig();
$oIndex->loadPlugins();
$oIndex->setTemplate();
$oIndex->setLanguage();
$oIndex->loadCronjob();

/**
 * If we are on a productive enviroment, make sure that we can't override the system.
 */
if (is_dir('install') && WEBSITE_DEV == false)
  exit('Please install software via <strong>install/</strong> and delete the folder afterwards!');

# Set active user
$aUser = \CandyCMS\Model\Session::getSessionData();

define('USER_ID', (int) $aUser['id']);
define('USER_PASSWORD', isset($aUser['password']) ? $aUser['password'] : '');

# Try to get facebook data
if (USER_ID == 0) {
  $oFacebook = $oIndex->loadFacebookExtension();
  if ($oFacebook == true)
    $aFacebookData = $oFacebook->getUserData();
}

/**
 * Define user constants for global use.
 *
 * List of user rights:
 * 0 = Guests / unregistered users
 * 1 = Members
 * 2 = Facebook users
 * 3 = Moderators
 * 4 = Administrators
 */
define('USER_RIGHT', isset($aFacebookData[0]['uid']) ?
                2 :
                (int) $aUser['user_right']);

define('USER_FACEBOOK_ID', isset($aFacebookData[0]['uid']) ?
                $aFacebookData[0]['uid'] :
                '');

define('USER_EMAIL', isset($aFacebookData[0]['email']) ?
                $aFacebookData[0]['email'] :
                $aUser['email']);

define('USER_NAME', isset($aFacebookData[0]['first_name']) ?
                $aFacebookData[0]['first_name'] :
                $aUser['name']);

define('USER_SURNAME', isset($aFacebookData[0]['last_name']) ?
                $aFacebookData[0]['last_name'] :
                $aUser['surname']);

define('USER_FULL_NAME', USER_NAME . ' ' . USER_SURNAME);

# If this is an ajax request, no layout is loaded
$iAjax = isset($_REQUEST['ajax']) ? 1 : 0;
define('AJAX_REQUEST', (int) $iAjax);

# Define current url
define('CURRENT_URL', WEBSITE_URL . $_SERVER['REQUEST_URI']);

echo $oIndex->show();
?>