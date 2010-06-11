<?php
    $strplagiarism = get_string('turnitin', 'turnitin');
    $strplagiarismdefaults = get_string('turnitindefaults', 'turnitin');
    $strplagiarismerrors = get_string('turnitinerrors', 'turnitin');
    $tabs = array();
    $tabs[] = new tabobject('turnitinsettings', 'turnitin.php', $strplagiarism, $strplagiarism, false);
    $tabs[] = new tabobject('turnitindefaults', 'turnitin_defaults.php', $strplagiarismdefaults, $strplagiarismdefaults, false);
    $tabs[] = new tabobject('turnitinerrors', 'turnitin_errors.php', $strplagiarismerrors, $strplagiarismerrors, false);
    print_tabs(array($tabs), $currenttab);