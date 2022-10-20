<?php

// Initialization
include_once('includes/load.php');

$id_log = isset($_GET['id']) ? $_GET['id'] : '';
$directory = EXPORTS_DIR . 'export-' . $id_log . '/';

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
                                <img src="images/db.png" style="float: left;">
                                <div class="clearfix">
                                    <h4><?php echo $data->table; ?></h4>
                                    <p class="review-input-cols"><span class="input-cols"><?php echo $data->input_cols; ?></span> columns</p>
                                    <p class="review-input-rows"><span class="input-rows"><?php echo $data->input_rows; ?></span> records</p>
                                </div>
                            </div>

                            <div id="review-progress">
                                <span class="review-text">EXPORT</span>
                                <span class="triangle"></span>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-success" role="progressbar">
                                        <span class="sr-only"></span>
                                    </div>
                                    <div class="progress-label"></div>
                                </div>
                            </div>

                            <div id="review-output" class="col-sm-6 clearfix">
                                <div class="pull-right">
                                    <div class="file">
                                        <img src="images/file.png">
                                        <span>CSV</span>
                                    </div>
                                    <small class="input-size"><?php echo formatBytes($data->size); ?></small>
                                </div>
                                <div class="clearfix">
                                    <h4><?php echo $data->filename; ?></h4>
                                    <p id="review-output-cols"><span class="output-cols loading"><?php echo $data->output_cols; ?></span> columns</p>
                                    <p id="review-output-rows"><span class="output-rows loading"><?php echo $data->output_rows; ?></span> records</p>
                                </div>
                            </div>

                        </div>

                        <div id="report" class="row">

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

                                <h4 class="total">Exported: <span id="results-rows"><?php echo $data->total_exported; ?></span> / <span id="results-total-rows"><?php echo $data->output_rows; ?></span></h4>
                                <table class="pull-right">
                                    <tr class="text-success"<?php if ($data->total_success) echo ' style="display: table-row;"'; ?>>
                                        <td>SUCCESS: </td>
                                        <td><?php echo $data->total_success; ?></td>
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

                            <div class="col-sm-6">
                                <table class="log_table" style="margin-bottom: 30px;">
                                    <tbody>
                                        <tr>
                                            <td>Database: </td>
                                            <td><?php echo $settings->db; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Table: </td>
                                            <td><?php echo $settings->table; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-sm-6">
                                <table class="log_table">
                                    <tbody>
                                        <?php if ($settings->file_source == 'desktop') { ?>
                                        <tr>
                                            <td>File name: </td>
                                            <td><?php echo $settings->filename; ?></td>
                                        </tr>
                                        <?php } ?>
                                        <?php if ($settings->file_source == 'ftp') { ?>
                                        <tr>
                                            <td>FTP: </td>
                                            <td><?php echo $settings->ftp; ?></td>
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
                                        <tr class="space">
                                            <td>Mapping: </td>
                                            <td>
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>DB</th>
                                                            <th></th>
                                                            <th>CSV</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($settings->mapping as $mapping) { ?>
                                                        <tr>
                                                            <td><?php echo $mapping->label; ?></td>
                                                            <td class="text-center"><i class="wb wb-arrow-right"></i></td>
                                                            <td><?php echo $mapping->csv_header . (trim($mapping->csv_header) != '' ? ' ' : '') . '<small>(COL#' . $mapping->csv_column . ')</small>'; ?></td>
                                                        </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr class="space">
                                            <td>Rows to export: </td>
                                            <td><?php if ($settings->filter_rows == '') echo 'All'; else echo $settings->filter_rows; ?></td>
                                        </tr>
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
                                    <th data-sortable="true"><?php echo $mapping->csv_header . (trim($mapping->csv_header) != '' ? ' ' : '') . '<small>(COL#' . $mapping->csv_column . ')</small>'; ?></th>
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
    var total_exported = <?php echo $data->total_exported; ?>;
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
    <script src="js/export-log.js"></script>

</body>

</html>