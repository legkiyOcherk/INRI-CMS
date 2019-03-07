<?php
class Setup{
  
  var $doctype       = '<!doctype html>';
  var $cms           = CMS_NAME;
  var $db_pfx        = DB_PFX;
  var $lang          = 'ru';
  var $charset       = '<meta http-equiv="Content-Type" content="text/html" charset="utf-8">';
  var $title;
  var $description;
  var $keywords;
  var $nav_items_arr = array(); # ['link'] => title
  private $content; 
  
  function __construct (){ # конструктор
    $this->title = 'Установка '.$this->cms.' CMS';
    $this->set_content('<div class="container"><h1>Установка '.$this->cms.' CMS</h1></div>'); 
    
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
        <a class="navbar-brand" href="#">INRI</a>
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

}
