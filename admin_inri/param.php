<?
require_once('lib/class.Admin.php');
$admin = new Admin();

$output = AllFunction::OneFormAdmin( 'Блок контакты', 'config', $admin);

$admin->setContent($output);
echo $admin->showAdmin('content');