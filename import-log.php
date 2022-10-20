<?php

// Initialization
include_once('includes/load.php');

$id_log = isset($_GET['id']) ? $_GET['id'] : '';
$directory = IMPORTS_DIR . 'import-' . $id_log . '/';

$log = file_get_contents($directory . 'log.json');
$log = json_decode($log);

$data = isset($log->data) ? $log->data : '';
$settings = isset($log->settings) ? $log->settings : '';

?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>CSV IMPORT & EXPORT</title>
	<meta name="description" content="CSV Import & Export">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Google web fonts -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500" rel="stylesheet">

    <!-- Bootstrap -->
	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">

    <!-- Web Icons -->
	<link rel="stylesheet" href="css/web-icons.css">

    <!-- The main CSS file -->
	<link rel="stylesheet" href="css/styles.css">

</head>

<body>

    <?php
    // NAVBAR & SIDEBAR
    $current_page = basename(__FILE__, '.php');
    include('navbar-sidebar.php');
    ?>

	<div id="main" class="with-navbar with-sidebar">

        <h1>Log</h1>

        <div class="panel clearfix">
            <div class="panel-body">

                <?php if ($log) { ?>


                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#log_resume">Resume</a></li>
                    <li><a data-toggle="tab" href="#log_settings">Settings</a></li>
                    <li><a data-toggle="tab" href="#log_details">Details</a></li>
                </ul>

                <div class="tab-content">

                    <div id="log_resume" class="tab-pane fade in active">

                        <div id="review" class="row">

                            <div id="review-input" class="col-sm-6 clearfix">
                                <div class="pull-left">
                                    <div class="file">
                                        <img src="images/file.png">
                                        <span>CSV</span>
                                    </div>
                                    <small class="input-size"><?php echo formatBytes($data->size); ?></small>
                                </div>
                                <div class="clearfix">
                                    <h4><?php echo $data->filename; ?></h4>
                                    <p class="review-input-cols"><span class="input-cols"><?php echo $data->input_cols; ?></span> columns</p>
                                    <p class="review-input-rows"><span class="input-rows"><?php echo $data->input_rows; ?></span> records</p>
                                </div>
                            </div>

                            <div id="review-progress">
                                <span class="review-text">IMPORT</span>
                                <span class="triangle"></span>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-success" role="progressbar">
                                        <span class="sr-only"></span>
                                    </div>
                                    <div class="progress-label"></div>
                                </div>
                            </div>

                            <div id="review-output" class="col-sm-6 clearfix">
                                <img src="images/db.png" class="pull-right">
                                <div class="clearfix">
                                    <h4><?php echo $data->table; ?></h4>
                                    <p id="review-output-cols"><span class="output-cols loading"><?php echo $data->output_cols; ?></span> columns</p>
                                    <p id="review-output-rows"><span class="output-rows loading"><?php echo $data->output_rows; ?></span> records</p>
                                </div>
                            </div>

                        </div>

                        <div id="report" class="clearfix">

                            <div id="time-info" class="pull-left">
                                <h4 id="time-elapsed">Time Elapsed: <span>
                                <?php
                                echo miliseconds2human($data->time_elapsed);
                                ?>
                                </span></h4>
                                <div class="date_begin" style="display: block;"><span>Date begin: </span><span><?php echo str_replace('.', '.<small>', $data->date_begin) . '</small>'; ?></span></div>
                                <div class="date_end" style="display: block;"><span>Date end: </span><span><?php echo str_replace('.', '.<small>', $data->date_end) . '</small>'; ?></span></div>
                            </div>

                            <div id="results" class="pull-right text-right">

                                <h4 class="total">Imported: <span id="results-rows"><?php echo $data->total_imported; ?></span> / <span id="results-total-rows"><?php echo $data->output_rows; ?></span></h4>
                                <table class="pull-right">
                                    <tr class="text-info"<?php if ($data->total_skipped) echo ' style="display: table-row;"'; ?>>
                                        <td>SKIPED: </td>
                                        <td><?php echo $data->total_skipped; ?></td>
                                    </tr>
                                    <tr class="text-success"<?php if ($data->total_inserted) echo ' style="display: table-row;"'; ?>>
                                        <td>INSERTED: </td>
                                        <td><?php echo $data->total_inserted; ?></td>
                                    </tr>
                                    <tr class="text-primary"<?php if ($data->total_updated) echo ' style="display: table-row;"'; ?>>
                                        <td>UPDATED: </td>
                                        <td><?php echo $data->total_updated; ?></td>
                                    </tr>
                                    <tr class="text-warning"<?php if ($data->total_deleted) echo ' style="display: table-row;"'; ?>>
                                        <td>DELETED: </td>
                                        <td><?php echo $data->total_deleted; ?></td>
                                    </tr>
                                    <tr class="text-danger"<?php if ($data->total_error) echo ' style="display: table-row;"'; ?>>
                                        <td>ERROR: </td>
                                        <td><?php echo $data->total_error; ?></td>
                                    </tr>
                                </table>

                            </div>

                        </div>

                        <button id="btn-download-file" type="button" data-filename="<?php echo $data->filename; ?>" class="btn btn-sm btn-outline-info" style="margin-top: 30px;">Download file</button>

                    </div>

                    <div id="log_settings" class="tab-pane fade">

                        <div class="row">

                            <div class="col-md-6">
                                <table class="log_table" style="margin-bottom: 30px;">
                                    <tbody>
                                        <tr>
                                            <td>File source: </td>
                                            <td><?php echo $settings->file_source; ?></td>
                                        </tr>
                                        <?php if ($settings->file_source == 'desktop') { ?>
                                        <tr>
                                            <td>File: </td>
                                            <td><?php echo $settings->file_desktop; ?></td>
                                        </tr>
                                        <?php } ?>
                                        <?php if ($settings->file_source == 'url') { ?>
                                        <tr>
                                            <td>Url: </td>
                                            <td><?php echo $settings->url; ?></td>
                                        </tr>
                                        <?php } ?>
                                        <tr class="space">
                                            <td>Header: </td>
                                            <td><?php echo $settings->header ? 'Yes' : 'No'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Delimiter: </td>
                                            <td><?php echo $settings->delimiter; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Enclosure: </td>
                                            <td><?php echo $settings->enclosure; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Encoding: </td>
                                            <td><?php echo $settings->encoding; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <table class="log_table">
                                    <tbody>
                                        <tr>
                                            <td>Database: </td>
                                            <td><?php echo $settings->db; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Table: </td>
                                            <td><?php echo $settings->table; ?></td>
                                        </tr>
                                        <tr class="space">
                                            <td>Mapping: </td>
                                            <td>
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>DB</th>
                                                            <th style="width: 34px;"></th>
                                                            <th>CSV</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($settings->mapping as $mapping) { ?>
                                                        <tr>
                                                            <td><?php echo $mapping->label; ?></td>
                                                            <td class="text-center"><i class="wb wb-arrow-left"></i></td>
                                                            <td><?php echo $mapping->csv_header . ' <small>(COL#' . $mapping->csv_column . ')</small>'; ?></td>
                                                        </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr class="space">
                                            <td>Rows to import: </td>
                                            <td><?php if ($settings->filter_rows == '') echo 'All'; else echo $settings->filter_rows; ?></td>
                                        </tr>
                                        <tr class="space">
                                            <td>Field that defines a duplicate: </td>
                                            <td>
                                                <?php
                                                if (count($settings->duplicates) == 0)
                                                    echo '-';
                                                else
                                                    echo $settings->duplicates->label;
                                                ?>
                                            </td>
                                        </tr>
                                        <?php if (count($settings->duplicates) > 0) { ?>
                                        <tr>
                                            <td>- actions for the duplicates: </td>
                                            <td><?php echo ucfirst($settings->actions_duplicates); ?></td>
                                        </tr>
                                        <tr>
                                            <td>- actions for the rest: </td>
                                            <td>
                                                <?php
                                                if ($settings->actions_rest_create)
                                                    echo 'Create new records from data newly present in your file';
                                                if ($settings->actions_rest_create and $settings->actions_rest_delete)
                                                    echo '<br>';
                                                if ($settings->actions_rest_delete)
                                                    echo 'Delete records that are no longer present in your file';
                                                ?>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>

                        <button id="btn-download-settings" type="button" data-filename="log.json" class="btn btn-sm btn-outline-info" style="margin-top: 30px;">Download settings</button>

                    </div>

                    <div id="log_details" class="tab-pane fade">

                        <table id="logDetailsTable">
                            <thead>
                                <tr>
                                    <th data-sortable="true" data-width="55" class="row">ROW</th>
                                    <th data-sortable="true" data-width="55" class="status">STATUS</th>
                                    <th data-sortable="true" class="message">MESSAGE</th>
                                    <?php foreach ($settings->mapping as $mapping) { ?>
                                    <th data-sortable="true"><?php echo $mapping->label; ?></th>
                                    <?php } ?>
                                </tr>
                            </thead>
                        </table>

                        <button id="btn-download-details" type="button" data-filename="log_details.csv" class="btn btn-sm btn-outline-info" style="margin-top: 30px;">Download details</button>

                    </div>

                </div>

                <iframe id="downloadFrame" src=""></iframe>

            <?php } ?>

            </div>    <!-- panel-body -->
        </div>    <!-- panel -->

    </div>

    <script>
    // Global Config Vars
    var id_log = <?php echo $id_log; ?>;
    var total_imported = <?php echo $data->total_imported; ?>;
    var output_rows = <?php echo $data->output_rows; ?>;
    </script>

    <!-- JavaScript Includes -->

    <!-- jQuery -->
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>-->
    <script src="vendor/jquery/jquery-1.12.4.min.js"></script>

    <!-- Bootstrap -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Bootstrap Table -->
    <script src="vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="vendor/bootstrap-table/extensions/cookie/bootstrap-table-cookie.min.js"></script>

    <!-- Our main JS file -->
    <script src="js/import-log.js"></script>

</body>

</html>