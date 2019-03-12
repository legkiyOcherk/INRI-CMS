<?php #echo "Я Setup)";
#require_once('lib/class.Admin.php');
require_once('lib/class.Setup.php');
#$admin = new Admin();
$output = '';


class SetupSite extends Setup{
  
  function __construct (){
    
    parent::__construct();
    
    $this->nav_items_arr['/'.ADM_DIR.'/'.$this->script_name.'?step=setup_database_access' ] = '1. Доступ к базе данных';
    
    $this->nav_items_arr['/'.ADM_DIR.'/'.$this->script_name.'?step=setup_database_module' ] = '2. Установка модулей';
  }
  
  
  
  function setup_database_module_all(){
    
    parent::setup_database_module_all();
    
    switch(SITE_TYPE){
      case 'CUTAWAY':
        $this->setup_database_module_cutaway();    #Сайт визитка 
        break;
      
      case 'CORPORATE':
        $this->setup_database_module_corporate();  #Корпаротивный/новостной сайт 
        break;
        
      case 'ONLINESHOP':
        $this->setup_database_module_onlineshop(); #Онлайн магазин 
        break;
      
      default:
        break;
    }

  }
  
  function show(){
    $output = $step = '';
    
    if( isset($_GET['step']) && $_GET['step'] ) $step = $_GET['step']; #pri($step);
    
    switch ($step){
      case 'setup_database_access': #Подключение к базе данных
        $this->set_content($this->wrap_block($this->setup_database_access())) ;
        break;
      
      case 'setup_database_module': #Установка модулей
        $this->setup_database_module();
        break;
      
      case 'delete_database_module': #Установка модулей
        $this->delete_database_module();
        break;
    }
    
    $output = parent::show();
    
    return $output;
  }
}

$setup = new SetupSite();
#$setup->add_content($setup->wrap_block('<p>Установка курсов валют</p>')); 

$output .= $setup->show();


echo $output;

/*
if($output = $carisel->getContent($output)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}*/
