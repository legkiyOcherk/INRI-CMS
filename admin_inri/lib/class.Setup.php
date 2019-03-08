<?php
require_once('../define.php');
require_once(WA_PATH.'config.inc.php');
require_once(WA_PATH.'lib/mysql.lib.php');
require_once(WA_PATH.'lib/class.db.php');

class Setup{
  
  var $doctype           = '<!doctype html>';
  var $cms               = CMS_NAME;
  var $db_pfx            = DB_PFX;
  var $lang              = 'ru';
  var $charset           = '<meta http-equiv="Content-Type" content="text/html" charset="utf-8">';
  var $title;
  var $description;
  var $keywords;
  var $nav_items_arr     = array(); # ['link'] => title
  var $script_name       = 'setup.php'; 
  var $pdo;
  private $content; 
  
  function __construct (){ # конструктор
    if($this->is_database_access()){
      $this->pdo = db_open();  
    }
    
    
    $this->title = 'Установка '.$this->cms.' CMS';
    $this->set_content('<div class="container"><h1>Установка '.$this->cms.' CMS</h1></div>'); 
    
    #$this->nav_items_arr['/'.ADM_DIR.'/'.$this->script_name] = 'Главная';
    $this->nav_items_arr['/'] = 'Сайт';
    $this->nav_items_arr['/'.ADM_DIR.'/index.php'] = 'Админка';
  }
 
  function get_setup_header(){
    $output = '    
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="in-ri.ru">
    <link rel="shortcut icon" type="image/x-icon" href="/css/img/favicon/favicon.ico">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    ';
    return $output;
  }
  
  function get_setup_footer(){
    $output = '
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    ';
    
    return $output;
  }
  
  function get_setup_style(){
    $output = '
    <style>
    pre {
      display: block;
      padding: 9.5px;
      margin: 0 0 10px;
      font-size: 13px;
      line-height: 1.42857143;
      color: #333;
      word-break: break-all;
      word-wrap: break-word;
      background-color: #f5f5f5;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .container{
      padding-bottom: 15px;
    }
    </style>
    ';
    
    return $output;
  }
  
  function set_content($content){
    $this->content = $content;
  }
  
  function get_content(){
    return $this->content;
  }
  
  function add_content($text){
    $this->content .= $text;
  }
  
  function get_nav_bar(){
    $output = '';
    $output .= '
    <div class="container">
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="/'.ADM_DIR.'/'.$this->script_name.'">INRI</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mr-auto">'; 
    foreach($this->nav_items_arr as $k => $v){
      $output .= '
            <li class="nav-item">
              <a class="nav-link" href="'.$k.'">'.$v.'</a>
            </li>';
    }
            
    $output .= '
          </ul>
        </div>
      </nav>
    </div>';
    
    return $output;
  }
  
  function wrap_block($text){
    $output = '';
    
    $output .= '
    <div class="container">
      '.$text.'
    </div>';
    
    return $output;
  }
  
  function show(){
    $output = '';
    $output .= $this->doctype.'
<html lang="'.$this->lang.'">
  <head>
    '.$this->charset;
    $output .= $this->get_setup_header();
    $output .= '
    <title>'.$this->title.'</title>
  </head>
  <body>';
    $output .= $this->get_nav_bar();
    $output .= $this->get_content();
    $output .= $this->get_setup_footer();
    $output .= $this->get_setup_style();
    $output .= '
  </body>
</html>';
    
    return $output;
  }
  
  function is_database_access(){ # Проверка корректности подключения
    $charset = 'utf8';
    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $CFG = & $_SESSION["NEX_CFG"];
    $dsn = 'mysql:host='.$CFG["db_hostname"].';dbname='.$CFG["db_basename"].';charset='.$charset;
    #pri($dsn);
    try { 
      $PDO = new PDO($dsn, $CFG["db_username"], $CFG["db_password"], $opt); 
      return true;
    } 
    catch (PDOException $e) { 
     #die('Подключение не удалось: ' . $e->getMessage()); 
     return false;
    }
    
  }
  
  function test_database_access(){
    $output = '';
    if( $this->is_database_access() ){
      $output .= '
      <div class="alert alert-success" role="alert">
        Доступ к базе данных установлен.
      </div>';
    }else{
      $output .= '
      <div class="alert alert-danger" role="alert">
        Доступ к базе данных отсутствует!
      </div>';
    }
    return $output;
  }
  
  function set_database_access(&$db_arr){
    $output = '';
    
    if( isset($_POST) && $_POST ){
      $db_arr['db_hostname'] = $_POST['db_hostname'];
      $db_arr['db_username'] = $_POST['db_username'];
      $db_arr['db_password'] = $_POST['db_password'];
      $db_arr['db_basename'] = $_POST['db_basename'];
      
      $new_config_data = '#db_config
	"db_hostname" => "'.$db_arr['db_hostname'].'",
	"db_username" => "'.$db_arr['db_username'].'",
	"db_password" => "'.$db_arr['db_password'].'",
	"db_basename" => "'.$db_arr['db_basename'].'"
#end_db_config';

      $config_file = file_get_contents('config.inc.php');
      $config_file = preg_replace('|\#db_config(.*?)\#end_db_config|isU', $new_config_data, $config_file);
      $res = file_put_contents('config.inc.php', $config_file);
      $_SESSION["NEX_CFG"] =& $db_arr;
      #pri($config_file);
    }
    #pri($database_acs_arr);
    
    return $output; 
  }
  
  function setup_database_access(){
    $output = '';
    
    $db_arr = array(
      "db_hostname" => "",
    	"db_username" => "",
    	"db_password" => "",
    	"db_basename" => "" 
    );
    
    $output .= '
    <h1>Установка доступа к базе данных</h1>';
    $output .= $this->set_database_access($db_arr);
    #$output .= $this->test_database_access();
    if( $this->is_database_access() ){
      $output .= '
      <div class="alert alert-success" role="alert">
        Доступ к базе данных установлен.
      </div>';
    }else{
      $output .= '
      <div class="alert alert-danger" role="alert">
        Доступ к базе данных отсутствует!
      </div>';
    
      $output .= '
      <form method = "post">
        <div class="card">
          <div class="card-header">
            Доступ к базе данных
          </div>
          <div class="card-body">
            <h5 class="card-title">Введите данные</h5>
            
            <div class="form-group row">
              <label for="db_hostname" class="col-sm-3 col-form-label">Hostname</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="db_hostname" name = "db_hostname" value="'.$db_arr['db_hostname'].'">
              </div>
            </div>
            
            <div class="form-group row">
              <label for="db_username" class="col-sm-3 col-form-label">Username</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="db_username" name = "db_username" value="'.$db_arr['db_username'].'">
              </div>
            </div>
            
            <div class="form-group row">
              <label for="db_password" class="col-sm-3 col-form-label">Password</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="db_password" name = "db_password" value="'.$db_arr['db_password'].'">
              </div>
            </div>
            
            <div class="form-group row">
              <label for="db_basename" class="col-sm-3 col-form-label">Basename</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="db_basename" name = "db_basename" value="'.$db_arr['db_basename'].'">
              </div>
            </div>
            <div class="form-group row">
              <label for="submit" class="col-sm-3 col-form-label"></label>
              <div class="col-sm-9">
                <button class="btn btn-primary" type="submit">Применить</button>
              </div>
            </div>
            
            
            
          </div>
        </div>
      
      </form>';
    }
    
    return $output; 
  }
  
  function is_database($table_name){
    
    $CFG = & $_SESSION["NEX_CFG"];
    $database = $CFG["db_basename"];
    
    $s = "SHOW TABLES FROM $database LIKE '$table_name' ";
    
    if($q = $this->pdo->query($s)){
      if($q->rowCount()){
        return true;
      }  
    }
    
    return false;
  }
  
  function delete_table(){
    $output = $module_name = '';
    $output .= '
    <h3>Удаление модуля </h3>';
    if( isset($_GET['table_name']) && isset($_GET['table_name']) ){
      $table_name = addslashes( $_GET['table_name'] );
      
      if( !$this->is_database( $table_name ) ){
        $output .= '
        <div class="alert alert-danger" role="alert">
          Модуль '.$table_name.' Отсутствует!
        </div>';
      }else{
        $sql = "DROP TABLE `$table_name`";
        if($q = $this->pdo->query($sql)){
          $output .= '
          <div class="alert alert-success" role="alert">
            Таблица '.$table_name.' УДАЛЕНА!
          </div>';
        }else{
          $error .= '
          <div class="alert alert-danger" role="alert">
            Не удалось УДАЛИТЬ таблицу '.$table_name.'!
          </div>';
        }
      }
    }else{
      $output .= '
        <div class="alert alert-danger" role="alert">
          Отсутствует название модуля!
        </div>';
    }
    
    return $output;
  }
    
  function setup_database_table($module_name, $table_name, &$sql, &$sql_insert = null, $script_name = null  ){
   
    $output = $error = '';
    
    
    $output .= '
    <h3>Установка модуля `'.$module_name.'`</h3>';
    if( !$this->is_database( $table_name ) ){
      $output .= '
      <div class="alert alert-danger" role="alert">
        Таблица '.$table_name.' отсутствует!
      </div>';
      
      if($q = $this->pdo->query($sql)){
        $output .= '
        <div class="alert alert-success" role="alert">
          Таблица '.$table_name.' создана!
          &nbsp; <a class="btn btn-outline-danger btn-sm float-right" href = "/'.ADM_DIR.'/'.$this->script_name.'?step=delete_database_module&table_name='.$table_name.'">Удалить</a>
        </div>';
        if($sql_insert) {
          $q_insert = $this->pdo->query($sql_insert);
          $output .= '
          <div class="alert alert-success" role="alert">
            Таблица '.$table_name.' Заполнена значениями по умолчанию!
          </div>';
        }
      }else{
        $error .= '
        <div class="alert alert-danger" role="alert">
          Не удалось установить таблицу '.$table_name.'!
        </div>';
      }
      
      
    }else{
      $output .= '
      <div class="alert alert-success" role="alert">
        Таблица '.$table_name.' существует! 
        &nbsp; <a class="btn btn-outline-danger btn-sm float-right" href = "/'.ADM_DIR.'/'.$this->script_name.'?step=delete_database_module&table_name='.$table_name.'">Удалить</a>
      </div>';
    }
    
    if($error){
      $output .= $error;
    }else{
      if($script_name){
        $output .= '
        <div class="alert alert-primary" role="alert">
          Управление модулем `<b><a href = "'.IA_URL.$script_name.'" target = "_blank">'.$module_name.'</a></b>`.
        </div>';
      }
    }
    
    return $output; 
  }
  
  function setup_module_currency(){
    $title = "Курсы валют";
    $name = 'currency';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(256) NOT NULL,
        `title` varchar(256) NOT NULL,
        `val` text NOT NULL,
        `type` tinyint(1) NOT NULL DEFAULT '0',
        `comment` varchar(256) NOT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    $sql_insert = "
      INSERT INTO `$table` (`id`, `name`, `title`, `val`, `type`, `comment`, `hide`, `ord`) VALUES
      (1, 'eur', 'Курс EUR:', '70.50', 0, '', 0, 0),
      (2, 'usd', 'Курс USD:', '63.10', 0, '', 0, 0) ";
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_config(){
    $title = "Параметры";
    $name = 'config';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(256) NOT NULL,
        `title` varchar(256) NOT NULL,
        `val` text NOT NULL,
        `type` tinyint(1) NOT NULL DEFAULT '0',
        `comment` varchar(256) NOT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    $sql_insert = "
      INSERT INTO `$table` (`id`, `name`, `title`, `val`, `type`, `comment`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
      (1, 'email_order', 'E-mail', '1@in-ri.ru', 0, 'отпарка писем', 0, 0),
      (2, 'phone', 'Телефон', '<a href="tel:+79058010809">+7 (905) 801-08-09‬</a>', 0, '', 0, 0),
      (4, 'adress', 'Адрес', '620100, г. Екатеринбург, ул Сибирский тракт 12/2, офис 404', 0, '', 0, 0),
      (5, 'email', 'E-mail', '1@in-ri.ru', 0, '', 0, 0),
      (8, 'soc_net', 'Социальные сети', '<a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a>\r\n<a href="#"><i class="fa fa-vk" aria-hidden="true"></i></a>\r\n<a href="#"><i class="fa fa-odnoklassniki" aria-hidden="true"></i></a>\r\n<a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a>\r\n<a href="#"><i class="fa fa-youtube-play" aria-hidden="true"></i></a>\r\n<a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>', 1, '', 0, 0),
      (3, 'whatsap_phone', 'Телефон whatsap', '+79058010809', 0, 'Пишется только цифрами без пробелов', 0, 0),
      (6, 'working_hour', 'Время работы', 'Время работы: ежедневно с 10-00 до 18-00', 0, '', 0, 0) 
HTML;
    return $this->setup_database_table( $title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_seo(){
    $title = "СЕО";
    $name = 'seo';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `type` varchar(255) NOT NULL,
        `view` int(1) DEFAULT NULL,
        `ord` int(11) NOT NULL DEFAULT '0',
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `value` text NOT NULL,
        `comment` varchar(256) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `type`, `view`, `ord`, `hide`, `value`, `comment`) VALUES ";
    $sql_insert .=<<<HTML
      (10, 'Keywords главной', 'mine_keywords', NULL, 21, 0, '', NULL),
      (14, 'Alt картинки товара', 'img_alt', NULL, 25, 1, '', NULL),
      (15, 'Title картинки товара', 'img_title', NULL, 27, 1, '', NULL),
      (9, 'Description Главной', 'mine_description', 1, 20, 0, 'Интернет Магазин', NULL),
      (8, 'Title текстовой статьи', 'lib_text_title', NULL, 110, 0, '*h1* ', NULL),
      (7, 'Title текстового раздела', 'lib_cat_title', NULL, 100, 1, '*h1*', NULL),
      (6, 'Title новости', 'news_title', NULL, 90, 1, '*h1*', NULL),
      (1, 'Title главой', 'mine_title', NULL, 10, 0, 'Интернет Магазин', NULL),
      (2, 'Title каталога товаров', 'goods_cat_title', NULL, 22, 1, '*h1*', NULL),
      (3, 'Title Товара', 'goods_title', NULL, 23, 1, '*h1*', NULL),
      (4, 'Title производителя (Бренда)', 'brand_title', NULL, 115, 1, '*h1* ', NULL),
      (17, 'Title Страны', 'country_title', NULL, 116, 1, '*h1* ', NULL),
      (20, 'Description Товара', 'goods_description', 1, 24, 1, '', NULL)
HTML;
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_design(){
    $title = "Оформление сайта";
    $name = 'design';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `type` varchar(255) NOT NULL,
        `view` int(1) DEFAULT NULL,
        `ord` int(11) NOT NULL DEFAULT '0',
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `value` text NOT NULL,
        `comment` varchar(256) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `type`, `view`, `ord`, `hide`, `value`, `comment`) VALUES ";
    $sql_insert .=<<<HTML
      (12, 'Стили', 'user_style', 2, 130, 0, '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" >\r\n\r\n<style>\r\n  .test{\r\n	padding: 20px;\r\n  }\r\n  .mine_slider {\r\n    max-width: 1920px;\r\n  }\r\n</style>\r\n\r\n', 'Пользовательские css стили'),
      (11, 'Скрипты, счетчики', 'user_script', 2, 120, 0, '<script type="text/javascript">\r\n\r\n</script>\r\n<!--LiveInternet counter--><script type="text/javascript">\r\ndocument.write("<a href=''//www.liveinternet.ru/click'' "+\r\n"target=_blank><img src=''//counter.yadro.ru/hit?t44.6;r"+\r\nescape(document.referrer)+((typeof(screen)=="undefined")?"":\r\n";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?\r\nscreen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+\r\n";h"+escape(document.title.substring(0,150))+";"+Math.random()+\r\n"'' alt='''' title=''LiveInternet'' "+\r\n"border=''0'' width=''0'' height=''0''><\\/a>")\r\n</script><!--/LiveInternet-->\r\n', 'Яндекс метрика, liveinternet.ru и пользовательские js скрипты'),
      (21, 'Файлы', 'user_file', 3, 140, 0, '', 'Пользовательские файлы. ( путь до папки /images_ckeditor/files <a href = "/images_ckeditor/files/test.jpg" target = "_blank">тест</a> )'),
      (23, 'meta теги', 'user_meta', 2, 90, 0, '', 'Подтверждение прав: вембмастер, метрика и др.'),
      (22, 'robots.txt', 'user_robots', 2, 100, 0, 'User-agent: * \r\nHost:\r\nSitemap: /sitemap.xml\r\n', 'Пользовательские css стили');
HTML;
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_url(){
    $title = "Человеко-понятные адреса";
    $name = 'url';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `url` varchar(255) NOT NULL,
        `module` varchar(255) NOT NULL,
        `module_id` int(11) NOT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        UNIQUE KEY `url` (`url`),
        KEY `module` (`module`),
        KEY `module_id` (`module_id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_accounts(){
    $title = "Администрирование";
    $name = 'accounts';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(20) NOT NULL AUTO_INCREMENT,
        `onec_id` varchar(255) DEFAULT NULL,
        `onec_id_company` varchar(255) DEFAULT NULL,
        `title` varchar(255) DEFAULT NULL,
        `img` varchar(255) DEFAULT NULL,
        `login` varchar(255) NOT NULL DEFAULT '',
        `key` varchar(32) NOT NULL DEFAULT '',
        `hash` varchar(32) NOT NULL DEFAULT '',
        `debug_pass` varchar(255) DEFAULT NULL,
        `fullname` varchar(255) NOT NULL DEFAULT '',
        `is_admin` tinyint(1) NOT NULL DEFAULT '0',
        `is_programmer` tinyint(1) NOT NULL DEFAULT '0',
        `untouchible` tinyint(1) NOT NULL DEFAULT '0',
        `iscontent` tinyint(4) NOT NULL DEFAULT '0',
        `ismanag` tinyint(4) NOT NULL DEFAULT '0',
        `iscatalog` tinyint(4) NOT NULL DEFAULT '0',
        `isjournalist` tinyint(1) NOT NULL DEFAULT '0',
        `id_company` int(11) DEFAULT '0',
        `email` varchar(255) DEFAULT NULL,
        `phone` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `is_client` tinyint(1) NOT NULL DEFAULT '1',
        `is_active` tinyint(1) NOT NULL DEFAULT '1',
        `is_bye` tinyint(1) NOT NULL DEFAULT '1',
        `ord` int(11) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = "
      INSERT INTO `$table` (`id`, `onec_id`, `onec_id_company`, `title`, `img`, `login`, `key`, `hash`, `debug_pass`, `fullname`, `is_admin`, `is_programmer`, `untouchible`, `iscontent`, `ismanag`, `iscatalog`, `isjournalist`, `id_company`, `email`, `phone`, `longtxt1`, `is_client`, `is_active`, `is_bye`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
        (35, NULL, NULL, 'Администратор', NULL, 'd', 'f5a90e6da513e45848a39a1689aa8261', '555f9dc6ac9e3abbcd05b4e46a0d57f4', NULL, 'Илья Ощепков', 1, 0, 0, 1, 1, 1, 1, 0, 'ilya.oshepkov@gmail.com', '9058010809', '', 1, 1, 1, 5)
HTML;
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  
  function setup_module_url_cutaway(){
    $title = "Человеко-понятные адреса";
    $name = 'url';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `url` varchar(255) NOT NULL,
        `module` varchar(255) NOT NULL,
        `module_id` int(11) NOT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        UNIQUE KEY `url` (`url`),
        KEY `module` (`module`),
        KEY `module_id` (`module_id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `url`, `module`, `module_id`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
        (1, 'Главная', 'glavnaya', 'wed_smpl_article', 1, 0, 0),
        (2, 'О компании', 'kompanii', 'wed_smpl_article', 2, 0, 0),
        (3, 'Фотогалерея', 'fotogalereya', 'wed_smpl_article', 3, 0, 0),
        (4, 'Документы', 'dokumenty', 'wed_smpl_article', 4, 0, 0),
        (5, 'Дилеры', 'dilery', 'wed_smpl_article', 5, 0, 0),
        (6, 'Контакты', 'kontakty', 'wed_smpl_article', 6, 0, 0),
        (8, 'Блок 1', 'blok-1', 'wed_mine_block', 2, 0, 0),
        (9, 'Блок 2', 'blok-2', 'wed_mine_block', 3, 0, 0),
        (10,'Блок 3', 'blok-3', 'wed_mine_block', 4, 0, 0),
        (11,'Слайдер на главной',  'slayder-na-glavnoy', 'wed_mine_block', 5, 0, 0),
        (12,'Услуги', 'uslugi', 'wed_smpl_article', 7, 0, 0),
        (13,'Шапка сайта', 'shapka-sayta', 'wed_mine_block', 6, 0, 0),
        (14,'Главное меню', 'glavnoe-menyu', 'wed_mine_block', 7, 0, 0),
        (15,'Контент на внутренних страницах',  'kontent-na-vnutrennih-stranicah', 'wed_mine_block', 8, 0, 0),
        (16,'robots.txt',  'robots_txt', 'robots_txt', 0, 0, 0)
        
HTML;
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_mine_block_cutaway(){
    $title = "Блоки на главной странице";
    $name = 'mine_block';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `img` varchar(255) NOT NULL,
      `link` varchar(255) DEFAULT NULL,
      `longtxt2` text,
      `fl_is_fixed` tinyint(1) NOT NULL DEFAULT '0',
      `hide` tinyint(1) NOT NULL DEFAULT '0',
      `ord` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `link`, `longtxt2`, `fl_is_fixed`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
(2, 'Блок 3', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h1>Блок</h1>\r\n\r\n<h2 style="text-align: center;">Контакты</h2>\r\n</div>\r\n</div>\r\n<!-- сontacts_inretactive -->\r\n\r\n<div class="сontacts_inretactive_box">\r\n<div class="сontacts_inretactive">\r\n<div class="ya_map"><script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A71f1792e079ea2350269366fbc0037aaad93183c6e2c49cd23c1eb8b4c0ccb9e&amp;width=100%25&amp;height=500&amp;lang=ru_RU&amp;scroll=false"></script></div>\r\n\r\n<div class="сontacts_descr">\r\n<p><b>Наш адрес:</b><br />\r\n620014, г. Екатеринбург, пр. Ленина 24/8, оф. 451</p>\r\n\r\n<p><b>Телефоны:</b><br />\r\n<a href="tel:+73433723307">+7 (343) 372-33-07</a> <a href="tel:+7 (343) 371-21-45">+7 (343) 371-21-45</a></p>\r\n\r\n<p><b>Электропочта:</b><br />\r\n<a href="mailto:d1@d1.ru">d1@d1.ru</a></p>\r\n</div>\r\n<!-- End сontacts_inretactive --></div>\r\n</div>\r\n<style type="text/css">/* --- сontacts --- */\r\n.сontacts_box{\r\n  background: url("img/mc_bg.png") no-repeat center center;\r\n  background-size: cover;\r\n  min-height: 490px;\r\n  padding-bottom: 0px;\r\n}\r\n.сontacts{\r\n  padding-bottom: 20px;\r\n}\r\n.сontacts_descr{\r\n  max-width: 335px;\r\n  background: #ffffff;\r\n  margin: 0 auto;\r\n  margin-top: 10px;\r\n  padding: 15px 25px;\r\n  border: 10px solid #17a2b8;\r\n  text-align: center;\r\n}\r\n.сontacts_descr b{\r\n  font: 400 25px/30px AvantGardeGothicBdITC-Reg, Arial, sans-serif;\r\n  color: #000000;\r\n}\r\n.сontacts_descr a{\r\n  color: #000000;\r\n}\r\n/* --- End сontacts --- */\r\n  \r\n  /* --- сontacts_inretactive_box --- */\r\n.сontacts_inretactive_box{\r\n  min-height: 500px;\r\n  padding-bottom: 0px;\r\n  \r\n}\r\n.сontacts_inretactive{\r\n  padding-bottom: 20px;\r\n  position: relative;\r\n}\r\n.ya_map{\r\n  position: absolute;\r\n  top: 0;\r\n  height: 500px;\r\n  width: 100%;\r\n}\r\n.сontacts_inretactive .mtitle_box{\r\n  /*position: relative;\r\n  z-index: 1;\r\n  text-align: right;\r\n  margin-right: 15px;*/\r\n}\r\n\r\n.сontacts_inretactive .сontacts_descr{\r\n  margin: 55px 15px 15px 25px;\r\n  position: relative;\r\n  z-index: 1;\r\n  max-width: 335px;\r\n  background: #ffffff;\r\n  float: right;\r\n}\r\n@media (max-width: 768px){\r\n  .сontacts_inretactive .сontacts_descr{\r\n    margin: 0 auto 0 auto;\r\n    float: none;\r\n  }\r\n  .сontacts_inretactive{\r\n    padding-top: 525px;\r\n  }\r\n}\r\n/* --- End сontacts_inretactive --- */\r\n</style>\r\n', 0, 0, 6),
(3, 'Блок 1', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h2>Блок</h2>\r\n</div>\r\n</div>\r\n\r\n<div class="block_box bl_bg" style="">\r\n<div class="block_b" style="background: rgba(0, 0, 0, 0.3);\r\n    padding-top: 15px;\r\n    padding-bottom: 15px;">\r\n<div class="block">\r\n<h2 style="color: #fff;">Коротко о нас</h2>\r\n\r\n<p>Ни для кого не секрет, что в Сети лицом корпорации или фирмы является веб-сайт. Именно по этой причине солидные организации не жалеют денег на создание, поддержку, поисковую оптимизацию сайтов в Интернете. Первым шагом на пути к успеху является <b>разработка сайта</b>, которую лучше заказать у настоящих мастеров своего дела - студии, специализирующейся на сайтах. Дизайн студия D1.ru, предлагает своим клиентам разработку сайтов в Екатеринбурге, в Москве и Челябинске. Нам важен каждый сайт, поэтому к каждому клиенту мы находим индивидуальный подход в разработке сайта.</p>\r\n\r\n<p>Очень важна грамотная и эффективная <strong>поисковая оптимизация сайта</strong>, которая призвана привлечь на сайт целевую аудиторию, увеличить посещаемость сайта, увеличить количество заказов с сайта.<br />\r\nСсылка на Ваш сайт появляется в поисковых системах Яндекс, Google, Mail, Рамблер в тот момент, когда клиенту нужны ваши товары/услуги, он как раз ищет поставщика, к кому обратиться. Такой клиент уже лояльно относится к Вашей компании.<br />\r\nВ последнее время эффект от <strong>поисковой оптимизации сайта в Екатеринбурге</strong> увеличивается в 2 раза за год, т.к. растет количество интернет-пользователей и их активность.</p>\r\n\r\n<p><b>Дизайн-студия D1.ru занимается разработкой сайтов, поддержкой, созданием интернет-магазинов, интернет-рекламой и&nbsp;поисковой оптимизацией.</b></p>\r\n\r\n<p><b>Директор студии - Илья Крохалев</b> до создания студии 5 лет занимался разработкой сайтов, развитием городского портала E1.ru в компании Урал Релком.</p>\r\n\r\n<p><b>Студия работает с 1 марта 2005 года.</b> Многие специалисты студии работают в сфере создания сайтов более 15&nbsp;лет,</p>\r\nчто гарантирует высокое качество работы, решение любых технических вопросов.\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><b>Среди наших клиентов:</b> крупные заводы, торговые компании, турфирмы, агентства недвижимости, застройщики, известные интернет-магазины, медицинские компании и многие другие.<br />\r\n&nbsp;</p>\r\n</div>\r\n</div>\r\n</div>\r\n<style type="text/css">.bl_bg{\r\n    background-image: url(/images_ckeditor/files/test.jpg);\r\n    background-attachment: fixed;\r\n    background-size: cover;\r\n    background-position: center center;\r\n    text-shadow: 1px 1px 16px rgba(0, 0, 0, 0.8);\r\n    color: #ffffff;\r\n    padding-top: 0;\r\n    padding-bottom: 0;\r\n    margin-bottom: 0px;\r\n    /*-webkit-transform: translate3d(0,0,0);*/\r\n    -webkit-backface-visibility: hidden;\r\n  }\r\n  .admin_edit_box .bl_bg{\r\n    /*position: relative;\r\n    z-index: 1600;*/\r\n  }\r\n</style>\r\n', 0, 0, 5),
(4, 'Блок 2', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h2>Блок&nbsp;</h2>\r\n\r\n<div class="scheme">\r\n<div class="row">\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-globe fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Встречи 1 раз в неделю</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-tasks fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">График работы</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-external-link-alt fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Новые техники</div>\r\n</div>\r\n</div>\r\n\r\n<div class="row">\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="far fa-clock fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Поддержка</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-percent fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">100%</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="far fa-sun fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Качественный результат</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<style type="text/css">/* --- scheme --- */\r\n.scheme{\r\n	margin-top: 25px;\r\n  margin-bottom: 25px;\r\n}\r\n.scheme .c_img_box{\r\n  text-align:center;\r\n  /*color: #28a745;*/\r\n  padding-bottom: 25px;\r\n  padding-top: 15px;\r\n}\r\n.scheme .c_title{\r\n	text-align:center;\r\n  padding-bottom: 35px;\r\n  font-size: 24px;\r\n  font-weight: bold;\r\n}\r\n.steps{\r\n  \r\n}\r\n.steps .rim{\r\n  display: inline-block;\r\n  width: 25px;\r\n  text-align:center;\r\n}\r\n.steps .left{\r\n  /*text-align:right;*/\r\n}\r\n.steps .right{\r\n  border-left: 2px solid #37373e59;\r\n  border-right: 2px solid #37373e59;\r\n  border-top: 2px solid #37373e59;\r\n  padding-top: 20px;\r\n  padding-bottom: 20px;\r\n  /*min-height: 75px;*/\r\n}\r\n.steps .row:last-child {\r\n  \r\n  /*border-bottom: 3px solid #37373e;*/\r\n}\r\n/* --- END scheme --- */\r\n</style>\r\n', 1, 0, 4),
(5, 'Слайдер', '', 'block_mine_slider', '', 0, 0, 2),
(6, 'Шапка сайта', '', 'block_mine_header', '', 1, 0, 0),
(7, 'Меню сайта', '', 'block_mine_top_menu', '', 1, 0, 1),
(8, 'Контент на внутренних страницах', '', 'block_inner_content', '', 1, 0, 3)
HTML;
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  
  function setup_database_module_required(){
    
    $this->add_content( $this->wrap_block(  # Курсы валют
                                              $this->setup_module_currency()  ));
      
    $this->add_content( $this->wrap_block(  # Параметры
                                            $this->setup_module_config()  ));
                                            
    $this->add_content( $this->wrap_block(  # СЕО
                                            $this->setup_module_seo()  ));
                                            
    $this->add_content( $this->wrap_block(  # Оформление сайта
                                            $this->setup_module_design()  ));
    
    $this->add_content( $this->wrap_block(  # Администрирование
                                            $this->setup_module_accounts()  ));
                                            
    #$this->add_content( $this->wrap_block(  # ЧПУ
    #                                          $this->setup_module_url()  ));
  }
  
  function setup_database_module_cutaway(){ # Сайт визитка
  
    $this->add_content( $this->wrap_block(  # ЧПУ
                                              $this->setup_module_url_cutaway()  ));
                                              
    $this->add_content( $this->wrap_block(  # Блоки на главной странице
                                              $this->setup_module_mine_block_cutaway()  ));
  }
  
  function setup_database_module_all(){
    
    $this->setup_database_module_required();
    
    
  }
  
  function setup_database_module(){
    $output = '';
    if( $this->is_database_access() ){
      
      $this->setup_database_module_all();
                                              
      
    }else{
      $this->add_content( $this->wrap_block( $this->test_database_access() ));
    }
    
    return $output; 
  }
  
  function delete_database_module(){
    $output = ''; 
    if( $this->is_database_access() ){
      
      $this->add_content( $this->wrap_block(  $this->delete_table()  ));
    
    }else{
      $this->add_content( $this->wrap_block( $this->test_database_access() ));
    }
    
    return $output; 
  }

}
