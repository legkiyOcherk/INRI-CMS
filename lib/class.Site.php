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
    $output .= Article::show_head_chief_menu2($this); # Меню с выподашкой cat_articles
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
 
}

class SiteOnlineshop extends SiteBase{
  
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

