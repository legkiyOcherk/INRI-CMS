<?
if (!defined("WA_PATH"))    define("WA_PATH",    "./");
if (!defined("NX_PATH"))    define("NX_PATH",    "./");
if (!defined("NEX_PATH"))   define("NEX_PATH",   "../");
if (!defined("IA_URL"))     define("IA_URL",     "/admin_inri/");
if (!defined("ADM_DIR"))    define("ADM_DIR",    "admin_inri");
if (!defined("CMS_NAME"))   define("CMS_NAME",   "INRI");
if (!defined("DB_PFX"))     define("DB_PFX",     "inri_");
if (!defined("ADMIN_NAME")) define("ADMIN_NAME", "Admin PANEL");
if (!defined("SITE_NAME"))  define("SITE_NAME",  "in-ri.ru"); 


#if (!defined("ADMIN_FAVICON"))       define("ADMIN_FAVICON", "http://in-ri.ru/css/img/favicon_black.ico");
if (!defined("ADMIN_FAVICON"))       define("ADMIN_FAVICON", "http://in-ri.ru/css/img/favicon_white.ico");
if (!defined("SOURCE_SITE_CUTAWAY")) define("SOURCE_SITE_CUTAWAY", "http://cutaway.ready.in-ri.ru"); 

define("CART_TYPE_DOOR", 1);
define("CART_TYPE_CATALOGUE", 2);
define('TIME_LIFE_COOKIE', 604800); // 7 days

date_default_timezone_set("Asia/Yekaterinburg");