<?php

$new_style_template = true;

include_once '../../../includes/easyparliament/init.php';
include_once INCLUDESPATH . 'easyparliament/member.php';

$alert = new MySociety\TheyWorkForYou\AlertView\Simple($THEUSER);
$data = $alert->display();

MySociety\TheyWorkForYou\Renderer::output('alert/postcode', $data);

