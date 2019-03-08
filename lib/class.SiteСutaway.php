<?php
class Site {
  
  var $siteDoctype = '<!DOCTYPE html>';
  var $siteCharset = '<meta http-equiv="Content-Type" content="text/html" charset="utf-8">';
  var $siteTitle = '';
  var $siteDescription = '';
  var $siteKeywords = '';

  var $siteHead;
  var $siteHeader;
  var $siteContent;
  var $siteFooter;
  
  var $adminLink = "";
  var $canonical_url = "";
  
  var $arr_urls = array();
  var $module = "404";
  var $module_id = null;
  var $language = "ru";
  var $is_block_inner_content = false;
  
  var $cat_arr = array();
  
  var $pdo;
  var $bread = '';
  var $soc_net = '';
  var $js_scripts = '';
  var $search;
  var $visited_pages = array();
    
  // Конструктор
  function __construct($module = '404') {
  
    global $PDO;
    db_open();
    $this->pdo = $PDO;
    $this->module = $module;
    
    #$this->_BASKET = new Basket();
    #$this->search = new Search();
    
    #$this->having_poor_vision = new HavingPoorVision();
    #$this->eur = $this->pdo->query("SELECT `val` FROM `currency` WHERE `name` = 'eur'");
    #$this->usd = $this->pdo->query("SELECT `val` FROM `currency` WHERE `name` = 'usd'");
    
    
    // Версия для слабовидящих
    if(isset($_SESSION['is_having_poor_vision'])){
      $this->is_having_poor_vision = $_SESSION['is_having_poor_vision'];
    }else{
      $_SESSION['is_having_poor_vision'] = $this->is_having_poor_vision = false;
    }
    
    // ------------- SEO -------------
    {
    $res = $this->pdo->query("SELECT `value` FROM `".DB_PFX."seo` WHERE `type` = 'mine_title'");
    $row = $res->fetch();
    $seo_title =  $row['value'];
    if($seo_title) $this->siteTitle = str_replace("*h1*", $this->siteTitle, $seo_title)." - ".$_SERVER['HTTP_HOST'];
    
    $res = $this->pdo->query("SELECT `value` FROM `".DB_PFX."seo` WHERE `type` = 'mine_description'");
    $row = $res->fetch();
    $seo_description =  $row['value']; 
    if($seo_description) $this->siteDescription = str_replace("*h1*", $this->siteDescription, $seo_description);
    
    $res = $this->pdo->query("SELECT `value` FROM `".DB_PFX."seo` WHERE `type` = 'mine_keywords'");
    $row = $res->fetch();
    $mine_keywords =  $row['value']; 
    if($mine_keywords) $this->siteKeywords = str_replace("*h1*", $this->siteKeywords, $mine_keywords);
    }
    // ------------- END SEO -------------
    
    
    if(!$this->phone_header = db::value("val", DB_PFX."config", "name = 'phone'")){
      $this->phone_header = '
        <a href="tel:88000000000">8-800-000-00-00</a>';
    }
    if(!$this->adress_header = db::value("val", DB_PFX."config", "name = 'adress'")){
      $this->adress_header   = '';
    }
    
    if($this->email_header   = db::value("val", DB_PFX."config", "name = 'email'")){
      $this->email_header    = '<a href = "mailto:'.$this->email_header.'">'.$this->email_header.'</a><br />';
    }else{
      $this->email_header    = '';
    }
    
    if($this->working_hour   = db::value("val", DB_PFX."config", "name = 'working_hour'")){
    }else{
      $this->working_hour    = 'Время работы:<br>ПН-ПТ: с 9:00 до 18:00';
    }
    
    $this->soc_net          = db::value("val", DB_PFX."config", "name = 'soc_net'"); 
    
    $this->user_script      = db::value("value", DB_PFX."design", "type = 'user_script'");
    
    if(!$this->user_style   = db::value("value", DB_PFX."design", "type = 'user_style'") ){
      $this->user_style = '';
    }
    
    // Просмотренные страницы
    (!isset ($_SESSION['visited_pages'])) ? $_SESSION['visited_pages'] = '' : $this->visited_pages = $_SESSION['visited_pages'];
    #pri($this->visited_pages); #unset($_SESSION['visited_pages']);
    
    $this->route();
  }
  
  function getModule(){
    return $this->module;
  }
  
  function getModuleId(){
    return $this->module_id;
  }
  
  function setSiteTitle($text){
    $this->siteTitle = $text;
  }
  
  function setSiteDescription($text){
    $this->siteDescription = $text;
  }
  
  function setSiteKeywords($text){
    $this->siteKeywords = $text;
  }
  // Роутинг
   
  function route(){
    
    $route = $_SERVER['REQUEST_URI'];
    
    $url_get_route = explode("?", $route);
    #pri($url_get_route);
    if(isset($url_get_route[0])){
      if($url_get_route[0]) { // urls
        $url_get_route[0] = substr($url_get_route[0], 1);
        $route_urls = explode("/", $url_get_route[0]);
        $this->arr_urls = $route_urls;
        $url_items = new Url('url');
        $url_items->route($this->arr_urls[0], $this->module, $this->module_id);
      }
    }
    
    if(isset($url_get_route[1])){
      if($url_get_route[1]){ // $_GET
        $route_gets = explode("&", $url_get_route[1]);
        foreach($route_gets as $key=>$val){
          $r_get = explode("=", $val);
          $_GET[$r_get[0]] = $r_get[1];
       }
      }
    }
    
    #pri($_GET);
    #pri($this);
     //die();
  }
  
  function getHead(){
    
    $output = '    
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="d1.ru">
    <link rel="shortcut icon" type="image/x-icon" href="/css/img/favicon/favicon.ico">
    ';
    
    if($this->canonical_url){
      $output .= '
        <link rel="canonical" href="'.$this->canonical_url.'"/>';  
    }
    
    $output .= '
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
    <link href="/css/style.css?v=1" rel="stylesheet">
    ';
    $output .= $this->user_style;
    
    return  $output;
  }
  
  function getHeader(){
    $output = '';
    if($this->module != 'index' && !$this->is_block_inner_content){
      $output .= $this->getMineHeader();
      $output .= $this->getMineTopMenu();  
    }
    
    return  $output;
  }
  
  function getMineHeader(){
    $output = '';
    
    $output .= '
          <!-- header -->
          <div class = "header_box">
            <div class = "header">
            
              <div class="row align-items-center">
              
                <div class="col-12 col-sm-4 col-lg-auto">
                  <div class="logo_box">
                    <a href="/"><img class="logo" src="/css/img/logo2.png" alt=""></a>
                  </div>
                </div>
                
                <div class="col-12 col-sm-4 col-lg">
                  <div class="header_phone_box">
                    <div class="header_phone">
                      '.$this->phone_header.'
                    </div>
                    <div class="header_adress">
                      '.$this->adress_header.'
                    </div>
                    <div class="header_work">
                      '.$this->working_hour.'
                    </div>
                  </div>
                </div>
                
                <div class="col-12 col-sm-4 col-lg-auto">
                  <div class="header_callback_box">
                    <div class="header_callback">
                      <button class="btn flmenu1" data-id="0" data-target="#myModal" data-title="Заказать обратный звонок" data-toggle="modal">Заказать обратный звонок</button>
                    </div>
                    <div class="header_soc">
                      '.$this->soc_net.'
                    </div>
                  </div>
                </div>
                
              </div>
              
            </div>
          </div>
          <!-- End header -->';
          
    $output = $this->addEditAdminLink($output, '/wedadmin/param.php');
    
    return $output;
  }
  
  function getMineTopMenu(){
    $output = '';
    
    $output .= '
          <!-- top_menu -->
          <div class="top_menu_box">
            <div class="top_menu">
            
              <nav class= "navbar navbar-expand-sm navbar-dark bg-dark">
                <a class="navbar-brand d-sm-none" href="#">Меню</a>
                <button class="navbar-toggler" type="button" 
                  data-toggle="collapse" 
                  data-target="#navbarsTop" 
                  aria-controls="navbarsTop" 
                  aria-expanded="false" 
                  aria-label="Toggle navigation">
                  <!--<span class="navbar-toggler-icon"></span>-->
                  <i class="fa fa-bars fa-lg" title="Toggle navigation"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarsTop">
                  
                  <ul class="navbar-nav mr-auto">
                    '.Article::show_simple_menu($this).'
                  </ul>

                </div>
              </nav>
              
            </div>
          </div>
          <!-- End top_menu -->
    ';
    
    $output = $this->addEditAdminLink($output, '/wedadmin/smpl_article.php');
    
    return $output;
  }
  
  function getFooter(){
    $output = '';
        
    $output .= '
      </div>
    </div>
    ';
    
    $output .= '
    <!-- footer -->
    <div class="footer_box">
      <div class="footer">
        
        <div class="row">
          <div class="col-12 footer_menu_box">
            <ul class="footer_menu ">
              '.Article::show_simple_menu($this).'
            </ul>
          </div>
          
          <div class="col-12 soc_net_box">
            <div class = "soc_net">'.$this->soc_net.'</div>
          </div>
          
        </div>';
        
    $output .= '
        <div class="row">
          <div class="col tac">
            <div class="d1_box">
              <span>Разработано</span> 
              <a href="http://d1.ru/" target="_blank">
                <b>  
                  
                  <svg version="1.1" id="_x31_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="100%" height="100%" viewBox="0 0 352.657 106.492" enable-background="new 0 0 352.657 106.492" xml:space="preserve">

                    <path class="d1_item" fill-rule="evenodd" clip-rule="evenodd" fill="#181818" d="M103.592,53.289c0,15.4-4.514,27.838-13.567,37.337
                    c-10.11,10.557-24.546,15.847-43.332,15.847H0V0.13h46.694c20.572,0,35.738,5.878,45.471,17.634
                    C99.783,26.956,103.592,38.782,103.592,53.289L103.592,53.289z M64.187,53.312c0-7.336-2.046-13.002-6.137-16.952
                    c-3.715-3.573-8.723-5.36-15-5.36h-4.961v44.554h4.961c4.914,0,9.052-1.058,12.461-3.174C61.295,68.76,64.187,62.411,64.187,53.312z
                    "></path>
                    
                    <polygon class="d1_item" fill-rule="evenodd" clip-rule="evenodd" fill="#181818" points="152.093,106.342 113.986,106.342 113.965,29.342 
                    98.048,29.342 115.025,0.13 152.054,0 152.085,83.491 "></polygon>
                    
                    <path class="d1_item" fill-rule="evenodd" clip-rule="evenodd" fill="#181818" d="M260.783,106.423h-43.732l-14.177-35.808l-1.176-0.282v36.09
                    h-37.689v-22.93V0.08h48.293c13.002,0,23.23,2.915,30.73,8.746c8.158,6.419,12.249,15.917,12.249,28.449
                    c0,13.26-6.16,22.219-18.456,26.85L260.783,106.423L260.783,106.423z M217.051,37.745c0-3.057-1.011-5.502-3.033-7.312
                    c-2.022-1.811-4.702-2.728-8.041-2.728h-4.279v20.173h4.279C213.36,47.879,217.051,44.493,217.051,37.745z"></path>
                    
                    <path class="d1_item" fill-rule="evenodd" clip-rule="evenodd" fill="#181818" d="M352.657,61.494c0,32.234-16.73,44.998-49.928,44.998
                    c-33.199,0-49.667-12.764-49.667-44.998V0.13h38.089v60.753c0,10.651,3.903,15.988,11.709,15.988
                    c7.806,0,11.709-5.338,11.709-15.988V0.13h38.089V61.494z"></path>
                    
                    <g>
                     <path class="d1_item d1_item_bg" fill-rule="evenodd" clip-rule="evenodd" fill="#181818" stroke="#FFFFFF" stroke-width="23.2954" stroke-miterlimit="22.9256" d="
                     M158.436,101.182c9.533,0,17.309-7.776,17.309-17.309c0-9.534-7.775-17.309-17.309-17.309c-9.533,0-17.309,7.775-17.309,17.309
                     C141.127,93.407,148.903,101.182,158.436,101.182z"></path>
                     <path class="d1_item" fill-rule="evenodd" clip-rule="evenodd" fill="#181818" d="M158.436,101.182c9.533,0,17.309-7.776,17.309-17.309
                     c0-9.534-7.775-17.309-17.309-17.309c-9.533,0-17.309,7.775-17.309,17.309C141.127,93.407,148.903,101.182,158.436,101.182z"></path>
                   </g>
                  </svg>
                </b>
              </a>
            </div>
            
          </div>
        </div>';
            
    $output .= '
        <div class="row">
          <div class="col">'.$this->user_script.'</div>
        </div>';
        
    $output .= '
      </div>
    </div>
    <!-- End footer -->';
    
    
    return  $output;
  }
  
  function getStylesheetAndJs(){
    $output = '';

    $output .= '
    <!--  ====================== stylesheet, js  ====================== -->
    <link rel="stylesheet" type="text/css" media="all" href="/css/font-awesome/css/font-awesome.min.css">
    
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    
    <script src="/vendors/maskedinput/jquery.maskedinput.min.js"></script>
    <script type="text/javascript">
    $(function() {
       $("#phone").mask("+7 (999) 999-99-99");
       $("#UserPhone").mask("+7 (999) 999-99-99");
    });
    </script>
    
    <link rel="stylesheet" href="/vendors/fancybox3/jquery.fancybox.min.css" type="text/css" media="screen">
    <script type="text/javascript" src="/vendors/fancybox3/jquery.fancybox.min.js"></script>
    <script type="text/javascript" charset="utf-8">
    $(function(){
      $(".fancyfoto").fancybox({
        maxWidth : 800,
        maxHeight : 600,
        loop : true,
        fitToView : false,
        autoSize : false,
        closeClick : false,
        openEffect : "none",
        closeEffect : "none"
      });
    });
    </script>
    ';
    
    #<link rel="stylesheet" type="text/css" media="all" href="/css/webhostinghub-glyphs/whhg.css">
    #<script type="text/javascript" src="/js/js.js"></script>
    
    if($this->js_scripts)$output .= $this->js_scripts;
    
    return  $output;
  }
  
  function addEditAdminLink($cont = '', $link){
    $output = '';
    if (isset($_SESSION["WA_USER"])){
      $output = '
        <div class = "admin_edit_box">';
      $output .= $cont;
      $output .= '
          <a class = "admin_edit_link btn btn-info btn-sm" href = "'.$link.'" title="Редактировать">
            <i class="fa fa-pencil"></i>
          </a>
        </div>';
    }else{
      $output .= $cont; 
    }  
    
    return $output;
  }
  
  function getMineSlider(){
    $output = '';
    
    $s = "
      SELECT `".DB_PFX."carusel`.* 
      FROM `".DB_PFX."carusel`
      WHERE `".DB_PFX."carusel`.`hide` = 0
      ORDER BY `".DB_PFX."carusel`.`ord`
    "; #pri($s);
    
    if($q = $this->pdo->query($s)){
      if($count = $q->rowCount()){
        $output .= '
    <!-- mine_slider -->
    <div class="mine_slider_box">
      <div class="mine_slider">
        
        <div id="carouseMineControls" class="carousel slide" data-ride="carousel" data-interval = "35000">
          <ol class="carousel-indicators">';
            
        for($i = 0; $i < $count; $i++ ){
          (!$i) ? $active = 'class="active"' : $active = '';
          $output .= '
            <li data-target="#carouseMineControls" data-slide-to="'.$i.'" '.$active.'></li>';
        }    
        $output .= '
          </ol>
          
          <div class="carousel-inner">';
        $i = 0; 
        while($r = $q->fetch()){
          ( !$i++) ? $active = "active" : $active = ""; 
          ( $r['link'] ) ? $href = $r['link'] : $href = "#";
          if( $r['img'] ) {
            $img = "/images/carusel/slide/".$r['img'];
          }else continue ;
          
          
          $output .= '
            <div class="carousel-item '.$active.'">
              <div class="carousel_item_slide">';
          if($r['link'])
            $output .= '
                <a href = "'.$r['link'].'" target = "_blank">';
          $output .= '
                <img class="d-block w-100" src="'.$img.'" alt="'.$r['title'].'">';
          if($r['link'])
            $output .= '
                </a>';
          if($r['txt1'] || $r['longtxt1']){
            $output .= '
                 <div class="carousel-caption d-none d-md-block">';
            if($r['txt1'])
              $output .= '     
                  <h5>'.$r['txt1'].'</h5>';
            if($r['longtxt1'])
              $output .= '                  
                  <p>'.$r['longtxt1'].'</p>';
            $output .= '
                </div>';
          
          }
          $output .= '
              </div>
            </div>
          ';
        }
          $output .= '
          </div>
          <a class="carousel-control-prev" href="#carouseMineControls" role="button" data-slide="prev">
            <i class="fa fa-angle-left" aria-hidden="true"></i>
            <span class="sr-only">Предидущий</span>
          </a>
          <a class="carousel-control-next" href="#carouseMineControls" role="button" data-slide="next">
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="sr-only">Следующий</span>
          </a>
        </div>
        
      </div>
      
    </div>    
    <!-- End mine_slider -->';
      }
    }
    
    $output = $this->addEditAdminLink($output, '/wedadmin/carusel.php');
    
    return $output;
  }
  
  function getMineFerrumForm(){
    $output = '';
    
    $output .= '
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <!-- mine_application -->
    <div class="mine_application_box">
      <div class="mine_application">
       
        <div class="mine_application_cont">
          <div class="mine_application_title_box">
            <div class="mine_application_title">Отправить заявку</div>
          </div>
          <div class="mine_application_descr">Наш менеджер ответит на ваш вопрос удобным для Вас способом</div>

          <div class="application_form_box">
            <div class="application_form">
    ';
    #          <form action="#" method="post">
    $output .= '
                <div class="row">
                  <div class="col-12 col-md">
                    <div class="form-group">
                      <input type="text" class="form-control" id="formNameInput" name = "formNameInput" placeholder="Как к Вам обращаться?">
                    </div>
                  </div>
                  <div class="col-12 col-md">
                    <div class="form-group">
                      <input type="text" class="form-control" id="formNamePhone" name = "formNamePhone" placeholder="+7 (___) ___-____" pattern="(\+?\d[- .()]*){7,13}" >
                    </div>
                  </div>
                  <div class="col-12 col-md">
                    <div class="form-group">
                      <input type="text" class="form-control" id="formNameMail" name = "formNameMail" placeholder="E-mail">
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <textarea id="formYourQuestion" name="formYourQuestion" id="" cols="30" rows="7"></textarea>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 col-md recaptcha_box">
                    <div class="form-group">';
                      #<img src="/css/img/tmp/recaptcha.png" alt="">
    $output .= '
                      <div class="g-recaptcha" data-sitekey="6LeclzkUAAAAALjr8-he8iludg6DwFZD_vEymWTF"></div>
                    </div>
                  </div>
                  <div class="col-12 col-md">
                    <div class="form-group">
                      <button class="mf_btn btn">Отправить</button>
                    </div>
                  </div>
                </div>
    ';
    #          </form>
    $output .= '
              <div class="application_form_notice">Нажимая на кнопку ОТПРАВИТЬ, я подтверждаю, что ознакомился с <a href="#">политикой обработки персональных данных</a> и <a href="#">даю согласие на обработку персональных данных</a>.</div>

            </div>
          </div>
          
        </div>
        
      </div>
    </div>
      <div class="like_body mine_get_cart_line">
      
        <!-- Modal -->
        <div class="modal fade" id="getCallModal" tabindex="-1" role="dialog" aria-labelledby="myModalCallLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
            
              <div class="modal-header">
                <p class="modal-title" id="myModalCallLabel"><strong>Заявка</strong></p>
              
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
              
              </div>
              
              <div class="modal-body getCard-body ">
                
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">Закрыть</button>
                <!--<button type="button" class="btn btn-primary">Сохранить изменения</button>-->
              </div>
            </div>
          </div>
        </div>
      </div>
    <!-- End mine_application -->
    ';
    
    $this->js_scripts .= '
    <script src="/vendors/maskedinput/jquery.maskedinput.min.js"></script>
      
    <script type="text/javascript" charset="utf-8">
      $(function() {
        $("#formNamePhone").mask("+7 (999) 999-9999");
        
        $(".mf_btn").click(function(){
          
          $( "input[name$=\'formNameInput\']" ).removeClass("is-invalid");
          $( "input[name$=\'formNamePhone\']" ).removeClass("is-invalid");
          $( "input[name$=\'formNameMail\']" ).removeClass("is-invalid");
          
          var requestName = $( "input[name$=\'formNameInput\']" ).val();
          var requestPhone = $( "input[name$=\'formNamePhone\']" ).val();
          var requestMail = $( "input[name$=\'formNameMail\']" ).val();
          var requestText = $( "textarea[name$=\'formYourQuestion\']" ).val();
          
          var qRecaptchaResponse = $( "#g-recaptcha-response" ).val(); 
          
          /*if (!requestPhone){ alert("Напишите номер телевона.");}*/
          
          if (!requestName){ $( "input[name$=\'formNameInput\']" ).addClass("is-invalid"); }
          if (!requestPhone){ $( "input[name$=\'formNamePhone\']" ).addClass("is-invalid"); }
          if (!requestMail){ $( "input[name$=\'formNameMail\']" ).addClass("is-invalid"); }
          
          
          
          if (  (!requestName)  ||
                (!requestPhone) ||
                (!requestMail)  ){
      			return false;
      		}/*else if( !qRecaptchaResponse ){
            if (!qRecaptchaResponse){ alert("Введите капчу") };
            return false;
          }*/else{
            
            $.ajax({
               type: "POST",
               url: "/ajax.php",
               data: {  "good_buy" : 0,
                        "user_title" : "Заявка с сайта",
                        "userPhone" : requestPhone, 
                        "userName" : requestName, 
                        "userMail" : requestMail,
                        "userText" : requestText,
                        "g-recaptcha-response" : qRecaptchaResponse
                         
                      },
               success: function(msg){
                if(msg == "ok"){
                  $(".getCard-body").html("<span style = \'color: green;\' >Ваше заявка отправленна.<br> В ближайшее время мы с вами свяжемся!</span>");
                  $("#getCallModal").modal("show");
                  $( "input[name$=\'formNameInput\']" ).removeClass("is-invalid").val("");
                  $( "input[name$=\'formNamePhone\']" ).removeClass("is-invalid").val("");
                  $( "input[name$=\'formNameMail\']" ).removeClass("is-invalid").val("");
                  $( "textarea[name$=\'formYourQuestion\']" ).val("");
                  grecaptcha.reset();
                  
                }else{
                  $(".getCard-body").html("<span style = \'color: red;\' >"+msg+"</span>");
                  $("#getCallModal").modal("show");
                }

               }
            });
            
          }
          
        });
      });
      </script>
    ';
    
    return $output;
  }

  function getIndexContent(){
    $output = '';
    #header('Location: /makeup '); 
    $s = "
      SELECT `".DB_PFX."mine_block`.*, `".DB_PFX."url`.`url`
      FROM `".DB_PFX."mine_block`    
      LEFT JOIN `".DB_PFX."url`
      ON (`".DB_PFX."url`.`module` = '".DB_PFX."mine_block') AND (`".DB_PFX."url`.`module_id` = `".DB_PFX."mine_block`.`id`) 
      WHERE `".DB_PFX."mine_block`.`hide` = 0
      ORDER BY `".DB_PFX."mine_block`.`ord`
    "; #pri($s);
    
    if($q = $this->pdo->query($s))
      if($q->rowCount())
        while($r = $q->fetch()){
          $output .= '
    <a name="'.$r['url'].'"></a>';
          
          switch ($r['link']) {
            case  'block_mine_header':
                  $output .= $this->getMineHeader(); break;
            case  'block_mine_top_menu':
                  $output .= $this->getMineTopMenu(); break;
            case  'block_mine_slider':
                  $output .= $this->getMineSlider(); break;
            case  'block_mine_news':
                  $output .= $this->getMineNews(); break;
            case  'block_ferrum_form':
                  $output .= $this->getMineFerrumForm(); break;
                  
            default:
                  if($r['longtxt2']){
                    $output .= $this->addEditAdminLink($r['longtxt2'], '/wedadmin/mine_block.php?edits='.$r['id']);
                  }
                  break; 
          }
          
        }
    
    #$output .= $this->showVisitedPage();
    
    return $output;
  }
  
  
  
  function getInnerContent($cont){
    $output = '';
    
    if(db::value('link', '".DB_PFX."mine_block', 'link = "block_inner_content"' )){
      $this->is_block_inner_content = true;
      $s = "
        SELECT `".DB_PFX."mine_block`.*, `".DB_PFX."url`.`url`
        FROM `".DB_PFX."mine_block`    
        LEFT JOIN `".DB_PFX."url`
        ON (`".DB_PFX."url`.`module` = '".DB_PFX."mine_block') AND (`".DB_PFX."url`.`module_id` = `".DB_PFX."mine_block`.`id`) 
        WHERE `".DB_PFX."mine_block`.`hide` = 0
        AND `".DB_PFX."mine_block`.`fl_is_fixed` = 1
        ORDER BY `".DB_PFX."mine_block`.`ord`
      "; #pri($s);
      
      if($q = $this->pdo->query($s))
        if($q->rowCount())
          while($r = $q->fetch()){
            $output .= '
      <a name="'.$r['url'].'"></a>';
            
            switch ($r['link']) {
              
              case  'block_mine_header':
                    $output .= $this->getMineHeader(); break;
              case  'block_mine_top_menu':
                    $output .= $this->getMineTopMenu(); break;
              case  'block_mine_slider':
                    $output .= $this->getMineSlider(); break;
              case  'block_mine_news':
                    $output .= $this->getMineNews(); break;
              case  'block_ferrum_form':
                    $output .= $this->getMineFerrumForm(); break;
                    
              case  'block_inner_content':
                    $output .= $this->addEditAdminLink($cont, '/wedadmin'.$this->adminLink); 
                    break;
                    
              default:
                    if($r['longtxt2']){
                      $output .= $this->addEditAdminLink($r['longtxt2'], '/wedadmin/mine_block.php?edits='.$r['id']);
                    }
                    break; 
            }
            
          }
    }else{
      $output .= $cont;
    }
    
    #$output .= $this->showVisitedPage();
    
    return $output;
  }
  
  function getContentPrefix($left_menu = true){
    $output = '';
    
    #($left_menu) ? $row = "row" : $row = '';
    
    $output .= '
    <!-- content -->
    <div class="content_box">
      <div class="content">
        '.$this->bread.'
        <div class = "content_body">
    ';
    

    
    return $output;
  }
  
  function getContentPostfix($left_menu = true){
    $output = '';
    $output .= '
        </div>
      </div>
    </div>
    <!-- End content -->
    ';
    #$output .= $this->showVisitedPage();
    
    return $output;
  }
  
  function getContent(){
    $output = '';
    
    $flIsProduction = false;
    $left_menu = false;
    $cont = '';
    #pri($this);
    switch($this->module){
      
      case DB_PFX.'smpl_article':
        $this->adminLink = "/smpl_article.php";
        if($this->module_id) $this->adminLink .= "?edits=".$this->module_id;
        
        $left_menu = true;
        $cont = Article::getSmplItems($this, $this->module_id, '".DB_PFX."smpl_article');
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
      
      case DB_PFX.'news':
        $this->adminLink = "/news.php";
        if($this->module_id) $this->adminLink .= "?edits=".$this->module_id;
        
        $cont = self::getNews();
        $cont = $this->getContentPrefix(false).$cont.$this->getContentPostfix(false);
        break;
        
      case 'search':
        $cont = $this->search->showSearchItems($this);
        $cont = $this->getContentPrefix().$cont.$this->getContentPostfix();
        break;
        
      case 'backup_sql':
        echo 'backup_sql';
        $GLOBALS['DATE_UPDATE'] = date("Y-m-d H:i:s");
        self::backup();
        die();
        break;
      
      case 'robots_txt':
        echo 'robots.txt';
        die(); 
        break; 
      
      case '404':
        header("HTTP/1.0 404 Not Found");
        header("Location: /404.php"); 
        break; 
        
      default:
        $this->module = "index";
        $this->module_id = 0;
        $output .= self::getIndexContent();   
    }
    
    if($cont){
      $output = $this->getInnerContent($cont);
    }
    
    
    return $output;
  }
  
  function get404Content(){
    $output = '';
    $this->setSiteTitle('Ошибка 404. Страница не найдена.');
    
    $this->bread = '
    <div class="bread_crumbs_box ">
      <div class="bread_crumbs">
        <a href="/">Главная</a>  → <span>404</span>
      </div>      
    </div>
    ';
    
    $prefix = $this->getContentPrefix(false);
    $postfix = $this->getContentPostfix(false);
    
    $output .= '
      
    
      <div class="mine_content_box" style="min-height: 350px;" >
        
          <h1 class="md_content_h1" style="">Ошибка 404</h1>
          <div class="line_header">&nbsp;</div>
         

          Ошибка 404. Страница не найдена.
    ';
    $output .= '
      </div>
    ';
    
    return $prefix.$output.$postfix;
  }
  
  function getAdminPanel(){
    $output = '';
    if (isset($_SESSION["WA_USER"])){
      
      $this->js_scripts .= '
       <!--script src="/js/jquery.cookie.js"></script-->
       <script>
       $(function(){
       // if ($.cookie("wa_hide",{ path: "/" })) {$("#wa_panel").hide();$("#podloz").hide();}
      	$("#trigger").click(function(){
      		// $.cookie("wa_hide", "hide",{ path: "/" });
      		$("#wa_panel").slideUp(500);
      		$("#podloz").hide();
      	})	
      	$("#trigger2").click(function(){
      		// $.removeCookie("wa_hide",{ path: "/" });
      		$("#wa_panel").slideDown(500);
      		var h=$("#wa_panel").height();
      		$("#podloz").show();
      		
      	})
       })
      </script>
      <style>
        #wa_panel{
        	padding-right:0; 
        	position:fixed; 
        	top:0; 
        	right:0; 
        	width:160px; 
        	overflow:hidden;	
        	border-radius:0 0 10px 10px;
        	-webkit-box-shadow: 0 5px 3px 2px #999; 
        	box-shadow: 0 1px 3px 1px #999; z-index:1000;
          background: #fff;
          text-align: center;    
          padding-left: 0;
          z-index: 2500;
        }
        #podloz{
        	height:90px;
        }
        #trigger,#trigger2{
        	cursor:pointer;
        }
      </style>';
      
      $output .= '
      <div class="container-fluid " id="wa_panel">
        <div class="row-fluid">
          <div class="span2"><a class="btn btn-sm" target = "_blank" href="/wedadmin'.$this->adminLink.'">править в Wedadmin</a></div>
        </div>
        <div class="row-fluid" style="text-align:center">
          <i class="icon-chevron-up" id="trigger"> Х скрыть </i>
        </div>
     </div>';
    }
    
    return $output;
  }
  
  function getMineReviews(){
    $output = '';
    
    $s = "
      SELECT `".DB_PFX."reviews`.*
      FROM `".DB_PFX."reviews`
      WHERE `".DB_PFX."reviews`.`hide` = 0
      ORDER BY `".DB_PFX."reviews`.`date` DESC
      LIMIT 10
    "; #pri($s);
    $q = $this->pdo->query($s);
    
    if($q->rowCount()){
      $output .= '
    <!-- mine_reviews -->
    <div class="mine_reviews_box">
      <div class="mine_reviews">
        <div class="mine_reviews_title">Отзывы наших гостей</div>
        <div class="mine_reviews_slider_box">
          <div class="mr_oriole">
            <img src="/css/img/mr_oriole.png" alt="" />
          </div>
          
          <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">';
      $i = 0;
      while($r = $q->fetch()){
        ($i++) ? $active = '' : $active = 'active';
        $output .= '
              <div class="carousel-item '.$active.'">
                <p class="mr_img"><img src="/css/img/rbg.png" alt="" /></p>
                <p class="mr_descr">'.trim(strip_tags($r['longtxt1'])).'</p>
                <p class="mr_bot">';
        if($r['date']){
          $output .= sqlDateToRusDate($r['date']).' ';
        } 
        $output .= $r['title'].'
                </p>
              </div>';
      }
      $output .= '
          </div>
            <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
              <img src="/css/img/mr_l.png" alt="" />
              <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
              <img src="/css/img/mr_r.png" alt="" />
              <span class="sr-only">Next</span>
            </a>
          </div>
        
        </div>
        
      </div>
    </div>
    <!-- End mine_reviews -->';
    
    }
    
    return $output;
  }
  
  function getVisitedPage($link = null, $img = null, $title = null){
    $output = '';
    if(!$img) $img = '/css/img/no_photo.jpg';
    if(1/*$link && $img && $title*/){
      $output .= '
            <li>
              <a href = "'.$link.'">
                <img src="'.$img.'" />
                <div class="pop_slade_text">
                  <div class="p_s_name">'.$title.'</div>
                </div>
              </a>
            </li>
      ';
    }
    
    return $output;    
  }
  
  function addVisitedPage($module, $module_id, $cont){
    $output = '';
    
    $newHistoriArr[] = array(
                              'module'    => $module,
                              'module_id' => $module_id,
                              'cont'     => $cont
                            );
    if(is_array($this->visited_pages) && $this->visited_pages)                        
      foreach ($this->visited_pages as $his) {
        if ( ($his['module'] == $module) && ($his['module_id'] == $module_id) ) continue;
        $newHistoriArr[] = $his;
      }
    
    $this->visited_pages = $newHistoriArr;
    $_SESSION['visited_pages'] = $newHistoriArr;
    #pri($this->visited_pages);
    
    return;
  }
  
  function showVisitedPage($output = ''){
    
    if($this->visited_pages){
      $output .= '
        <!-- recently_viewed -->
        <div class="recently_viewed_box_box">
          <div class="recently_viewed">
        
            <div class="pop_goods_header">Недавно просмотренные страницы</div>
            <div class="pop_slader">
              <ul class="slides">
      ';
      foreach($this->visited_pages as $his){
        $output .= $his['cont'];
      }
      $output .= '
            </ul>
            
          </div>
          
        </div>
      </div>  
      
      <script>
        $(window).load(function() {
          $(".pop_slader").flexslider({
            animation: "slide",
            animationLoop: true,
            itemWidth: 150,
            prevText: "",
            nextText: "",  
            touch: true,
            controlNav: false
          });
        });
      </script>
      
      <!-- END recently_viewed -->
    ';
    
    }
    
    return $output;
  }
  
  function getFeedbackForm(){
    $output = '';
    
    $output = '
      <!-- Modal -->
      <div class="modal fade" id="getCardModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title" id="getCardModalLabel"><strong>Заказ обратного звонка</strong></h4>
            </div>
            <div class="modal-body getCard-body ">
              
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
              <!--<button type="button" class="btn btn-primary">Сохранить изменения</button>-->
            </div>
          </div>
        </div>
      </div>
    </div>

      <div class = "feedback_form_box">
        <div class = "feedback_form">
          <div class = "col-12 feedback_form_sub">
            <div class = "row">
              
              <div class = "col-sm-4 col-12">
                <span id="backRequestErrorName"></span>
                <input type = "text" id="nameRequest" name ="nameRequest" placeholder = "ФИО">  
              </div>
              
              <div class = "col-sm-4 col-12">
                <span id="backRequestErrorEmail"></span>
                <input type = "text" id="phoneRequest" name="phoneRequest" placeholder = "Ваш телефон">
              </div>
              
              <div class = "col-sm-4 col-12">
                <div class="sub_form_box" id = "sendFormRequest">
                  <div class="sub_form">Заказать поставку</div>
                </div>
                <div class = "sub_form_box_pointer">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>';
    $this->js_scripts .= ' 
    <script type="text/javascript" >
      $(document).ready(function() {
        
        //Заказ обратного звонка
	      jQuery("#sendFormRequest").click(function(){
		      $( "#backRequestErrorEmail").hide();
		      $( "#backRequestErrorName").hide();
		
		      var requestName  = $( "input[name$=\'nameRequest\']" ).val();
          var requestPhone = $( "input[name$=\'phoneRequest\']" ).val();
		
      		if (!requestName){
      		 	$( "#backRequestErrorName").html("<span style = \'color: red;\' >Введите Имя</span>");
      			$( "#backRequestErrorName").show();	
      		}
          
          if (!requestPhone){
      		 	$( "#backRequestErrorEmail").html("<span style = \'color: red;\' >Введите Email</span>");
      			$( "#backRequestErrorEmail").show();	
      		}
		
      		if ( (!requestName) || (!requestPhone)){
      			return false;
      		}else{
      
      
          $.ajax({
               type: "POST",
               url: "/ajax.php",
               data: {"feedback" : 1, "requestName" : requestName, "requestPhone" : requestPhone, },
               success: function(msg){
                //event.stopPropagation();
    		        //$("#formRequest").css("visibility", "hidden");
                if(msg == "ok"){
                  $(".getCard-body").html("<span style = \'color: green;\' >Ваше заявка отправленна.<br> В ближайшее время мы с вами свяжемся!</span>");
                  $("#getCardModal").modal("show");
                  $("#nameRequest").val("");
                  $("#phoneRequest").val("");
                }else{
                  $(".getCard-body").html("<span style = \'color: red;\' >"+msg+"</span>");
                  $("#getCardModal").modal("show");
                }

               }
             });
      
      
            //$("#formRequest").css("visibility", "hidden");
        }
      
      });
    });
    
    </script>
    ';
    
    
    return $output;
  }
  
  function getMobal(){
    $output = '';
    $output .= '
    <!-- Button trigger modal -->
    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <p class="modal-title" id="myModalLabel"></p>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <input type = "hidden" id = "goods_id" value = "">

          <div class="modal-body">            
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-addon input-group-text">Имя</span>
              </div>
              <input type="text" class="form-control" id = "UserName" placeholder="Иван"  >
            </div>
            <br>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-addon input-group-text">Телефон <span style = "color:#d73200;">*</span></span>
              </div>
              <input type="text" class="form-control req" id = "UserPhone" placeholder="+7 900 800-800-80" required="required" >
            </div>
            <br>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-addon input-group-text">Почта</span>
              </div>  
              <input type="mail" class="form-control" id = "UserMail" placeholder="ivan@mail.ru"  pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}">
            </div>
            <br>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-addon input-group-text">Текст</span>
              </div> 
              <textarea class="form-control glyphicon-ok" rows="5" id = "UserText"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <!--<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>-->
            <button type="button" class="btn btn-primary good_buy_mobal" >Отправить</button>
          </div>
        </div>
      </div>
    </div>

    <div id="modal_alert" class="modal fade " >
      <div class="modal-dialog alert alert-succes">
        <div class="modal-content alert alert-succes">
          <div class="modal-header">
            <p class="modal-title" id = "modal_alert_title"></p>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id = "modal_alert_body">
            <b>Спасибо! Ваша заявка успешно отправлена.</b><br><br>
            Менеджер свяжется с Вами.
          </div>
          <div class="modal-footer"><button class="btn btn-success" type="button" data-dismiss="modal">Закрыть</button></div>    </div>
      </div>
    </div>';
    
    $this->js_scripts .= '
    <script type="text/javascript" charset="utf-8">
    $(function(){
      $(".good_buy", this).click(function() {
        $("#myModalLabel").html($(this).data( "title" ));
        $("#goods_id").val($(this).data( "id" ));
      });
      
      $(".flmenu1", this).click(function() {
        $("#myModalLabel").html($(this).data( "title" ));
        $("#goods_id").val($(this).data( "id" ));
      });
      $(function() {
        //при нажатии на кнопку с id="save"
        $(".good_buy_mobal", this).click(function() {
          //переменная formValid
          var formValid = true;
          //перебрать все элементы управления input 
          $(".req").each(function() {
          //найти предков, которые имеют класс .form-group, для установления success/error
          var formGroup = $(this).parents(".input-group");
          //найти glyphicon, который предназначен для показа иконки успеха или ошибки
          var glyphicon = formGroup.find(".form-control-feedback");
          
          //для валидации данных используем HTML5 функцию checkValidity
          if (this.checkValidity()) {
            //добавить к formGroup класс .has-success, удалить has-error
            formGroup.addClass("has-success").removeClass("has-error");
            //добавить к glyphicon класс glyphicon-ok, удалить glyphicon-remove
            glyphicon.addClass("glyphicon-ok").removeClass("glyphicon-remove");
          } else {
            //добавить к formGroup класс .has-error, удалить .has-success
            formGroup.addClass("has-error").removeClass("has-success");
            //добавить к glyphicon класс glyphicon-remove, удалить glyphicon-ok
            glyphicon.addClass("glyphicon-remove").removeClass("glyphicon-ok");
            //отметить форму как невалидную 
            formValid = false;  
          }
        });
        //если форма валидна, то
        if (formValid) {
          //сркыть модальное окно
          $("#myModal").modal("hide");
          
          var  id_good = $("#goods_id").val();
          var  user_title = $("#myModalLabel").html();
          var  userName = $("#UserName").val();
          var  userPhone = $("#UserPhone").val();
          var  userMail = $("#UserMail").val();
          var  userText = $("#UserText").val();
          
          $.ajax({
           type: "POST",
           url: "/ajax.php",
           data: {"good_buy" : 1, 
                  "id_good" : id_good, 
                  "user_title" : user_title, 
                  "userName" : userName, 
                  "userPhone" : userPhone, 
                  "userMail" : userMail,
                  "userText" : userText
                 },
           success: function(msg){
            event.stopPropagation();
            
            
            if(msg == "ok"){
              //alert("Спасибо, ваш заказ принят, мы вам перезвоним!");
              $("#modal_alert_title").html("<b>Спасибо!</b>");
              $("#modal_alert_body").html("<b>Ваша заявка успешно отправлена.</b></BR></BR>Менеджер свяжется с Вами.");
              $("#myModal").modal("hide");
              $("#myModalLabel").html();
              $("#UserName").val("");
              $("#UserPhone").val("");
              $("#UserMail").val("");
              $("#UserText").val("");
              $(".good_buy_mobal").parents(".input-group").removeClass("has-success");
              $(".good_buy_mobal").parents(".input-group").removeClass("has-error");
              $("#modal_alert").modal("show");
              
              if(user_title == "Записаться на приём"){
                yaCounter36445210.reachGoal("make_appointment");
              }
              if(user_title == "Расчет стоимости лечения"){
                yaCounter36445210.reachGoal("calculation_cost_treatment");
              }
              
              
              
            }else{
              alert("Опс... кажется что то пошло не так");
              /*
              $("#modal_alert_title").html("Ошибка");
              $("#modal_alert_body").html("Опс... кажется что то пошло не так ");
              $("#myModal").modal("hide");
              $("#modal_alert").modal("show");
              */
            }

           }
         });
          //отобразить сообщение об успехе
          //$("#modal_alert").modal("show");
        }
      });
});

      
    });
    </script>
    ';
    
    return $output;
  }
  
  function backup(){
    //Узнаем делался ли бэкап сегодня
    $back_date = date('Y-m-d');
    $sql = "
    SELECT * 
    FROM ".DB_PFX."backup_file
    WHERE date_backup >= '$back_date 00:00:00'
    AND date_backup <= '$back_date 23:59:59'
    ";
    $res = mysql_query($sql);
    
    echo "back_date = $back_date <br> sql = $sql<br>";
    #die();
    
    /*
    //Удаляем папку со сатрыми картинками
    system('cd importXLS; rm -rf tmpImg; rm -rf catimages;');
    //Создаем директорию и разархивируем туда картинки
    system('cd importXLS; mkdir tmpImg;  cd zip; unzip zipFile.zip -d ../tmpImg');
    */
      
    if(!mysql_num_rows($res)){
      
    
      //Создаем бэкап базы данных 
      $temp_time = date("Y-m-d_H:i:s");
      $update = $GLOBALS['DATE_UPDATE'];
      
      $username = $_SESSION["NEX_CFG"]['db_username'];
      $password = $_SESSION["NEX_CFG"]['db_password'];
      $hostname = $_SESSION["NEX_CFG"]['db_hostname'];
      $database = $_SESSION["NEX_CFG"]['db_basename'];
      
      $backupFile = 'backup/mysql_backup/mysql_db_'.$temp_time.'.sql';
      /**
      * 
      * --skip-opt --compact --add-drop-table
      * 
      */
      $command = "mysqldump   -u$username -p$password -h$hostname $database  > $backupFile";
      #echo "database = $database<br>";
      #echo "command = $command<br>";
      #$command = "mysql -u$username -p$password -h$hostname $database > $backupFile";
      system($command, $result);
      #echo $result;
      
      /*
      //Копируем файлы с каринками
      $temp_img_dir = "img_".$temp_time;
      system("cd backup/img_backup; mkdir $temp_img_dir"); 
      system("cp -a ../images/goods backup/img_backup/$temp_img_dir");
      system("cp -a ../images/all_images  backup/img_backup/$temp_img_dir");
      system("cp -a ../images/all_files  backup/img_backup/$temp_img_dir");
      */
      
      //Добавляем запись в бд
      $sql = "
      INSERT INTO  `".DB_PFX."backup_file` ( `date_backup` ,  `is_file`, `file_name`) 
      VALUES ( '$update',  '1', '$temp_time');
      ";
      
      $res = mysql_query($sql);
      
      
      //Удаляем старые бэкапы
      
      // узнаем дату 30 бэкапа
      $sql = "
      SELECT * 
      FROM  ".DB_PFX."backup_file 
      ORDER BY  date_backup DESC 
      LIMIT 29 , 1
      ";
      
      
      $res = mysql_query($sql);
      
      //Если она есть
      if(mysql_num_rows($res)){
        $row = mysql_fetch_assoc($res);
        $date_3_backup = $row['date_backup'];
        
        //Узнаем имена файлов не удаленных бэкапов
        $sql = "
        SELECT * 
        FROM  `".DB_PFX."backup_file` 
        WHERE  `date_backup` < '$date_3_backup' 
        AND is_file =1
        ";
        
        $res  = mysql_query($sql);
        
        while($row = mysql_fetch_assoc($res)){
          $backup_name = $row['file_name'];
          //Удаляем файлы бэкапа
          //system('cd backup/img_backup; rm -rf img_'.$backup_name);
          system('cd backup/mysql_backup; rm -rf mysql_db_'.$backup_name.'.sql');
          
          //Делаем запись в бд что файлы удалены
          $up_id = $row['id'];
          $sql_up = 
          "
          UPDATE  `".DB_PFX."backup_file` SET  `is_file` =  '0' WHERE  `id` = $up_id LIMIT 1 ;
          ";
          
          $res_up = mysql_query($sql_up);
        }
        
      }
      
    }

  }
   
  function get_phpmorphy($descr_str) {
    global $morphy;
    
    $descr_str = strip_tags($descr_str);
    
    $descrs = str_word_count($descr_str, 1, "АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя0123456789");
    $orm_search = '';
    foreach($descrs as $descr){
      
      $des = mb_strtoupper($descr, 'UTF-8');
      //echo "des = $des<br>";
      $collection = $morphy->findWord($des);
      if(false === $collection) { 
        #echo $des, " NOT FOUND\n<br>";
        $orm_search .= $des." ";
        continue;
      } else {
        
      }
    
      foreach($collection as $paradigm) {
        #echo "lemma: ", $paradigm[0]->getWord(), "\n<br>";
        $orm_search .= $paradigm[0]->getWord()." ";
        break;
      }
    }
    
    $orm_search = trim($orm_search);
    
    return $orm_search;
  }

  function compress_html($compress){
    $i = array('/>[^S ]+/s','/[^S ]+</s','/(s)+/s');
    $ii = array('>','<','1');
   
    return preg_replace($i, $ii, $compress);
  }

  function showSite($view = 'content'){
    
    switch($view){
      case 'content':
        $this->siteContent = self::getContent();
      break; 
      
      case '404':
        $this->siteContent = self::get404Content();
      break; 
    }
    
    $this->siteHead = self::getHead();
    $this->siteHeader = self::getHeader(); 
    $this->siteFooter = self::getFooter(); 
    
    $output = '';
    
    $output .= $this->siteDoctype;
    $output .= '
<html lang="ru">
  <head>
    <meta http-equiv="content-language" content="ru" />';
    $output .= '
    '.$this->siteCharset.'
    <title>'.$this->siteTitle.'</title>
    <meta name="description" content="'.$this->siteDescription.'">
    <meta name="keywords" content="'.$this->siteKeywords.'">';
    
    $output .= $this->siteHead;
    $output .= '
  </head>
  <body>';
    $cont = $this->siteContent;
    $output .= $this->siteHeader;
    $output .= $cont;
    $output .= $this->siteFooter;
    $output .= $this->getMobal();
    $output .= $this->getAdminPanel();
    $output .= $this->getStylesheetAndJs();
    $output .= '
  <body>
</html>';
    
    return  $output;
  }
  
  
}
