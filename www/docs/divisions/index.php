<?php

include_once '../../includes/easyparliament/init.php';
include_once INCLUDESPATH . 'easyparliament/member.php';

$this_page = 'divisions_recent';

$MEMBER = null;
if ($THEUSER->postcode_is_set()) {
    $MEMBER = new MySociety\TheyWorkForYou\Member(array('postcode' => $THEUSER->postcode(), 'house' => HOUSE_TYPE_COMMONS));
}

$divisions = new MySociety\TheyWorkForYou\Divisions($MEMBER);
$data = $divisions->getRecentDivisions(30);

if (isset($MEMBER)) {
    $data['mp_name'] = ucfirst($MEMBER->full_name());
}

$data['last_updated'] = MySociety\TheyWorkForYou\Divisions::getMostRecentDivisionDate()['latest'];

$template = 'divisions/index';
MySociety\TheyWorkForYou\Renderer::output($template, $data);
