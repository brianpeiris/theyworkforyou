<?php

include_once dirname(__FILE__) . '/../www/includes/easyparliament/init.php';


// create the cohorts table

echo "Generating party policy comparisons\n";
MySociety\TheyWorkForYou\PartyCohort::populateCohorts();
MySociety\TheyWorkForYou\PartyCohort::calculatePositions(false);
