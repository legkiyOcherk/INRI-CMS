<?php #echo "Я Setup)";
require_once('lib/class.Admin.php');
require_once('lib/class.Setup.php');
$admin = new Admin();
$output = '';


class SetupSite extends Setup{
  
}

$setup = new SetupSite();
$setup->add_content($setup->wrap_block('<p>Установка курсов валют</p>'));

$output .= $setup->show();


echo $output;

/*
if($output = $carisel->getContent($output)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}*/
