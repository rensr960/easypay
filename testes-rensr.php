<?php
include_once('../../config/config.inc.php');
include_once('../../init.php');

global $cookie;
$id_lang = $cookie->id_lang;

$newGroup = new AttributeGroup(null, $id_lang);
$newGroup->name = 'tests';
$newGroup->public_name = 'tests';
$newGroup->group_type = 'select';
$newGroup->add();

echo json_encode($newGroup);

?>