<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
$output = AllFunction::OneSeoFormAdmin( 'Оформление сайта', 'il_design', $admin); 

$admin->setContent($output);
echo $admin->showAdmin('content');