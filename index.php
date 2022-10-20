<?php

// Initialization
include_once('includes/load.php');

// If there is an action
if (isset($_GET['action']) || isset($_POST['action']))
    // Call the actions script
    include_once('actions.php');
else
    // Includes the default page
    include_once('import-list.php');
