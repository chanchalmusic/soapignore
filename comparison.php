<?php

// get all links of old sites
$old = array('1', '3', '5', '7');

// get all links of new site
$new = array('2', '5', '6', '8');

$diff = array_diff($old, $new);

print_r($diff);