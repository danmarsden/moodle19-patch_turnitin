<?php
//file for running Turnitin teacher sync script
require_once("../config.php");
require_once($CFG->libdir."/turnitinlib.php");

turnitin_cron_sync_teachers(false, false);