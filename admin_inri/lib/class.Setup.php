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
    <meta name="author" content="'.SITE_NAME.'">
    <link rel="shortcut icon" type="image/x-icon" href="'.ADMIN_FAVICON.'">
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
        <a class="navbar-brand" href="/'.ADM_DIR.'/'.$this->script_name.'">'.CMS_NAME.'</a>
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
  
  function sql_insert_database_table( $table_name, &$sql_insert){
    $output = '';
    
    if($sql_insert) {
      $this->pdo->query("TRUNCATE TABLE  `$table_name`");
      if($q_insert = $this->pdo->query($sql_insert)){
        $output .= '
      <div class="alert alert-success" role="alert">
        Таблица '.$table_name.' Заполнена значениями по умолчанию!
      </div>';  
      }else{
        $output .= '
        <div class="alert alert-danger" role="alert">
          Не удалось заполнить таблицу '.$table_name.' значениями по умолчанию!!
        </div>';
      }
      
    }
    
    return $output;
  }
  
  function sql_def_insert_database_table( $module_name, $table_name, &$sql_insert, $script_name = null){
    $output = '';
    if($ins_text = $this->sql_insert_database_table( $table_name, $sql_insert  )){
      $output .= '
      <h4>Наполнение модуля `'.$module_name.'`</h4>';
      $output .= $ins_text;
      if($script_name){
        $output .= '
        <div class="alert alert-primary" role="alert">
          Управление модулем `<b><a href = "'.IA_URL.$script_name.'" target = "_blank">'.$module_name.'</a></b>`.
        </div>';
      }
    }
    return $output;
  }
    
  function setup_database_table($module_name, $table_name, &$sql, &$sql_insert = null, $script_name = null  ){
   
    $output = $error = '';
    
    
    $output .= '
    <h4>Установка модуля `'.$module_name.'`</h4>';
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
        $output .= $this->sql_insert_database_table($table_name, $sql_insert);
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
  
  function create_img_dir( $name ){
    if (!is_dir("../images")){
      mkdir("../images");
      chmod ("../images", 0777);
    }
    
    if (!is_dir("../images/".$name )){
      mkdir ( "../images/".$name );
      chmod ( "../images/".$name, 0755 );
    }
    
    if (!is_dir("../images/".$name."/orig")){
      mkdir("../images/".$name."/orig");
      chmod ("../images/".$name."/orig", 0755);
    }
    
    if (!is_dir("../images/".$name."/slide")){
      mkdir("../images/".$name."/slide");
      chmod ("../images/".$name."/slide", 0755);
    }
    
    if (!is_dir("../images/".$name."/temp")){
      mkdir("../images/".$name."/temp");
      chmod ("../images/".$name."/temp", 0755);
    }
    
    if (!is_dir("../images/".$name."/variations")){
      mkdir("../images/".$name."/variations");
      chmod ("../images/".$name."/variations", 0755);
    }
  }
  
  function create_file_dir( $name ){
    
    $this->create_img_dir( $name ); 
    
    if (!is_dir("../images/".$name."/files")){
      mkdir("../images/".$name."/files");
      chmod ("../images/".$name."/files", 0755);
    }
  }
  
  function copy_file( $file_name, $source, $target ){ 
    $output = '';
    try { 
      if($current_file = file_get_contents( $source ) ){
        file_put_contents($target, $current_file );
      }
    } 
    catch (PDOException $e) { 
      $output .= '
        <div class="alert alert-danger" role="alert">
          Ошибка копирования файла `'.$file_name.'` Источник `'.$source.'`!
        </div>';
      #pri( 'Ошибка копирования файла `'.$file_name.'` Источник `'.$source.'`' );
    }
     
    /*$ch = curl_init( $source );
    $fp = fopen( $target, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);*/
    
    return $output;
  }
  
  function copy_img_module( $name, $source_site ){
    $output = '';
    $table = DB_PFX.$name;
    $items = db::select('*', $table, null, null, null, null, 0 );
    
    foreach($items as $item){
      if( isset($item['img']) && $item['img'] ){
        
        $output .= $this->copy_file(  $item['img'], 
                                      $source_site.'/images/'.$name.'/orig/'.$item['img'],
                                      '../images/'.$name.'/orig/'.$item['img']  ); 
                         
        $output .= $this->copy_file(  $item['img'], 
                                      $source_site.'/images/'.$name.'/slide/'.$item['img'],
                                      '../images/'.$name.'/slide/'.$item['img']  );
      }
    }
    
    return $output;
  }
  
  
  function copy_file_module( $name, $source_site ){
    $output = '';
    $table = DB_PFX.$name;
    $items = db::select('*', $table, null, null, null, null, 0 );
    
    foreach($items as $item){
      
      if( isset($item['img']) && $item['img'] ){
        
        $output .= $this->copy_file(  $item['img'], 
                                      $source_site.'/images/'.$name.'/orig/'.$item['img'],
                                      '../images/'.$name.'/orig/'.$item['img']  ); 
                         
        $output .= $this->copy_file(  $item['img'], 
                                      $source_site.'/images/'.$name.'/slide/'.$item['img'],
                                      '../images/'.$name.'/slide/'.$item['img']  );
      }
      
      if( isset($item['file']) && $item['file'] ){
        
        $output .= $this->copy_file(  $item['file'], 
                                      $source_site.'/images/'.$name.'/files/'.$item['file'],
                                      '../images/'.$name.'/files/'.$item['file']  ); 
        
      }
    }
    
    return $output;
  }
  
  function create_cat_img_dir( $name ){
    
    parent::create_img_dir();
    
    if (!is_dir("../images/".$name."/cat")){
      mkdir("../images/".$name."/cat");
      chmod ("../images/".$name."/cat", 0755);
    }
    
    if (!is_dir("../images/".$name."/cat/orig")){
      mkdir("../images/".$name."/cat/orig");
      chmod ("../images/".$name."/cat/orig", 0755);
    }
    
    if (!is_dir("../images/".$name."/cat/slide")){
      mkdir("../images/".$name."/cat/slide");
      chmod ("../images/".$name."/cat/slide", 0755);
    }
    
    if (!is_dir("../images/".$name."/cat/temp")){
      mkdir("../images/".$name."/cat/temp");
      chmod ("../images/".$name."/cat/temp", 0755);
    }
        if (!is_dir("../images/".$name."/cat/variations")){
      mkdir("../images/".$name."/cat/variations");
      chmod ("../images/".$name."/cat/variations", 0755);
    }
  }
  
  #--------------------- setup_database_module_required --------------------- 
  function setup_module_currency_onlineshop( $title, $name ){
    
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
  
  function setup_module_config( $title, $name ){
    
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
  
  function setup_module_seo( $title, $name ){
    
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
  
  function setup_module_design( $title, $name ){
    
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
(1, 'Стили', 'user_style', 2, 130, 0, '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" >\r\n\r\n<style>\r\n  .test{\r\n	padding: 20px;\r\n  }\r\n  .mine_slider {\r\n    /*max-width: 1920px;*/\r\n  }\r\n</style>\r\n\r\n', 'Пользовательские css стили'),
(2, 'Скрипты, счетчики', 'user_script', 2, 120, 0, '<script type="text/javascript">\r\n\r\n</script>\r\n', 'Яндекс метрика, liveinternet.ru и пользовательские js скрипты'),
(3, 'Файлы', 'user_file', 3, 140, 0, '', 'Пользовательские файлы. ( путь до папки /images_ckeditor/files <a href = "/images_ckeditor/files/test.jpg" target = "_blank">тест</a> )'),
(4, 'meta теги', 'user_meta', 2, 90, 0, '', 'Подтверждение прав: вембмастер, метрика и др.'),
(5, 'robots.txt', 'user_robots', 2, 100, 0, 'User-agent: * \r\nHost:\r\nSitemap: /sitemap.xml\r\n', 'Пользовательские css стили'),
(6, 'Логотип', 'user_logo', 0, 150, 0, '/images_ckeditor/files/logo.png', 'Логотип. ( путь до папки /images_ckeditor/files <a href = "/images_ckeditor/files/test.jpg" target = "_blank">тест</a> )'),
(7, 'Слоган', 'user_site_slogan', 0, 160, 0, 'Слоган', ''),
(8, 'Фавикон', 'user_favicon', 0, 170, 0, '/images_ckeditor/files/favicon.ico', '');
HTML;
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_url( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = '';
    
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
  
  function setup_module_accounts( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(20) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) DEFAULT NULL,
        `img` varchar(255) DEFAULT NULL,
        `login` varchar(255) NOT NULL DEFAULT '',
        `key` varchar(32) NOT NULL DEFAULT '',
        `hash` varchar(32) NOT NULL DEFAULT '',
        `fullname` varchar(255) NOT NULL DEFAULT '',
        `is_admin` tinyint(1) NOT NULL DEFAULT '0',
        `is_programmer` tinyint(1) NOT NULL DEFAULT '0',
        `untouchible` tinyint(1) NOT NULL DEFAULT '0',
        `iscontent` tinyint(4) NOT NULL DEFAULT '0',
        `ismanag` tinyint(4) NOT NULL DEFAULT '0',
        `iscatalog` tinyint(4) NOT NULL DEFAULT '0',
        `isjournalist` tinyint(1) NOT NULL DEFAULT '0',
        `email` varchar(255) DEFAULT NULL,
        `phone` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `login`, `key`, `hash`, `fullname`, `is_admin`, `is_programmer`, `untouchible`, `iscontent`, `ismanag`, `iscatalog`, `isjournalist`, `email`, `phone`, `longtxt1`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
      (1, 'Администратор', '1552249940.jpeg', 'd', '1d319e1b83c1b7ec90328bcca7e6e200', '7d86b76f7b71aae04d6cd2d59090b4e9', 'Илья Ощепков', 1, 1, 0, 1, 1, 1, 1, 'ilya.oshepkov@gmail.com', '9058010809', '', 0, 0);
HTML;
    $this->create_img_dir( $name );
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_mine_block( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = '';
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
    
    $this->create_img_dir( $name );
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_carusel( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = '';
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `link` varchar(255) DEFAULT NULL,
        `txt1` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    
    $this->create_img_dir( $name );
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_reservations( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = '';
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `date` varchar(10) DEFAULT NULL,
        `userIp` varchar(255) DEFAULT NULL,
        `userName` varchar(255) DEFAULT NULL,
        `userPhone` varchar(255) DEFAULT NULL,
        `userMail` varchar(255) DEFAULT NULL,
        `userStatus` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_admin_logs( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `ip` varchar(15) NOT NULL,
      `user_id` int(11) NOT NULL,
      `date_time` datetime NOT NULL,
      `action` int(11) NOT NULL,
      `item_id` int(11) NOT NULL,
      `changes` text NOT NULL,
      `script` varchar(16) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `ip` (`ip`,`user_id`,`date_time`,`action`,`script`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = '';
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_all_log( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `type` varchar(255) NOT NULL,
        `user_id` varchar(255) NOT NULL,
        `ip` varchar(15) DEFAULT NULL,
        `int_ip` int(10) DEFAULT NULL,
        `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
        `dump_data` text,
        `query` text,
        `module` varchar(255) NOT NULL,
        `module_id` int(11) NOT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = '';
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_all_images( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = '';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `module` varchar(255) NOT NULL,
        `module_id` int(11) NOT NULL,
        `img` varchar(255) NOT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        `module_ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    
    $this->create_img_dir( $name );
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_all_files( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = '';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `module` varchar(255) NOT NULL,
        `module_id` int(11) NOT NULL,
        `img` varchar(255) NOT NULL,
        `file` varchar(255) NOT NULL,
        `longtxt1` text,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        `module_ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    
    $this->create_file_dir( $name );
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  
  #--------------------- setup_database_module_cutaway --------------------- 
  function insert_def_module_url_cutaway( $title, $name ){
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `url`, `module`, `module_id`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
        (1, 'Главная', 'glavnaya', 'inri_smpl_article', 1, 0, 0),
        (2, 'О компании', 'kompanii', 'inri_smpl_article', 2, 0, 0),
        (3, 'Фотогалерея', 'fotogalereya', 'inri_smpl_article', 3, 0, 0),
        (4, 'Документы', 'dokumenty', 'inri_smpl_article', 4, 0, 0),
        (5, 'Дилеры', 'dilery', 'inri_smpl_article', 5, 0, 0),
        (6, 'Контакты', 'kontakty', 'inri_smpl_article', 6, 0, 0),
        (8, 'Блок 1', 'blok-1', 'inri_mine_block', 2, 0, 0),
        (9, 'Блок 2', 'blok-2', 'inri_mine_block', 3, 0, 0),
        (10,'Блок 3', 'blok-3', 'inri_mine_block', 4, 0, 0),
        (11,'Слайдер на главной',  'slayder-na-glavnoy', 'inri_mine_block', 5, 0, 0),
        (12,'Услуги', 'uslugi', 'inri_smpl_article', 7, 0, 0),
        (13,'Шапка сайта', 'shapka-sayta', 'inri_mine_block', 6, 0, 0),
        (14,'Главное меню', 'glavnoe-menyu', 'inri_mine_block', 7, 0, 0),
        (15,'Контент на внутренних страницах',  'kontent-na-vnutrennih-stranicah', 'inri_mine_block', 8, 0, 0),
        (16,'robots.txt',  'robots_txt', 'robots_txt', 0, 0, 0)
        
HTML;
        
    return $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name );
  }
  
  function insert_def_module_mine_block_cutaway( $title, $name ){
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `link`, `longtxt2`, `fl_is_fixed`, `hide`, `ord`) VALUES ";  
    $sql_insert .=<<<HTML
(2, 'Блок 3', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h1>Блок</h1>\r\n\r\n<h2 style="text-align: center;">Контакты</h2>\r\n</div>\r\n</div>\r\n<!-- сontacts_inretactive -->\r\n\r\n<div class="сontacts_inretactive_box">\r\n<div class="сontacts_inretactive">\r\n<div class="ya_map"><script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Abbe514650299016758f03759051467d1a8adc3faf2e49e2556ec55580a030390&amp;width=100%25&amp;height=500&amp;lang=ru_RU&amp;scroll=false"></script></div>\r\n\r\n<div class="сontacts_descr">\r\n<p><b>Наш адрес:</b><br />\r\n620100, г. Екатеринбург, ул Сибирский тракт 12/2, офис 404</p>\r\n\r\n<p><b>Телефоны:</b><br />\r\n<a href="tel:+79058010809">+7 (905) 801-08-09</a></p>\r\n\r\n<p><b>Электропочта:</b><br />\r\n<a href="mailto:1@in-ri.ru">1@in-ri.ru</a></p>\r\n</div>\r\n<!-- End сontacts_inretactive --></div>\r\n</div>\r\n<style type="text/css">/* --- сontacts --- */\r\n.сontacts_box{\r\n  background: url("img/mc_bg.png") no-repeat center center;\r\n  background-size: cover;\r\n  min-height: 490px;\r\n  padding-bottom: 0px;\r\n}\r\n.сontacts{\r\n  padding-bottom: 20px;\r\n}\r\n.сontacts_descr{\r\n  max-width: 335px;\r\n  background: #ffffff;\r\n  margin: 0 auto;\r\n  margin-top: 10px;\r\n  padding: 15px 25px;\r\n  border: 10px solid #17a2b8;\r\n  text-align: center;\r\n}\r\n.сontacts_descr b{\r\n  font: 400 25px/30px AvantGardeGothicBdITC-Reg, Arial, sans-serif;\r\n  color: #000000;\r\n}\r\n.сontacts_descr a{\r\n  color: #000000;\r\n}\r\n/* --- End сontacts --- */\r\n  \r\n  /* --- сontacts_inretactive_box --- */\r\n.сontacts_inretactive_box{\r\n  min-height: 500px;\r\n  padding-bottom: 0px;\r\n  \r\n}\r\n.сontacts_inretactive{\r\n  padding-bottom: 20px;\r\n  position: relative;\r\n}\r\n.ya_map{\r\n  position: absolute;\r\n  top: 0;\r\n  height: 500px;\r\n  width: 100%;\r\n}\r\n.сontacts_inretactive .mtitle_box{\r\n  /*position: relative;\r\n  z-index: 1;\r\n  text-align: right;\r\n  margin-right: 15px;*/\r\n}\r\n\r\n.сontacts_inretactive .сontacts_descr{\r\n  margin: 55px 15px 15px 25px;\r\n  position: relative;\r\n  z-index: 1;\r\n  max-width: 335px;\r\n  background: #ffffff;\r\n  float: right;\r\n}\r\n@media (max-width: 768px){\r\n  .сontacts_inretactive .сontacts_descr{\r\n    margin: 0 auto 0 auto;\r\n    float: none;\r\n  }\r\n  .сontacts_inretactive{\r\n    padding-top: 525px;\r\n  }\r\n}\r\n/* --- End сontacts_inretactive --- */\r\n</style>\r\n', 0, 0, 6),
(3, 'Блок 1', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h2>Блок</h2>\r\n</div>\r\n</div>\r\n\r\n<div class="block_box bl_bg" style="">\r\n<div class="block_b" style="background: rgba(0, 0, 0, 0.3);\r\n    padding-top: 150px;\r\n    padding-bottom: 150px;">\r\n<div class="block">\r\n<h2 style="color: #fff;">ПРОЕКТИРОВАНИЕ И ДИЗАЙН</h2>\r\n\r\n<p>Внешний вид сайта - лицо компании. Важно, чтобы он был не только красивым, но и удобным. Интуитивно понятный интерфейс пользователя плюс отзывчивый дизайн увеличивают конверсию и продажи.</p>\r\n\r\n<h2 style="color: #fff;">РАЗРАБОТКА</h2>\r\n\r\n<p>На этапе разработки учитывается адаптивность сайта. Он должен корректно отображаться в любом браузере и на всех видах устройств. Удобная система управления сайтом позволит вам вести проект самостоятельно.</p>\r\n\r\n<h2 style="color: #fff;">ПРОДВИЖЕНИЕ И ПОДДЕРЖКА</h2>\r\n\r\n<p>Как не потеряться в медиа пространстве? Мы готовы сотрудничать с вами, отзываясь на любые просьбы и следим за тем, чтобы ваш сайт был в топе поисковиков.</p>\r\n\r\n<p><b>ВЕБ-студия <a href="//in-ri.ru" style="color: #dc3545;">in-ri.ru</a> занимается разработкой сайтов, поддержкой, созданием интернет-магазинов, интернет-рекламой и&nbsp;поисковой оптимизацией.</b></p>\r\n</div>\r\n</div>\r\n</div>\r\n<style type="text/css">.bl_bg{\r\n    background-image: url(/images_ckeditor/files/test.jpg);\r\n    background-attachment: fixed;\r\n    background-size: cover;\r\n    background-position: center center;\r\n    text-shadow: 1px 1px 16px rgba(0, 0, 0, 0.8);\r\n    color: #ffffff;\r\n    padding-top: 0;\r\n    padding-bottom: 0;\r\n    margin-bottom: 0px;\r\n    /*-webkit-transform: translate3d(0,0,0);*/\r\n    -webkit-backface-visibility: hidden;\r\n  }\r\n  .admin_edit_box .bl_bg{\r\n    /*position: relative;\r\n    z-index: 1600;*/\r\n  }\r\n</style>\r\n', 0, 0, 5),
(4, 'Блок 2', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h2>Блок&nbsp;</h2>\r\n\r\n<div class="scheme">\r\n<div class="row">\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-globe fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Встречи 1 раз в неделю</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-tasks fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">График работы</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-external-link-alt fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Новые техники</div>\r\n</div>\r\n</div>\r\n\r\n<div class="row">\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="far fa-clock fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Поддержка</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-percent fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">100%</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="far fa-sun fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Качественный результат</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<style type="text/css">/* --- scheme --- */\r\n.scheme{\r\n	margin-top: 25px;\r\n  margin-bottom: 25px;\r\n}\r\n.scheme .c_img_box{\r\n  text-align:center;\r\n  /*color: #28a745;*/\r\n  padding-bottom: 25px;\r\n  padding-top: 15px;\r\n}\r\n.scheme .c_title{\r\n	text-align:center;\r\n  padding-bottom: 35px;\r\n  font-size: 24px;\r\n  font-weight: bold;\r\n}\r\n.steps{\r\n  \r\n}\r\n.steps .rim{\r\n  display: inline-block;\r\n  width: 25px;\r\n  text-align:center;\r\n}\r\n.steps .left{\r\n  /*text-align:right;*/\r\n}\r\n.steps .right{\r\n  border-left: 2px solid #37373e59;\r\n  border-right: 2px solid #37373e59;\r\n  border-top: 2px solid #37373e59;\r\n  padding-top: 20px;\r\n  padding-bottom: 20px;\r\n  /*min-height: 75px;*/\r\n}\r\n.steps .row:last-child {\r\n  \r\n  /*border-bottom: 3px solid #37373e;*/\r\n}\r\n/* --- END scheme --- */\r\n</style>\r\n', 1, 0, 4),
(5, 'Слайдер', '', 'block_mine_slider', '', 0, 0, 2),
(6, 'Шапка сайта', '', 'block_mine_header', '', 1, 0, 0),
(7, 'Меню сайта', '', 'block_mine_top_menu', '', 1, 0, 1),
(8, 'Контент на внутренних страницах', '', 'block_inner_content', '', 1, 0, 3),
(9, 'Подвал сайта', '', 'block_mine_footer', '', 1, 0, 7);
HTML;
    
    return $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name );
  }
  
  function insert_def_module_carusel_cutaway( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `link`, `txt1`, `longtxt1`, `img_alt`, `img_title`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
(1, 'Слайд №1', '1532522017.jpg', '', '', '', '', '', 0, 0),
(2, 'Слайд №2', '1532521995.jpg', '', 'Текст', '<p>Описание описание описание описание</p>\r\n', 'Alt изображение', 'Title изображение', 0, 1),
(3, 'Слайд №3', '1532522040.jpg', '', '', '', '', '', 0, 2),
(4, 'Слайд №4', '1531731541.jpg', '', '', '', '', '', 0, 3),
(5, 'Слайд №5', '1531731565.jpg', '', '', '', '', '', 0, 4);
HTML;
    
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CUTAWAY );
    
    return $output;
  }
  
  function insert_def_module_all_images_cutaway( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `module`, `module_id`, `img`, `longtxt1`, `longtxt2`, `seo_h1`, `seo_title`, `seo_description`, `img_alt`, `img_title`, `hide`, `ord`, `module_ord`) VALUES ";
    $sql_insert .=<<<HTML
(70, 'yamaha scr950.jpg', 'inri_smpl_article', 3, '1532522961_10.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(69, 'QOVZzEETzGY.jpg', 'inri_smpl_article', 3, '1532522961_9.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(68, 'HONDACB900FHornet-5190_3.jpg', 'inri_smpl_article', 3, '1532522961_8.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(63, '14658847586171.jpg', 'inri_smpl_article', 3, '1532522961_3.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(64, '14658847586242.jpg', 'inri_smpl_article', 3, '1532522961_4.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(65, '14658847586293.jpg', 'inri_smpl_article', 3, '1532522961_5.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(66, '14764684961551.jpg', 'inri_smpl_article', 3, '1532522961_6.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(67, '14785962379880.jpg', 'inri_smpl_article', 3, '1532522961_7.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(62, '14658847586130.jpg', 'inri_smpl_article', 3, '1532522961_2.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(61, '14657292234510.jpg', 'inri_smpl_article', 3, '1532522961_1.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(60, '4.jpg', 'inri_smpl_article', 3, '1532522961_0.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(71, 'mMAPhsLGD6w.jpg', 'inri_smpl_article', 3, '1532523022_0.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(72, 'QPyh7YBBraM.jpg', 'inri_smpl_article', 3, '1532523023_1.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(73, 'rNqjPvV3a2Q.jpg', 'inri_smpl_article', 3, '1532523024_2.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0);
HTML;
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CUTAWAY );
    
    return $output;
  }
  
  function insert_def_module_all_files_cutaway( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `module`, `module_id`, `img`, `file`, `longtxt1`, `img_alt`, `img_title`, `hide`, `ord`, `module_ord`) VALUES ";
    $sql_insert .=<<<HTML
(1, '1972.pdf', 'inri_smpl_article', 4, '', '1530877350_0.pdf', NULL, NULL, NULL, 0, 0, 0),
(2, '1973.pdf', 'inri_smpl_article', 4, '', '1530877350_1.pdf', NULL, NULL, NULL, 0, 0, 0),
(3, '1974.pdf', 'inri_smpl_article', 4, '', '1530877350_2.pdf', NULL, NULL, NULL, 0, 0, 0),
(4, '1975.pdf', 'inri_smpl_article', 4, '', '1530877350_3.pdf', NULL, NULL, NULL, 0, 0, 0),
(5, '1976.pdf', 'inri_smpl_article', 4, '', '1530877350_4.pdf', NULL, NULL, NULL, 0, 0, 0);
HTML;
    
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CUTAWAY );
    $output .= $this->copy_file_module( $name, SOURCE_SITE_CUTAWAY );
    
    return $output;
  }
  
  function setup_module_smpl_article_cutaway( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `date` varchar(10) DEFAULT NULL,
        `link` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `fl_mine_menu` tinyint(1) DEFAULT NULL,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `orm_search_name` varchar(255) DEFAULT NULL,
        `orm_search` text,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `date`, `link`, `longtxt1`, `longtxt2`, `fl_mine_menu`, `seo_h1`, `seo_title`, `seo_description`, `seo_keywords`, `img_alt`, `img_title`, `orm_search_name`, `orm_search`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
(1, 'Главная', '', '', '/', '', '', 1, '', '', '', '', '', '', 'ГЛАВНЫЙ', '', 0, 0),
(2, 'О компании', '', '', '', '', '<h3>Пример текста &laquo;О компании&raquo;, как его написать &ndash; &laquo;пикап&raquo; потенциального клиента</h3>\r\n\r\n<p>Вы на рынке с 2002 года? Мне всё равно. &laquo;АвтоВАЗ&raquo; на рынке дольше вас&hellip; У вас хорошая динамика развития и молодой, дружный коллектив? Отлично, то есть, опыта у сотрудников маловато&hellip; Закупили дорогое немецкое оборудование (в кредит?), когда рубль балансировал на ободке унитаза? Значит, теперь мне за это расплачиваться? Прощайте!</p>\r\n\r\n<p>Как написать текст на страницу &laquo;О компании&raquo; и выбросить из словосочетания &laquo;потенциальный клиент&raquo; первое слово? Сделать это в среде &laquo;неправильных&raquo; клиентов. Ведь они, как девушка, которую постоянно атакуют пикаперы: интерес проявляет, но в машину не садится.</p>\r\n\r\n<p>Проблема в том, что у вас все как у всех: низкие цены, надежное оборудование и специалисты &ndash; профессионалы своего дела, у которых клиентоориенитрованность на нуле.</p>\r\n\r\n<p>Если не знаете, как написать текст о компании для сайта, и нужны примеры, то эта работа для <a href="//in-ri.ru" target="_blank">нас</a>.</p>\r\n', 1, '', '', '', '', '', '', 'О КОМПАНИЯ', 'ПРИМЕР ТЕКСТ LAQUO О КОМПАНИЯ RAQUO КАК ЕГО НАПИСАТЬ NDASH LAQUO ПИКАП RAQUO ПОТЕНЦИАЛЬНЫЙ КЛИЕНТ ВЫ НА РЫНОК С 2002 ГОД Я ВЕСЬ РАВНО LAQUO АВТОВАЗ RAQUO НА РЫНОК ДОЛГИЙ ВЫ HELLIP У ВЫ ХОРОШИЙ ДИНАМИК РАЗВИТИЕ И МОЛОДАЯ ДРУЖНЫЙ КОЛЛЕКТИВ ОТЛИЧНО ТО ЕСТЬ ОПЫТ У СОТРУДНИК МАЛОВАТЫЙ HELLIP ЗАКУПИТЬ ДОРОГОЙ НЕМЕЦКИЙ ОБОРУДОВАНИЕ В КРЕДИТ КОГДА РУБЛЬ БАЛАНСИРОВАТЬ НА ОБОДОК УНИТАЗ ЗНАЧИТ ТЕПЕРЬ Я ЗА ЭТО РАСПЛАЧИВАТЬСЯ ПРОЩАТЬ КАК НАПИСАТЬ ТЕКСТ НА СТРАНИЦА LAQUO О КОМПАНИЯ RAQUO И ВЫБРОСИТЬ ИЗ СЛОВОСОЧЕТАНИЕ LAQUO ПОТЕНЦИАЛЬНЫЙ КЛИЕНТ RAQUO ПЕРВЫЙ СЛОВО СДЕЛАТЬ ЭТО В СРЕДА LAQUO НЕПРАВИЛЬНЫЙ RAQUO КЛИЕНТ ВЕДЬ ОНИ КАК ДЕВУШКА КОТОРЫЙ ПОСТОЯННО АТАКОВАТЬ ПИКАПЕР ИНТЕРЕС ПРОЯВЛЯТЬ НО В МАШИН НЕ САДИТЬСЯ ПРОБЛЕМА В ТОМ ЧТО У ВЫ ВСЕ КАК У ВЕСЬ НИЗКИЙ ЦЕНА НАДЕЖНЫЙ ОБОРУДОВАНИЕ И СПЕЦИАЛИСТ NDASH ПРОФЕССИОНАЛ СВОЕ ДЕТЬ У КОТОРЫЙ КЛИЕНТООРИЕНИТРОВАННОСТЬ НА НУЛЬ ЕСЛИ НЕ ЗНАТЬ КАК НАПИСАТЬ ТЕКСТ О КОМПАНИЯ ДЛЯ САЙТ И НУЖНЫЙ ПРИМЕР ТО ЭТОТ РАБОТА ДЛЯ МЫ', 0, 3),
(3, 'Фотогалерея', '', '', '', '', '', 1, '', '', '', '', '', '', 'ФОТОГАЛЕРЕЯ', '', 0, 1),
(4, 'Документы', '', '', '', '', '', 1, '', '', '', '', '', '', 'ДОКУМЕНТ', '', 0, 2),
(5, 'Дилеры', '', '', '', '', '', 0, '', '', '', '', '', '', 'ДИЛЕР', '', 0, 5),
(6, 'Контакты', '', '', '', '', '<p><b>Директор: Ощепков Илья Александрович</b></p>\r\n\r\n<p><b>E-mail</b>: <a href="mailto:1@in-ri.ru">1@in-ri.ru</a></p>\r\n\r\n<p><b>Телефоны в Екатеринбурге:</b></p>\r\n\r\n<p><b>Сот. <a href = "tel:+79058010809">+7 (905) 801-08-09</a></b></p>\r\n\r\n<p><b>Адрес:</b> 620100, г. Екатеринбург, ул Сибирский тракт 12/2, офис 404</p>\r\n\r\n<p><b>Время работы:</b> пн-пт 10:00-19:00, сб-вс 11:00-18:00<br />\r\n&nbsp;</p>\r\n<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Abbe514650299016758f03759051467d1a8adc3faf2e49e2556ec55580a030390&amp;width=100%25&amp;height=500&amp;lang=ru_RU&amp;scroll=false"></script>\r\n\r\n<p>&nbsp;</p>\r\n\r\n', 1, '', '', '', '', '', '', 'КОНТАКТ', 'ДИРЕКТОР ОЩЕПОК ИЛЬЯ АЛЕКСАНДР E-MAIL 1 IN-RI RU ТЕЛЕФОН В ЕКАТЕРИНБУРГ СОТЫ 7 905 801-08-09 АДРЕС 620100 Г ЕКАТЕРИНБУРГ УТЬ СИБИРСКИЙ ТРАКТ 12 2 ОФИС 404 ВРЕМЯ РАБОТА ПН-ПТ 10 00-19 00 СБ-ВС 11 00-18 00 NBSP NBSP', 0, 4),
(7, 'Услуги', '', '', '', '', '<ul>\r\n	<li>Услуга 1</li>\r\n	<li>Услуга 2</li>\r\n	<li>Услуга 3</li>\r\n</ul>\r\n', 0, '', '', '', '', '', '', 'УСЛУГА', 'УСЛУГА 1 УСЛУГА 2 УСЛУГА 3', 0, 6);
HTML;
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  #--------------------- setup_database_module_corporate ---------------------
  function insert_def_module_carusel_corporate( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `link`, `txt1`, `longtxt1`, `img_alt`, `img_title`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
(1, 'Слайд №1', '1531731471.jpg', '', '', '', '', '', 0, 0),
(2, 'Слайд №2', '1531731498.jpg', '', 'Текст', '<p>Описание описание описание описание</p>\r\n', 'Alt изображение', 'Title изображение', 0, 1),
(3, 'Слайд №3', '1531731518.jpg', '', '', '', '', '', 0, 2),
(4, 'Слайд №4', '1531731541.jpg', '', '', '', '', '', 0, 3),
(5, 'Слайд №5', '1531731565.jpg', '', '', '', '', '', 0, 4);
HTML;
    
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CORPORATE );
    
    return $output;
  }
  
  function insert_def_module_url_corporate( $title, $name ){
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `url`, `module`, `module_id`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
      (8, 'Блок 1', 'blok', 'wed_mine_block', 2, 0, 6),
      (9, 'Блок 2', 'blok-74548', 'wed_mine_block', 3, 0, 7),
      (10, 'Блок 3', 'blok-86291', 'wed_mine_block', 4, 0, 8),
      (11, 'Слайдер на главной', 'slayder-na-glavnoy', 'wed_mine_block', 5, 0, 9),
      (13, 'Шапка сайта', 'shapka-sayta', 'wed_mine_block', 6, 0, 11),
      (14, 'Главное меню', 'glavnoe-menyu', 'wed_mine_block', 7, 0, 12),
      (15, 'Контент на внутренних страницах', 'kontent-na-vnutrennih-stranicah', 'wed_mine_block', 8, 0, 13),
      (16, 'robots.txt', 'robots_txt', 'robots_txt', 0, 0, 14),
      (17, 'Главное меню', 'glavnoe-menyu-65489', 'wed_cat_articles', 1, 0, 15),
      (18, 'Главная', 'glavnaya', 'wed_cat_articles', 2, 0, 16),
      (19, 'О компании', 'kompanii', 'wed_cat_articles', 3, 0, 17),
      (20, 'Контакты', 'kontakty', 'wed_cat_articles', 4, 0, 18),
      (21, 'Статьи', 'stati', 'wed_cat_articles', 5, 0, 19),
      (22, 'Основатель диджитал-студии Finch — о веб-дизайне в России и США', 'osnovatel-didzhitalstudii-finch-o', 'wed_articles', 1, 0, 20),
      (23, 'Киану Ривз: «Россия ассоциируется у меня с моральной силой»', 'kianu-rivz-rossiya-associiruetsya', 'wed_articles', 2, 0, 21),
      (24, 'От бритвы до автомобиля: почему мир переходит к сервисам по подписке', 'britvy-do-avtomobilya-pochemu-mir', 'wed_articles', 3, 0, 22),
      (25, 'Контент', 'kontent', 'wed_cat_articles', 6, 0, 23),
      (26, 'Фотогалерея', 'fotogalereya', 'wed_cat_articles', 7, 0, 24),
      (27, 'Документы', 'dokumenty', 'wed_cat_articles', 8, 0, 25),
      (28, 'Новости', 'news', 'wed_news', 0, 0, 26),
      (29, 'Блог', 'blog', 'wed_blog', 0, 0, 27),
      (30, 'Новости', 'novosti', 'wed_cat_articles', 9, 0, 28),
      (31, 'Петросян отреагировал на сообщения о разделе имущества со Степаненко', 'petrosyan-otreagiroval-na-soobscheniya-o', 'wed_news', 1, 0, 29),
      (32, 'Ученые назвали причину исчезновения кораблей в Бермудском треугольнике', 'uchenye-nazvali-prichinu-ischeznoveniya', 'wed_news', 2, 0, 30),
      (33, 'Марафон «Европа-Азия» соберёт более шести тысяч участников', 'marafon-evropaaziya-soberyot-bolee', 'wed_news', 3, 0, 31);
HTML;
        
    return $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name );
  }
  
  function insert_def_module_mine_block_corporate( $title, $name ){
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `link`, `longtxt2`, `fl_is_fixed`, `hide`, `ord`) VALUES ";  
    $sql_insert .=<<<HTML
(2, 'Блок 3', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h1>Блок</h1>\r\n\r\n<h2 style="text-align: center;">Контакты</h2>\r\n</div>\r\n</div>\r\n<!-- сontacts_inretactive -->\r\n\r\n<div class="сontacts_inretactive_box">\r\n<div class="сontacts_inretactive">\r\n<div class="ya_map"><script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Abbe514650299016758f03759051467d1a8adc3faf2e49e2556ec55580a030390&amp;width=100%25&amp;height=500&amp;lang=ru_RU&amp;scroll=false"></script></div>\r\n\r\n<div class="сontacts_descr">\r\n<p><b>Наш адрес:</b><br />\r\n620100, г. Екатеринбург, ул Сибирский тракт 12/2, офис 404</p>\r\n\r\n<p><b>Телефоны:</b><br />\r\n<a href="tel:+79058010809">+7 (905) 801-08-09</a></p>\r\n\r\n<p><b>Электропочта:</b><br />\r\n<a href="mailto:1@in-ri.ru">1@in-ri.ru</a></p>\r\n</div>\r\n<!-- End сontacts_inretactive --></div>\r\n</div>\r\n<style type="text/css">/* --- сontacts --- */\r\n.сontacts_box{\r\n  background: url("img/mc_bg.png") no-repeat center center;\r\n  background-size: cover;\r\n  min-height: 490px;\r\n  padding-bottom: 0px;\r\n}\r\n.сontacts{\r\n  padding-bottom: 20px;\r\n}\r\n.сontacts_descr{\r\n  max-width: 335px;\r\n  background: #ffffff;\r\n  margin: 0 auto;\r\n  margin-top: 10px;\r\n  padding: 15px 25px;\r\n  border: 10px solid #17a2b8;\r\n  text-align: center;\r\n}\r\n.сontacts_descr b{\r\n  font: 400 25px/30px AvantGardeGothicBdITC-Reg, Arial, sans-serif;\r\n  color: #000000;\r\n}\r\n.сontacts_descr a{\r\n  color: #000000;\r\n}\r\n/* --- End сontacts --- */\r\n  \r\n  /* --- сontacts_inretactive_box --- */\r\n.сontacts_inretactive_box{\r\n  min-height: 500px;\r\n  padding-bottom: 0px;\r\n  \r\n}\r\n.сontacts_inretactive{\r\n  padding-bottom: 20px;\r\n  position: relative;\r\n}\r\n.ya_map{\r\n  position: absolute;\r\n  top: 0;\r\n  height: 500px;\r\n  width: 100%;\r\n}\r\n.сontacts_inretactive .mtitle_box{\r\n  /*position: relative;\r\n  z-index: 1;\r\n  text-align: right;\r\n  margin-right: 15px;*/\r\n}\r\n\r\n.сontacts_inretactive .сontacts_descr{\r\n  margin: 55px 15px 15px 25px;\r\n  position: relative;\r\n  z-index: 1;\r\n  max-width: 335px;\r\n  background: #ffffff;\r\n  float: right;\r\n}\r\n@media (max-width: 768px){\r\n  .сontacts_inretactive .сontacts_descr{\r\n    margin: 0 auto 0 auto;\r\n    float: none;\r\n  }\r\n  .сontacts_inretactive{\r\n    padding-top: 525px;\r\n  }\r\n}\r\n/* --- End сontacts_inretactive --- */\r\n</style>\r\n', 0, 0, 6),
(3, 'Блок 1', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h2>Блок</h2>\r\n</div>\r\n</div>\r\n\r\n<div class="block_box bl_bg" style="">\r\n<div class="block_b" style="background: rgba(0, 0, 0, 0.3);\r\n    padding-top: 150px;\r\n    padding-bottom: 150px;">\r\n<div class="block">\r\n<h2 style="color: #fff;">ПРОЕКТИРОВАНИЕ И ДИЗАЙН</h2>\r\n\r\n<p>Внешний вид сайта - лицо компании. Важно, чтобы он был не только красивым, но и удобным. Интуитивно понятный интерфейс пользователя плюс отзывчивый дизайн увеличивают конверсию и продажи.</p>\r\n\r\n<h2 style="color: #fff;">РАЗРАБОТКА</h2>\r\n\r\n<p>На этапе разработки учитывается адаптивность сайта. Он должен корректно отображаться в любом браузере и на всех видах устройств. Удобная система управления сайтом позволит вам вести проект самостоятельно.</p>\r\n\r\n<h2 style="color: #fff;">ПРОДВИЖЕНИЕ И ПОДДЕРЖКА</h2>\r\n\r\n<p>Как не потеряться в медиа пространстве? Мы готовы сотрудничать с вами, отзываясь на любые просьбы и следим за тем, чтобы ваш сайт был в топе поисковиков.</p>\r\n\r\n<p><b>ВЕБ-студия <a href="//in-ri.ru" style="color: #dc3545;">in-ri.ru</a> занимается разработкой сайтов, поддержкой, созданием интернет-магазинов, интернет-рекламой и&nbsp;поисковой оптимизацией.</b></p>\r\n</div>\r\n</div>\r\n</div>\r\n<style type="text/css">.bl_bg{\r\n    background-image: url(/images_ckeditor/files/test.jpg);\r\n    background-attachment: fixed;\r\n    background-size: cover;\r\n    background-position: center center;\r\n    text-shadow: 1px 1px 16px rgba(0, 0, 0, 0.8);\r\n    color: #ffffff;\r\n    padding-top: 0;\r\n    padding-bottom: 0;\r\n    margin-bottom: 0px;\r\n    /*-webkit-transform: translate3d(0,0,0);*/\r\n    -webkit-backface-visibility: hidden;\r\n  }\r\n  .admin_edit_box .bl_bg{\r\n    /*position: relative;\r\n    z-index: 1600;*/\r\n  }\r\n</style>\r\n', 0, 0, 5),
(4, 'Блок 2', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h2>Блок&nbsp;</h2>\r\n\r\n<div class="scheme">\r\n<div class="row">\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-globe fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Встречи 1 раз в неделю</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-tasks fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">График работы</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-external-link-alt fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Новые техники</div>\r\n</div>\r\n</div>\r\n\r\n<div class="row">\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="far fa-clock fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Поддержка</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-percent fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">100%</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="far fa-sun fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Качественный результат</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<style type="text/css">/* --- scheme --- */\r\n.scheme{\r\n	margin-top: 25px;\r\n  margin-bottom: 25px;\r\n}\r\n.scheme .c_img_box{\r\n  text-align:center;\r\n  /*color: #28a745;*/\r\n  padding-bottom: 25px;\r\n  padding-top: 15px;\r\n}\r\n.scheme .c_title{\r\n	text-align:center;\r\n  padding-bottom: 35px;\r\n  font-size: 24px;\r\n  font-weight: bold;\r\n}\r\n.steps{\r\n  \r\n}\r\n.steps .rim{\r\n  display: inline-block;\r\n  width: 25px;\r\n  text-align:center;\r\n}\r\n.steps .left{\r\n  /*text-align:right;*/\r\n}\r\n.steps .right{\r\n  border-left: 2px solid #37373e59;\r\n  border-right: 2px solid #37373e59;\r\n  border-top: 2px solid #37373e59;\r\n  padding-top: 20px;\r\n  padding-bottom: 20px;\r\n  /*min-height: 75px;*/\r\n}\r\n.steps .row:last-child {\r\n  \r\n  /*border-bottom: 3px solid #37373e;*/\r\n}\r\n/* --- END scheme --- */\r\n</style>\r\n', 1, 0, 4),
(5, 'Слайдер', '', 'block_mine_slider', '', 0, 0, 2),
(6, 'Шапка сайта', '', 'block_mine_header', '', 1, 0, 0),
(7, 'Меню сайта', '', 'block_mine_top_menu', '', 1, 0, 1),
(8, 'Контент на внутренних страницах', '', 'block_inner_content', '', 1, 0, 3),
(9, 'Подвал сайта', '', 'block_mine_footer', '', 1, 0, 7);
HTML;
    
    return $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name );
  }
  
  function insert_def_module_all_images_corporate( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `module`, `module_id`, `img`, `longtxt1`, `longtxt2`, `seo_h1`, `seo_title`, `seo_description`, `img_alt`, `img_title`, `hide`, `ord`, `module_ord`) VALUES ";
    $sql_insert .=<<<HTML
(60, '1531733498_0.jpg', 'wed_cat_articles', 7, '1533298966_0.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 1, 0),
(61, '1531733498_1.jpg', 'wed_cat_articles', 7, '1533298966_1.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 2, 0),
(62, '1531733498_2.jpg', 'wed_cat_articles', 7, '1533298966_2.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 3, 0),
(63, '1531733498_3.jpg', 'wed_cat_articles', 7, '1533298966_3.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 4, 0),
(64, '1531733498_4.jpg', 'wed_cat_articles', 7, '1533298966_4.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 5, 0),
(65, '1531733498_5.jpg', 'wed_cat_articles', 7, '1533298966_5.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 6, 0),
(66, '1531733498_6.jpg', 'wed_cat_articles', 7, '1533298966_6.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 7, 0),
(67, '1531733498_7.jpg', 'wed_cat_articles', 7, '1533298966_7.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 8, 0),
(68, '1531733498_8.jpg', 'wed_cat_articles', 7, '1533298966_8.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 9, 0),
(69, '1531733498_9.jpg', 'wed_cat_articles', 7, '1533298966_9.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 10, 0),
(70, '1531733498_10.jpg', 'wed_cat_articles', 7, '1533298966_10.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 11, 0),
(71, '1531733498_11.jpg', 'wed_cat_articles', 7, '1533298966_11.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 12, 0),
(72, '1531733498_12.jpg', 'wed_cat_articles', 7, '1533298966_12.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 13, 0);
HTML;
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CORPORATE );
    
    return $output;
  }
  
  function insert_def_module_all_files_corporate( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `module`, `module_id`, `img`, `file`, `longtxt1`, `img_alt`, `img_title`, `hide`, `ord`, `module_ord`) VALUES ";
    $sql_insert .=<<<HTML
(6, '1972.pdf', 'wed_cat_articles', 8, '', '1533299057_0.pdf', NULL, NULL, NULL, 0, 0, 0),
(7, '1973.pdf', 'wed_cat_articles', 8, '', '1533299057_1.pdf', NULL, NULL, NULL, 0, 0, 0),
(8, '1974.pdf', 'wed_cat_articles', 8, '', '1533299057_2.pdf', NULL, NULL, NULL, 0, 0, 0),
(9, '1975.pdf', 'wed_cat_articles', 8, '', '1533299057_3.pdf', NULL, NULL, NULL, 0, 0, 0),
(10, '1976.pdf', 'wed_cat_articles', 8, '', '1533299057_4.pdf', NULL, NULL, NULL, 0, 0, 0),
(11, '1977.pdf', 'wed_cat_articles', 8, '', '1533299057_5.pdf', NULL, NULL, NULL, 0, 0, 0),
(12, '1978.pdf', 'wed_cat_articles', 8, '', '1533299057_6.pdf', NULL, NULL, NULL, 0, 0, 0),
(13, '1979.pdf', 'wed_cat_articles', 8, '', '1533299057_7.pdf', NULL, NULL, NULL, 0, 0, 0),
(14, '1980.pdf', 'wed_cat_articles', 8, '', '1533299057_8.pdf', NULL, NULL, NULL, 0, 0, 0),
(15, '1981.pdf', 'wed_cat_articles', 8, '', '1533299057_9.pdf', NULL, NULL, NULL, 0, 0, 0),
(16, '1982.pdf', 'wed_cat_articles', 8, '', '1533299057_10.pdf', NULL, NULL, NULL, 0, 0, 0),
(17, '1983.pdf', 'wed_cat_articles', 8, '', '1533299057_11.pdf', NULL, NULL, NULL, 0, 0, 0),
(18, '1984.pdf', 'wed_cat_articles', 8, '', '1533299057_12.pdf', NULL, NULL, NULL, 0, 0, 0),
(19, '1985.pdf', 'wed_cat_articles', 8, '', '1533299057_13.pdf', NULL, NULL, NULL, 0, 0, 0),
(20, '1986.pdf', 'wed_cat_articles', 8, '', '1533299057_14.pdf', NULL, NULL, NULL, 0, 0, 0),
(21, '1987.pdf', 'wed_cat_articles', 8, '', '1533299057_15.pdf', NULL, NULL, NULL, 0, 0, 0),
(22, '1988.pdf', 'wed_cat_articles', 8, '', '1533299057_16.pdf', NULL, NULL, NULL, 0, 0, 0),
(23, '1989.pdf', 'wed_cat_articles', 8, '', '1533299057_17.pdf', NULL, NULL, NULL, 0, 0, 0),
(24, '1990.pdf', 'wed_cat_articles', 8, '', '1533299057_18.pdf', NULL, NULL, NULL, 0, 0, 0),
(25, '1991.pdf', 'wed_cat_articles', 8, '', '1533299057_19.pdf', NULL, NULL, NULL, 0, 0, 0);
HTML;
    
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CORPORATE );
    $output .= $this->copy_file_module( $name, SOURCE_SITE_CORPORATE );
    
    return $output;
  }
  
  function setup_module_news_cutaway( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `date` varchar(10) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `fl_show_mine` tinyint(1) DEFAULT NULL,
        `orm_search_name` varchar(255) DEFAULT NULL,
        `orm_search` text,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `date`, `longtxt1`, `longtxt2`, `fl_show_mine`, `orm_search_name`, `orm_search`, `seo_h1`, `seo_title`, `seo_description`, `seo_keywords`, `img_alt`, `img_title`, `hide`, `ord`) VALUES  ";
    $sql_insert .=<<<HTML
(1, 'Петросян отреагировал на сообщения о разделе имущества со Степаненко', '1533303902.jpg', '2018-08-02', 'Отмечалось, что иск подала Степаненко. Петросян назвал эти новости &quot;недоразумением&quot;. Юморист, телеведущий и певец Владимир Винокур позднее заявил, что раздел имущества между Евгением Петросяном и Еленой Степаненко не должен становиться предметом интереса СМИ.', '<p>Юмористы <strong>Евгений Петросян</strong> и <strong>Елена Степаненко</strong> делят совместно нажитое имущество в&nbsp;Хамовническом суде, сообщает агентство городских новостей &laquo;<a href="https://www.mskagency.ru/materials/2805128" target="_blank">Москва</a>&raquo;.</p>\r\n\r\n<p class="image-in-text" itemprop="image" itemscope="" itemtype="https://schema.org/ImageObject"><img data-author="Фото: скриншот" src="/images/news/orig/1533303902.jpg" /><span class="title"></span><br />\r\n<span class="author">Фото: скриншот</span></p>\r\n\r\n<p>С&nbsp;иском в&nbsp;суд обратилась Степаненко, сообщили в&nbsp;пресс-службе суда. Заявление будет рассмотрено 6&nbsp;августа.</p>\r\n\r\n<p>Петросян заявил, что ему об&nbsp;этом ничего неизвестно,&nbsp;&mdash; по&nbsp;его словам, &laquo;это какое-то недоразумение&raquo;.</p>\r\n\r\n<p>Также народный артист отказался комментировать информацию о&nbsp;разводе со&nbsp;Степаненко. &laquo;Давайте без комментариев. Какая вам разница, что у&nbsp;меня происходит? Без комментариев&raquo;,&nbsp;&mdash; сказал Евгений Петросян.</p>\r\n\r\n<p>Официально юмористы о&nbsp;своем разводе не&nbsp;объявляли.</p>\r\n', 0, 'ПЕТРОСЯН ОТРЕАГИРОВАТЬ НА СООБЩЕНИЕ О РАЗДЕЛ ИМУЩЕСТВО СО СТЕПАНЕНКО', 'ЮМОРИСТ ЕВГЕНИЯ ПЕТРОСЯН И ЕЛЕНА СТЕПАНЕНКО ДЕЛИТЬ СОВМЕСТНО НАЖИТОЕ ИМУЩЕСТВО В NBSP ХАМОВНИЧЕСКИЙ СУД СООБЩАТЬ АГЕНТСТВО ГОРОДСКОЙ НОВОСТЬ LAQUO МОСКВА RAQUO ФОТО СКРИНШОТ С NBSP ИСКОМЫЙ В NBSP СУД ОБРАТИТЬСЯ СТЕПАНЕНКО СООБЩИТЬ В NBSP ПРЕСС-СЛУЖБА СУД ЗАЯВЛЕНИЕ БЫТЬ РАССМОТРЕТЬ 6 NBSP АВГУСТ ПЕТРОСЯН ЗАЯВИТЬ ЧТО ОН ОБ NBSP ЭТО НИЧЕГО НЕИЗВЕСТНЫЙ NBSP MDASH ПО NBSP ЕГО СЛОВО LAQUO ЭТО КАКОЙ-ТО НЕДОРАЗУМЕНИЕ RAQUO ТАКЖЕ НАРОДНЫЙ АРТИСТ ОТКАЗАТЬСЯ КОММЕНТИРОВАТЬ ИНФОРМАЦИЯ О NBSP РАЗВОД СО NBSP СТЕПАНЕНКО LAQUO ДАВАТЬ БЕЗ КОММЕНТАРИЙ КАКАТЬ ВЫ РАЗНИЦА ЧТО У NBSP МЕНЬ ПРОИСХОДИТЬ БЕЗ КОММЕНТАРИЙ RAQUO NBSP MDASH СКАЗАТЬ ЕВГЕНИЯ ПЕТРОСЯН ОФИЦИАЛЬНО ЮМОРИСТ О NBSP СВОЕ РАЗВОД НЕ NBSP ОБЪЯВЛЯТЬ', '', '', '', '', '', '', 0, 0),
(2, 'Ученые назвали причину исчезновения кораблей в Бермудском треугольнике', '1533304968.jpg', '2018-08-03', 'Ученые из&nbsp;Университета Саутгемптона проверили одну из&nbsp;наиболее вероятных версий исчезновения кораблей в&nbsp;Бермудском треугольнике&nbsp;&mdash; блуждающие волны. Об этом говорится в&nbsp;документальном фильме &quot;Загадка Бермудского треугольника&quot; на&nbsp;Channel 5, сообщает Daily Star.', '<div itemprop="articleBody">\r\n<p>Ученые из&nbsp;Университета Саутгемптона (Великобритания) проверили одну из&nbsp;наиболее вероятных версий исчезновения кораблей в&nbsp;Бермудском треугольнике &ndash; блуждающие волны. Об этом говорится в&nbsp;документальном фильме &quot;Загадка Бермудского треугольника&quot; на&nbsp;Channel 5, сообщает британская газета Daily Star.</p>\r\n\r\n<p>Район Атлантического океана, ограниченный треугольником, вершинами которого являются Флорида, Бермудские острова и&nbsp;Пуэрто-Рико, известен тем, что там, как&nbsp;считается, чаще, чем в&nbsp;других районах пропадают морские и&nbsp;воздушные суда.&nbsp;</p>\r\n\r\n<div id="">\r\n<div><img alt="Поселение на Бермудских островах" src="/images_ckeditor/images/news/1479120401.jpg" style="float: left; margin: 5px 5px 5px 0px;" title="Поселение на Бермудских островах" /></div>\r\n\r\n<div>Биофизик: тайна Бермудского треугольника, возможно, близка к разгадке</div>\r\n</div>\r\n\r\n<p>Существуют множество версий загадочных исчезновений, однако основные научные: мощные выбросы метана, генерация волнами инфразвука, слишком быстрое течение и&nbsp;блуждающие волны. Последние представляют собой гигантские одиночные волны, внезапно возникающие в&nbsp;океане, высотой 20-30 метров.&nbsp;&nbsp;</p>\r\n\r\n<p><span style="font-size: 1rem;">Исследователи из&nbsp;Саутгемптона решили сосредоточиться именно на&nbsp;этой версии. Они смоделировали &quot;волну-убийцу&quot; на&nbsp;компьютере и&nbsp;направили к&nbsp;ней модель судна. Как и&nbsp;ожидалось, корабль &quot;затонул&quot; за&nbsp;считанные минуты.</span></p>\r\n\r\n<p>По мнению ученых, блуждающие волны могут объяснить исчезновение кораблей, но&nbsp;не самолетов. Поэтому говорить о&nbsp;полном раскрытии секрета загадочного треугольника пока рано.</p>\r\n\r\n<p>Ведущий научный сотрудник Института физики Земли им. О.Ю. Шмидта РАН Александр Жигалин в&nbsp;эфире радио Sputnik прокомментировал выводы британских ученых.&nbsp;</p>\r\n\r\n<p>&quot;Бермудский треугольник &ndash; это такая модная загадка, которую очень долго разгадывают, но&nbsp;так до&nbsp;конца и&nbsp;не ясно, что там происходит. Сначала одной из&nbsp;ведущих версий было то, что в&nbsp;этом районе часты густые туманы. Кстати, эта гипотеза объясняет исчезновения как&nbsp;кораблей, так и&nbsp;самолетов. А вот что касается огромных волн, то как&nbsp;раз этим данный район особенно не&nbsp;грешит: море там не&nbsp;очень глубокое, там много водорослей, и&nbsp;подняться таким волнам там очень непросто. Во всяком случае, до&nbsp;сих пор об&nbsp;этом ничего не&nbsp;было слышно. А вот &quot;метановая гипотеза&quot; с&nbsp;точки зрения физики, на&nbsp;мой взгляд, достаточно оправдана: насыщенная пузырьками газа вода обладает пониженной несущей способностью, и&nbsp;корабли при&nbsp;определенных условиях могут в&nbsp;такой воде потонуть. Что касается существования огромных &quot;волн-убийцы&quot;, то это можно считать установленным фактом &ndash; свидетельств неожиданного появления таких волн вполне достаточно. Но их появление именно в&nbsp;данном районе у&nbsp;меня вызывает сильные сомнения&quot;, &ndash; сказал Александр Жигалин.&nbsp;</p>\r\n\r\n<p>Он уверен, что компьютерное моделирование &ndash; это способ проверки гипотез, а&nbsp;строить на&nbsp;нем гипотезу нельзя.</p>\r\n\r\n<p>&quot;Я не&nbsp;очень доверяю моделированию. Мой многолетний опыт работы как&nbsp;геофизика показывает, что моделирование &ndash; это немножко лукавая вещь. Поскольку, грубо говоря, что в&nbsp;машину заложишь, то на&nbsp;выходе и&nbsp;получишь. Вот хочется нам получить волну в&nbsp;30 метров &ndash; так мы ее, скорее всего, и&nbsp;получим. Надо изучать любое явление в&nbsp;реальности, а&nbsp;не только в&nbsp;компьютере &ndash; только так мы получим ясный ответ на&nbsp;все вопросы. Моделирование &ndash; это способ проверки гипотез, а&nbsp;строить на&nbsp;нем гипотезу нельзя&quot;, &ndash; заключил Александр Жигалин.&nbsp;</p>\r\n</div>\r\n', 0, 'УЧЕНЫЙ НАЗВАТЬ ПРИЧИНА ИСЧЕЗНОВЕНИЕ КОРАБЛЬ В БЕРМУДСКИЙ ТРЕУГОЛЬНИК', 'УЧЕНЫЙ ИЗ NBSP УНИВЕРСИТЕТ САУТГЕМПТОН ВЕЛИКОБРИТАНИЯ ПРОВЕРИТЬ ОДИН ИЗ NBSP НАИБОЛЕЕ ВЕРОЯТНЫЙ ВЕРСИЯ ИСЧЕЗНОВЕНИЕ КОРАБЛЬ В NBSP БЕРМУДСКИЙ ТРЕУГОЛЬНИК NDASH БЛУЖДАТЬ ВОЛНА ОБ ЭТО ГОВОРИТЬСЯ В NBSP ДОКУМЕНТАЛЬНЫЙ ФИЛЬМ QUOT ЗАГАДКА БЕРМУДСКИЙ ТРЕУГОЛЬНИК QUOT НА NBSP CHANNEL 5 СООБЩАТЬ БРИТАНСКИЙ ГАЗЕТА DAILY STAR РАЙОН АТЛАНТИЧЕСКИЙ ОКЕАН ОГРАНИЧЕННЫЙ ТРЕУГОЛЬНИК ВЕРШИНА КОТОРЫЙ ЯВЛЯТЬСЯ ФЛОРИДА БЕРМУДСКИЙ ОСТРОВ И NBSP ПУЭРТО-РИКО ИЗВЕСТНЫЙ ТЕМ ЧТО ТАМ КАК NBSP СЧИТАТЬСЯ ЧАЩА ЧЕМ В NBSP ДРУГОЙ РАЙОН ПРОПАДАТЬ МОРСКОЙ И NBSP ВОЗДУШНЫЙ СУД NBSP БИОФИЗИК ТАЙНА БЕРМУДСКИЙ ТРЕУГОЛЬНИК ВОЗМОЖНО БЛИЗКИЙ К РАЗГАДКА СУЩЕСТВОВАТЬ МНОЖЕСТВО ВЕРСИЯ ЗАГАДОЧНЫЙ ИСЧЕЗНОВЕНИЕ ОДНАКО ОСНОВНЫЙ НАУЧНЫЙ МОЩНЫЙ ВЫБРОС МЕТАН ГЕНЕРАЦИЯ ВОЛНА ИНФРАЗВУК СЛИШКОМ БЫСТРЫЙ ТЕЧЕНИЕ И NBSP БЛУЖДАТЬ ВОЛНА ПОСЛЕДНИЙ ПРЕДСТАВЛЯТЬ СЕБЯ ГИГАНТСКИЙ ОДИНОЧНЫЙ ВОЛНА ВНЕЗАПНО ВОЗНИКАТЬ В NBSP ОКЕАН ВЫСОТА 20-30 МЕТР NBSP NBSP ИССЛЕДОВАТЕЛЬ ИЗ NBSP САУТГЕМПТОН РЕШИТЬ СОСРЕДОТОЧИТЬСЯ ИМЕННО НА NBSP ЭТОТ ВЕРСИЯ ОНИ СМОДЕЛИРОВАТЬ QUOT ВОЛНУ-УБИЙЦА QUOT НА NBSP КОМПЬЮТЕР И NBSP НАПРАВИТЬ К NBSP ОНА МОДЕЛЬ СУДНЫЙ КАК И NBSP ОЖИДАТЬСЯ КОРАБЛЬ QUOT ЗАТОНУТЬ QUOT ЗА NBSP СЧИТАТЬ МИНУТА ПО МНЕНИЕ УЧЕНЫЙ БЛУЖДАТЬ ВОЛНА МОЧЬ ОБЪЯСНИТЬ ИСЧЕЗНОВЕНИЕ КОРАБЛЬ НО NBSP НЕ САМОЛЁТ ПОЭТОМУ ГОВОРИТЬ О NBSP ПОЛНЫЙ РАСКРЫТИЕ СЕКРЕТ ЗАГАДОЧНЫЙ ТРЕУГОЛЬНИК ПОКА РАНО ВЕДУЩИЙ НАУЧНЫЙ СОТРУДНИК ИНСТИТУТ ФИЗИК ЗЕМЛЯ ИМЯ О Ю ШМИДТ РАНА АЛЕКСАНДР ЖИГАЛИНА В NBSP ЭФИР РАДИО SPUTNIK ПРОКОММЕНТИРОВАТЬ ВЫВОД БРИТАНСКИЙ УЧЕНЫЙ NBSP QUOT БЕРМУДСКИЙ ТРЕУГОЛЬНИК NDASH ЭТО ТАКАТЬ МОДНЫЙ ЗАГАДКА КОТОРЫЙ ОЧЕНЬ ДОЛГО РАЗГАДЫВАТЬ НО NBSP ТАК ДО NBSP КОНЕЦ И NBSP НЕ ЯСНО ЧТО ТАМ ПРОИСХОДИТЬ СНАЧАЛА ОДИН ИЗ NBSP ВЕДУЩИЙ ВЕРСИЯ БЫЛО ТО ЧТО В NBSP ЭТО РАЙОН ЧАСТЫЙ ГУСТОЙ ТУМАН КСТАТИ ЭТОТ ГИПОТЕЗА ОБЪЯСНЯТЬ ИСЧЕЗНОВЕНИЕ КАК NBSP КОРАБЛЬ ТАК И NBSP САМОЛЁТ А ВОТ ЧТО КАСАТЬСЯ ОГРОМНЫЙ ВОЛНА ТО КАК NBSP РАЗ ЭТО ДАННЫЙ РАЙОН ОСОБЕННО НЕ NBSP ГРЕШИТЬ МОР ТАМ НЕ NBSP ОЧЕНЬ ГЛУБОКИЙ ТАМ МНОГО ВОДОРОСЛЬ И NBSP ПОДНЯТЬСЯ ТАКОЙ ВОЛНА ТАМ ОЧЕНЬ НЕПРОСТО ВО ВСЯКИЙ СЛУЧАЙ ДО NBSP СЕЙ ПОРА ОБ NBSP ЭТО НИЧЕГО НЕ NBSP БЫЛО СЛЫШНО А ВОТ QUOT МЕТАНОВЫЙ ГИПОТЕЗА QUOT С NBSP ТОЧКА ЗРЕНИЕ ФИЗИК НА NBSP МЫТЬ ВЗГЛЯД ДОСТАТОЧНО ОПРАВДАТЬ НАСЫЩЕННЫЙ ПУЗЫРЁК ГАЗ ВОД ОБЛАДАТЬ ПОНИЖЕННЫЙ НЕСУЩИЙ СПОСОБНОСТЬ И NBSP КОРАБЛЬ ПРИ NBSP ОПРЕДЕЛЕННЫЙ УСЛОВИЕ МОЧЬ В NBSP ТАКОЙ ВОД ПОТОНУТЬ ЧТО КАСАТЬСЯ СУЩЕСТВОВАНИЕ ОГРОМНЫЙ QUOT ВОЛН-УБИЙЦА QUOT ТО ЭТО МОЖНО СЧИТАТЬ УСТАНОВЛЕННЫЙ ФАКТ NDASH СВИДЕТЕЛЬСТВО НЕОЖИДАННЫЙ ПОЯВЛЕНИЕ ТАКОЙ ВОЛНА ВПОЛНЕ ДОСТАТОЧНО НО ИХ ПОЯВЛЕНИЕ ИМЕННО В NBSP ДАННЫЙ РАЙОН У NBSP МЕНЬ ВЫЗЫВАТЬ СИЛЬНЫЙ СОМНЕНИЕ QUOT NDASH СКАЗАТЬ АЛЕКСАНДР ЖИГАЛИНА NBSP ОН УВЕРИТЬ ЧТО КОМПЬЮТЕРНЫЙ МОДЕЛИРОВАНИЕ NDASH ЭТО СПОСОБ ПРОВЕРКА ГИПОТЕЗА А NBSP СТРОИТЬ НА NBSP НЕМОЙ ГИПОТЕЗА НЕЛЬЗЯ QUOT Я НЕ NBSP ОЧЕНЬ ДОВЕРЯТЬ МОДЕЛИРОВАНИЕ МЫТЬ МНОГОЛЕТНИЙ ОПЫТ РАБОТА КАК NBSP ГЕОФИЗИК ПОКАЗЫВАТЬ ЧТО МОДЕЛИРОВАНИЕ NDASH ЭТО НЕМНОЖКО ЛУКАВЫЙ ВЕЩЬ ПОСКОЛЬКУ ГРУБО ГОВОРИТЬ ЧТО В NBSP МАШИН ЗАЛОЖИТЬ ТО НА NBSP ВЫХОД И NBSP ПОЛУЧИТЬ ВОТ ХОТЕТЬСЯ МЫ ПОЛУЧИТЬ ВОЛНА В NBSP 30 МЕТР NDASH ТАК МЫ ЕЕ СКОРЫЙ ВСЕГО И NBSP ПОЛУЧИТЬ НАДО ИЗУЧАТЬ ЛЮБОЙ ЯВЛЕНИЕ В NBSP РЕАЛЬНОСТЬ А NBSP НЕ ТОЛЬКО В NBSP КОМПЬЮТЕР NDASH ТОЛЬКО ТАК МЫ ПОЛУЧИТЬ ЯСНЫЙ ОТВЕТ НА NBSP ВСЕ ВОПРОС МОДЕЛИРОВАНИЕ NDASH ЭТО СПОСОБ ПРОВЕРКА ГИПОТЕЗА А NBSP СТРОИТЬ НА NBSP НЕМОЙ ГИПОТЕЗА НЕЛЬЗЯ QUOT NDASH ЗАКЛЮЧИТЬ АЛЕКСАНДР ЖИГАЛИНА NBSP', '', '', '', '', '', '', 0, 0),
(3, 'Марафон «Европа-Азия» соберёт более шести тысяч участников', '1533305394.jpg', '2018-08-01', 'Велосипедисты смогут проехать по дистанциям в 2, 14, 34 и 50 километров.', '<div>Продолжается офлайн-регистрация участников IV Международного легкоатлетического марафона &laquo;Европа &mdash; Азия&raquo;, который пройдет в Екатеринбурге в это воскресенье, 5 августа. На данный момент на одну из дистанций уже зарегистрировались более шести тысяч человек. Но организаторы ожидают еще больше участников, так как многие спортсмены регистрируются за день до марафона.<br />\r\n<br />\r\nУчастникам предлагают дистанции в 3, 10, 21 и 42 километра. Старт организуют на площади 1905 года. Через каждые пять километров марафонцев будет ждать пункт, где можно перекусить и освежиться.<br />\r\n<br />\r\nНапомним, что из-за марафона будут перекрываться улицы:<br />\r\n<br />\r\n&ndash; с 00:00 до 18:00 проспект Ленина (от переулка Банковского до улицы 8 Марта);<br />\r\n<br />\r\n&ndash; с 07:00 до 15:00 проспект Ленина (от Репина до переулка Банковского), Репина (от Металлургов до проспекта Ленина), четная сторона улицы Металлургов (от автодороги Пермь &mdash; Екатеринбург до Репина);<br />\r\n<br />\r\n&ndash; с 07:00 до 10:00 проспект Ленина (от 8 Марта до площади Кирова), площадь Кирова (от проспекта Ленина до улицы Мира), улица Мира (от Малышева до Первомайской), Толмачева (от проспекта Ленина до Царской), Царская (от Толмачева до Николая Никонова), Бориса Ельцина (от 8 Марта до Челюскинцев), 8 Марта (от проспекта Ленина до улицы Бориса Ельцина).<br />\r\n<br />\r\nТакже с 07:00 до 14:00 будет закрыто движение по дороге Пермь &mdash; Екатеринбург (с 343-го по 345-й километр, обратное направление), с 4 по 6 августа не будет работать парковка на площади 1905 года.<br />\r\n<br />\r\nКроме того, из-за забега схему движения <!--colorstart:#CC0000--><span style="color:#CC0000"><!--/colorstart--><u>изменят</u><!--colorend--></span><!--/colorend--> 17 трамвайных, девять троллейбусных маршрутов, 17 муниципальных и 29 коммерческих автобусов.</div>\r\n', 0, 'МАРАФОН ?ЕВРОПА-АЗИЯ ? СОБРАТЬ БОЛЕЕ ШЕСТЬ ТЫСЯЧА УЧАСТНИК', 'ПРОДОЛЖАТЬСЯ ОФЛАЙН-РЕГИСТРАЦИЯ УЧАСТНИК IV МЕЖДУНАРОДНЫЙ ЛЕГКОАТЛЕТИЧЕСКИЙ МАРАФОН LAQUO ЕВРОПА MDASH АЗИЯ RAQUO КОТОРЫЙ ПРОЙДЕТ В ЕКАТЕРИНБУРГ В ЭТО ВОСКРЕСЕНИЕ 5 АВГУСТ НА ДАННЫЙ МОМЕНТ НА ОДИН ИЗ ДИСТАНЦИЯ УЖЕ ЗАРЕГИСТРИРОВАТЬСЯ БОЛЕЕ ШЕСТЬ ТЫСЯЧА ЧЕЛОВЕК НО ОРГАНИЗАТОР ОЖИДАТЬ ЕЩИЙ БОЛЬШИЙ УЧАСТНИК ТАК КАК МНОГИЙ СПОРТСМЕН РЕГИСТРИРОВАТЬСЯ ЗА ДЕТЬ ДО МАРАФОН УЧАСТНИК ПРЕДЛАГАТЬ ДИСТАНЦИЯ В 3 10 21 И 42 КИЛОМЕТР СТАРТ ОРГАНИЗОВАТЬ НА ПЛОЩАДЬ 1905 ГОД ЧЕРЕЗ КАЖДЫЙ ПЯТЬ КИЛОМЕТРОВЫЙ МАРАФОНЕЦ БЫТЬ ЖДАТЬ ПУНКТ ГДЕ МОЖНО ПЕРЕКУСИТЬ И ОСВЕЖИТЬСЯ НАПОМНИТЬ ЧТО ИЗ-ЗА МАРАФОН БЫТЬ ПЕРЕКРЫВАТЬСЯ УЛИЦА NDASH С 00 00 ДО 18 00 ПРОСПЕКТ ЛЕНИН ОТ ПЕРЕУЛОК БАНКОВСКИЙ ДО УЛИЦА 8 МАРТ NDASH С 07 00 ДО 15 00 ПРОСПЕКТ ЛЕНИН ОТ РЕПИН ДО ПЕРЕУЛОК БАНКОВСКИЙ РЕПИН ОТ МЕТАЛЛУРГ ДО ПРОСПЕКТ ЛЕНИН ЧЕТНЫЙ СТОРОНА УЛИЦА МЕТАЛЛУРГ ОТ АВТОДОРОГА ПЕРМЬ MDASH ЕКАТЕРИНБУРГ ДО РЕПИН NDASH С 07 00 ДО 10 00 ПРОСПЕКТ ЛЕНИН ОТ 8 МАРТ ДО ПЛОЩАДЬ КИРОВ ПЛОЩАДЬ КИРОВ ОТ ПРОСПЕКТ ЛЕНИН ДО УЛИЦА МИР УЛИЦА МИР ОТ МАЛЫШЕВ ДО ПЕРВОМАЙСКИЙ ТОЛМАЧЕВ ОТ ПРОСПЕКТ ЛЕНИН ДО ЦАРСКИЙ ЦАРСКИЙ ОТ ТОЛМАЧЕВ ДО НИКОЛАЙ НИКОНОВ БОРИС ЕЛЬЦИН ОТ 8 МАРТ ДО ЧЕЛЮСКИНЕЦ 8 МАРТ ОТ ПРОСПЕКТ ЛЕНИН ДО УЛИЦА БОРИС ЕЛЬЦИН ТАКЖЕ С 07 00 ДО 14 00 БЫТЬ ЗАКРЫТЫЙ ДВИЖЕНИЕ ПО ДОРОГА ПЕРМЬ MDASH ЕКАТЕРИНБУРГ С 343-ГО ПО 345-Й КИЛОМЕТР ОБРАТНЫЙ НАПРАВЛЕНИЕ С 4 ПО 6 АВГУСТ НЕ БЫТЬ РАБОТАТЬ ПАРКОВКА НА ПЛОЩАДЬ 1905 ГОД КРОМЕ ТОГО ИЗ-ЗА ЗАБЕГ СХЕМА ДВИЖЕНИЕ ИЗМЕНИТЬ 17 ТРАМВАЙНЫЙ ДЕВЯТЬ ТРОЛЛЕЙБУСНЫЙ МАРШРУТ 17 МУНИЦИПАЛЬНЫЙ И 29 КОММЕРЧЕСКИЙ АВТОБУС', '', '', '', '', '', '', 0, 0);
HTML;
    $output = '';
    $this->create_img_dir( $name );
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CORPORATE );
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  
  #--------------------- setup_database_module_onlineshop ---------------------
  
  
  
  function setup_database_module_required(){
    
    $this->add_content( $this->wrap_block(  "<h2>Базовые настройки</h2>" ));
      
    $this->add_content( $this->wrap_block(  # Параметры
                                            $this->setup_module_config( 'Параметры', 'config' )  ));
                                            
    $this->add_content( $this->wrap_block(  # СЕО
                                            $this->setup_module_seo( 'СЕО', 'seo' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Оформление сайта
                                            $this->setup_module_design( 'Оформление сайта', 'design' )  ));
    
    $this->add_content( $this->wrap_block(  # Администрирование
                                            $this->setup_module_accounts( 'Администрирование', 'accounts' )  ));
                                            
    $this->add_content( $this->wrap_block(  # ЧПУ
                                            $this->setup_module_url( 'Человеко-понятные адреса', 'url')  ));
                                            
    $this->add_content( $this->wrap_block(  # Блоки на главной странице
                                            $this->setup_module_mine_block( 'Блоки на главной странице', 'mine_block' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Слайдер
                                            $this->setup_module_carusel( 'Слайдер', 'carusel' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Заявки
                                            $this->setup_module_reservations( 'Заявки', 'reservations' )  ));                                        
    $this->add_content( $this->wrap_block(  # Логи входа в админку
                                            $this->setup_module_admin_logs( 'Логи входа в админку', 'admin_logs' )  ));
    
    $this->add_content( $this->wrap_block(  # Логи редактирования контента
                                            $this->setup_module_all_log( 'Логи редактирования контента', 'all_log' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe изображения
                                            $this->setup_module_all_images( 'Дополнительныe изображения', 'all_images' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe файлы
                                            $this->setup_module_all_files( 'Дополнительныe файлы', 'all_files' )  ));
  }
  
  function setup_database_module_cutaway(){ # Сайт визитка
    
    $this->add_content( $this->wrap_block(  "<h2>Сайт визитка</h2>" ));
                                              
    $this->add_content( $this->wrap_block(  # Содержание сайта
                                            $this->setup_module_smpl_article_cutaway( 'Содержание сайта', 'smpl_article' )  ));
                                              
    $this->add_content( $this->wrap_block(  # Слайдер
                                            $this->insert_def_module_carusel_cutaway('Слайдер', 'carusel')  ));
                                            
    $this->add_content( $this->wrap_block(  # Блоки на главной странице
                                            $this->insert_def_module_mine_block_cutaway( 'Блоки на главной странице', 'mine_block')  ));
                                            
    $this->add_content( $this->wrap_block(  # ЧПУ
                                            $this->insert_def_module_url_cutaway( 'Человеко-понятные адреса', 'url' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe изображения
                                            $this->insert_def_module_all_images_cutaway( 'Дополнительныe изображения', 'all_images' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe файлы
                                            $this->insert_def_module_all_files_cutaway( 'Дополнительныe файлы', 'all_files' )  ));
  }
  
  function setup_database_module_corporate(){ # Корпаротивный/новостной сайт
    
    $this->add_content( $this->wrap_block(  "<h2>Корпаротивный/новостной сайт</h2>" ));
    
    
    $this->add_content( $this->wrap_block(  # Новости
                                            $this->setup_module_news_cutaway( 'Новости', 'news' )  ));
    
    $this->add_content( $this->wrap_block(  # Слайдер
                                            $this->insert_def_module_carusel_corporate('Слайдер', 'carusel')  ));
                                            
    $this->add_content( $this->wrap_block(  # Блоки на главной странице
                                            $this->insert_def_module_mine_block_corporate( 'Блоки на главной странице', 'mine_block')  ));
                                            
    $this->add_content( $this->wrap_block(  # ЧПУ
                                            $this->insert_def_module_url_corporate( 'Человеко-понятные адреса', 'url' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe изображения
                                            $this->insert_def_module_all_images_corporate( 'Дополнительныe изображения', 'all_images' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe файлы
                                            $this->insert_def_module_all_files_corporate( 'Дополнительныe файлы', 'all_files' )  ));
                                            
  }
  
  function setup_database_module_onlineshop(){ # Интернет магазин
  
    $this->add_content( $this->wrap_block(  # Курсы валют
                                            $this->setup_module_currency_onlineshop( 'Курсы валют', 'currency' )  ));
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
