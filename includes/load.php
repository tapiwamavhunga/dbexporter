<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

// Configurations
include_once('config/config.php');

// Databases
include_once(DATABASES_FILE);
if(!isset($_databases))
    $_databases = array();

// ezSQL - MySQL abstraction
include_once("includes/ez_sql_core.php");
include_once("includes/ez_sql_mysqli.php");

// Core
include_once("includes/core_db.php");

// ParseCSV
include_once("includes/parsecsv.lib.php");

// Functions
include_once("includes/functions.php");

// If there is an action
if (isset($_GET['action']) or isset($_POST['action']))
    include_once('actions.php');
