<?php
require_once('lib/class.SiteBase.php');  
 
class SiteСutaway extends SiteBase{
  
}

class SiteCorporate extends SiteBase{
  
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
                  
                  <ul class="navbar-nav mr-auto">';
    $output .= Article::show_head_chief_menu2($this, DB_PFX.'articles_cat', DB_PFX.'articles', DB_PFX.'url'); # Меню с выподашкой cat_articles
    #$output .= Article::show_head_chief_menu($this); # Меню cat_articles
    #$output .= Article::show_simple_menu($this);     # Меню smpl_article
    $output .= '
                  </ul>

                </div>
              </nav>
              
            </div>
          </div>
          <!-- End top_menu -->
    ';
    
    $output = $this->addEditAdminLink($output, '/wedadmin/article.php');
    
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
            <ul class="footer_menu ">';
    $output .= Article::show_footer_menu($this, DB_PFX.'articles_cat', DB_PFX.'articles', DB_PFX.'url');
    #$output .= Article::show_simple_menu($this);
    $output .= '
            </ul>
          </div>';
    
    if($this->soc_net){
      $output .= '
          <div class="col-12 soc_net_box">
            <div class = "soc_net">'.$this->soc_net.'</div>
          </div>';
    }
          
    $output .= '      
        </div>';
    if( isset($this->phone_header) && $this->phone_header ){
      $output .= '
        <div class="row">
          <div class="col-12 tac">
            '.$this->phone_header.'
          </div>
        </div>';
    }
    if( isset($this->adress_header) && $this->adress_header ){
      $output .= '
        <div class="row">
          <div class="col-12 tac">
            '.$this->adress_header.'
          </div>
        </div>';
    }
    $output .= '
      </div>
    </div>
    <!-- End footer -->';
    
    
    return  $output;
  }
  
  function getContent(){
    $output = '';
    $flIsProduction = false;
    $left_menu = false;
    $cont = '';
    #pri($this); 
    switch($this->module){
      
      case DB_PFX.'articles_cat':
        $this->adminLink = "/articles.php";
        if($this->module_id){
          $this->adminLink .= "?editc=".$this->module_id;
          $_SESSION['articles']['c_id'] = $this->module_id;
        }
        (isset($this->left_menu) && $this->left_menu) ? $left_menu = true : $left_menu = false;
        
        $cont = Article::getCatItems($this, $this->module_id, DB_PFX.'articles_cat', DB_PFX.'articles');
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
        
      case DB_PFX.'articles':
        $this->adminLink = "/articles.php";
        if($this->module_id){
          $this->adminLink .= "?edits=".$this->module_id;
          $_SESSION['articles']['c_id'] = db::value("cat_id", DB_PFX."articles", "id = ".$this->module_id );
        }        
        (isset($this->left_menu) && $this->left_menu) ? $left_menu = true : $left_menu = false;
        
        $cont = Article::getItems($this, $this->module_id, DB_PFX.'articles_cat', DB_PFX.'articles');
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
       
      case DB_PFX.'news':
        $this->adminLink = "/news.php";
        if($this->module_id) $this->adminLink .= "?edits=".$this->module_id;
        
        $cont = News::getNews($this, $this->module_id, DB_PFX."news");
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
  
 
}

class SiteOnlineshop extends SiteBase{
  
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
                  
                  <ul class="navbar-nav mr-auto">';
    $output .= Article::show_head_chief_menu2($this, DB_PFX.'articles_cat', DB_PFX.'articles', DB_PFX.'url'); # Меню с выподашкой cat_articles
    #$output .= Article::show_head_chief_menu($this); # Меню cat_articles
    #$output .= Article::show_simple_menu($this);     # Меню smpl_article 
    $output .= '
                  </ul>

                </div>
              </nav>
              
            </div>
          </div>
          <!-- End top_menu -->
    ';
    
    $output = $this->addEditAdminLink($output, '/wedadmin/article.php');
    
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
            <ul class="footer_menu ">';
    $output .= Article::show_footer_menu($this, DB_PFX.'articles_cat', DB_PFX.'articles', DB_PFX.'url');
    #$output .= Article::show_simple_menu($this);
    $output .= '
            </ul>
          </div>';
    
    if($this->soc_net){
      $output .= '
          <div class="col-12 soc_net_box">
            <div class = "soc_net">'.$this->soc_net.'</div>
          </div>';
    }
          
    $output .= '      
        </div>';
    if( isset($this->phone_header) && $this->phone_header ){
      $output .= '
        <div class="row">
          <div class="col-12 tac">
            '.$this->phone_header.'
          </div>
        </div>';
    }
    if( isset($this->adress_header) && $this->adress_header ){
      $output .= '
        <div class="row">
          <div class="col-12 tac">
            '.$this->adress_header.'
          </div>
        </div>';
    }
    $output .= '
      </div>
    </div>
    <!-- End footer -->';
    
    
    return  $output;
  }
  
  function getContent(){
    $output = '';
    $flIsProduction = false;
    $left_menu = false;
    $cont = '';
    #pri($this); 
    switch($this->module){
      
      case DB_PFX.'goods_cat':
        $this->adminLink = "/goods.php";
        if($this->module_id){
          $this->adminLink .= "?editc=".$this->module_id;
          $_SESSION['goods']['c_id'] = db::value("cat_id", DB_PFX.'goods', "id = ".$this->module_id );
        }
        
        $this->cat_arr = db::select('id, parent_id, title, hide', DB_PFX.'goods_cat');
        $cont = Goods::show_cat_items($this, $this->module_id, DB_PFX.'goods_cat', DB_PFX.'il_goods');
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
        
      case DB_PFX.'goods':
        $this->adminLink = "/goods.php";
        if($this->module_id){
          $this->adminLink .= "?edits=".$this->module_id;
          $_SESSION['goods']['c_id'] = db::value("cat_id", DB_PFX.'goods', "id = ".$this->module_id );
        }
        
        $this->cat_arr = db::select("id, parent_id, title, hide", DB_PFX.'goods_cat');
        $item = db::row('*', DB_PFX.'goods', "id = ".$this->module_id);
        $cont = Goods::show_item_full($this, $item);
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
      
      case DB_PFX.'articles_cat':
        $this->adminLink = "/articles.php";
        if($this->module_id){
          $this->adminLink .= "?editc=".$this->module_id;
          $_SESSION['articles']['c_id'] = $this->module_id;
        }
        (isset($this->left_menu) && $this->left_menu) ? $left_menu = true : $left_menu = false;
        
        $cont = Article::getCatItems($this, $this->module_id, DB_PFX.'articles_cat', DB_PFX.'articles');
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
        
      case DB_PFX.'articles':
        $this->adminLink = "/articles.php";
        if($this->module_id){
          $this->adminLink .= "?edits=".$this->module_id;
          $_SESSION['articles']['c_id'] = db::value("cat_id", DB_PFX."articles", "id = ".$this->module_id );
        }        
        (isset($this->left_menu) && $this->left_menu) ? $left_menu = true : $left_menu = false;
        
        $cont = Article::getItems($this, $this->module_id, DB_PFX.'articles_cat', DB_PFX.'articles');
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
       
      case DB_PFX.'news':
        $this->adminLink = "/news.php";
        if($this->module_id) $this->adminLink .= "?edits=".$this->module_id;
        
        $cont = News::getNews($this, $this->module_id, DB_PFX."news");
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
  
} 

switch(SITE_TYPE){
  
  case 'CUTAWAY':
    class Site extends SiteСutaway{};
    break;
  
  case 'CORPORATE':
    class Site extends SiteCorporate{};
    break;
    
  case 'ONLINESHOP':
    class Site extends SiteOnlineshop{};
    break;
  
  default:
    die('Не задан тип сайта');
    break;
}

