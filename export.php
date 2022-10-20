<?php

// Initialization
include_once('includes/load.php');

$core = new Core($_databases);

$_dbs = $core->getGoodDBs();
$first_database_key = is_array($_dbs) ? key($_dbs) : array();
$_tables = $core->getGoodTables($first_database_key);

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
	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.css">

    <!-- Bootstrap Select -->
	<link rel="stylesheet" href="vendor/bootstrap-select/css/bootstrap-select.min.css">

    <!-- Form Validation -->
	<link rel="stylesheet" href="vendor/formvalidation/formValidation.min.css">

    <!-- Web Icons -->
	<link rel="stylesheet" href="css/web-icons.css">

    <!-- iCheck -->
	<link rel="stylesheet" href="vendor/icheck/skins/flat/blue.css">

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

        <?php if (!count($_dbs)) { ?>

        <h1>Error</h1>

        <div class="panel-body clearfix alert alert-danger dark alert-icon">
            <h4>No Database found!</h4>
            Please go to <a href="settings.php" class="alert-link"><i>SETTINGS > DATABASES</i></a> and configure one.
        </div>

        <?php } else { ?>

        <h1>Export</h1>

        <div class="panel clearfix">
            <div class="panel-body">

                <div id="preview">
                    <a id="previewInputButton" href="#previewInput" title="View input Database data"><i class="icon wb-table"></i></a>
                    <i class="icon wb-arrow-right" style="font-size: 9px; color: #e0e0e0;"></i>
                    <a id="previewOutputButton" href="#previewOutput" title="Preview data to be exported"><i class="icon wb-file"></i></a>
                </div>

                <div id="wizard">

                    <div class="wizard-inner">

                        <ul class="nav nav-pills" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#step1" data-toggle="tab" aria-controls="step1" role="tab" title="Table">
                                    <span class="round-tab">
                                        <i class="icon wb-table"></i>
                                    </span>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#step2" data-toggle="tab" aria-controls="step2" role="tab" title="Settings">
                                    <span class="round-tab">
                                        <i class="icon wb-settings"></i>
                                    </span>
                                </a>
                            </li>
                            <li role="presentation" class="disabled">
                                <a href="#step3" data-toggle="tab" aria-controls="step3" role="tab" title="Export">
                                    <span class="round-tab">
                                        <i class="icon wb-check"></i>
                                    </span>
                                </a>
                            </li>
                        </ul>

                    </div>

                    <form id="cie-form" role="form">
                        <input id="file_destination" type="hidden" name="file_destination" value="desktop" />

                        <div class="tab-content">

                            <div id="step1" class="tab-pane fade in active">

                                <?php if (count($_dbs) == 1 and $_hide_only_one_database) { ?>
                                <input id="db" type="hidden" name="db" value="<?php echo $first_database_key; ?>">
                                <?php } ?>

                                <h2>
                                    Table
                                    <small>Select the database table that you want to export</small>
                                </h2>

                                <div class="row">

                                    <?php if (count($_dbs) > 1 or !$_hide_only_one_database) { ?>
                                    <div class="form-group col-sm-6">
                                        <label>DB</label>
                                        <select id="db" name="db" class="selectpicker">
                                            <?php
                                            foreach ($_dbs as $key => $db)
                                                echo '<option value="' . $key . '">' . $db['label'] . '</option>';
                                            ?>
                                        </select>
                                    </div>
                                    <?php } ?>

                                    <div class="form-group col-sm-6">
                                        <label>Table</label>
                                        <select id="table" name="table" class="selectpicker">
                                            <?php
                                            foreach ($_tables as $key => $table)
                                                echo '<option value="' . $key . '" data-rel="' . $first_database_key . '">' . $table['label'] . '</option>';
                                            ?>
                                        </select>
                                    </div>

                                </div>

                                <div class="clearfix" style="margin-bottom: 40px;"></div>

                            </div>

                            <div id="step2" class="tab-pane">

                                <h2>
                                    Location
                                    <small>Choose the location of the file to export</small>
                                </h2>

                                <ul class="nav nav-tabs">
                                    <li class="active"><a data-toggle="tab" href="#desktop">Desktop</a></li>
                                    <li><a data-toggle="tab" href="#ftp">FTP</a></li>
                                    <li><a data-toggle="tab" href="#server">Server</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div id="desktop" class="tab-pane fade in active">
                                        <div class="form-group">
                                            <label>Filename</label>
                                            <input type="text" name="filename" class="form-control" value="" />
                                            <small class="help-block">ex: file.csv</small>
                                        </div>
                                    </div>
                                    <div id="ftp" class="tab-pane fade">
                                        <div class="row">
                                            <div class="form-group col-sm-6">
                                                <label>Address or URL</label>
                                                <input type="text" name="address_url" value="" class="form-control" />
                                            </div>
                                            <div class="form-group col-sm-6">
                                                <label>Port</label>
                                                <input type="text" name="port" value="21" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-sm-6">
                                                <label>Username</label>
                                                <input type="text" name="username" value="" class="form-control" />
                                            </div>
                                            <div class="form-group col-sm-6">
                                                <label>Password</label>
                                                <input type="text" name="password" value="" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-sm-12">
                                                <label>Remote path</label>
                                                <input type="text" name="path" value="" class="form-control" />
                                                <small class="help-block">ex: /httpdocs/folder/file.csv</small>
                                            </div>
                                        </div>

                                    </div>
                                    <div id="server" class="tab-pane fade">
                                        <div class="row">
                                            <div class="form-group col-sm-12">
                                                <label>Server path</label>
                                                <input type="text" name="server_path" value="" class="form-control" />
                                                <small class="help-block">ex: /home/folder/file.csv</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="clearfix" style="margin-bottom: 40px;"></div>

                                <h2>
                                    CSV Settings
                                    <small>Assign the columns of the CSV to each DB field</small>
                                </h2>

                                <div class="row">
                                    <div class="form-group col-sm-2 text-center">
                                        <label>Header</label>
                                        <input id="header" type="checkbox" name="header" value="1" checked />
                                    </div>
                                    <div class="form-group col-sm-3">
                                        <label>Delimiter</label>
                                        <input id="delimiter" type="text" name="delimiter" value="<?php echo $_export_csv_delimiter; ?>" class="form-control" />
                                    </div>
                                    <div class="form-group col-sm-3">
                                        <label>Enclosure</label>
                                        <input id="enclosure" type="text" name="enclosure" value="<?php echo str_replace('"', '&quot;', $_export_csv_enclosure); ?>" class="form-control" />
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Encoding</label>
                                        <select name="encoding" class="selectpicker form-control">
                                            <?php
                                            sort($_file_encoding_list, SORT_NATURAL);
                                            foreach ($_file_encoding_list as $encoding)
                                                echo '<option value="' . $encoding . '"' . ($_export_csv_encoding == $encoding ? ' selected="selected"' : '') . '>' . $encoding . '</option>';
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="clearfix" style="margin-bottom: 40px;"></div>

                                <h2>
                                    Mapping
                                    <small>Assign which columns of the DB will be on the CSV and name it's header</small>
                                </h2>

                                <table id="mapping_table" style="width: 100%;" class="table table-hover table-condensed table-striped export">
                                    <thead>
                                        <tr>
                                            <th colspan="2">DB field</th>
                                            <th>CSV header</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Message container -->
                                        <tr class="messageContainerRow" style="display: none">
                                            <td></td>
                                            <td>
                                                <div class="form-group">
                                                    <div id="messageContainer"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <button id="btn-maping-add" type="button" class="btn btn-sm btn-outline-primary">Add</button>

                                <div class="clearfix" style="margin-bottom: 40px;"></div>

                                <h2>
                                    Filter
                                    <small>Filter the data you want to export</small>
                                </h2>

                                <div class="row">
                                    <div class="form-group col-sm-12">
                                        <label>Rows to export</label>
                                        <input id="filter_row" type="text" name="filter_rows" class="form-control" />
                                        <small class="help-block">ex: 1,2,5-10,15,20-</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-sm-12">
                                        <table id="conditions_table" style="width: 100%;" class="table table-hover table-condensed table-striped empty">
                                            <thead>
                                                <tr>
                                                    <th colspan="2">Conditions</th>
                                                    <th>DB field</th>
                                                    <th></th>
                                                    <th>Value</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                        <button id="btn-conditions-add" type="button" class="btn btn-sm btn-outline-primary">Add</button>
                                    </div>
                                </div>

                            </div>

                            <div id="step3" class="tab-pane">

                                <h2>
                                    Export
                                    <small>Review if everything is ok and export</small>
                                </h2>

                                <div id="review" class="row">

                                    <div id="review-input" class="col-sm-6 clearfix">
                                        <div class="pull-left">
                                            <a href="#previewInput" data-toggle="modal" title="View input file" class="database">
                                                <img src="images/db.png">
                                            </a>
                                            <small class="input-size"></small>
                                        </div>
                                        <div class="clearfix">
                                            <h4></h4>
                                            <p class="review-input-cols"><span class="input-cols loading"></span> columns</p>
                                            <p class="review-input-rows"><span class="input-rows loading"></span> records</p>
                                        </div>
                                    </div>

                                    <div id="review-progress">
                                        <span class="review-text">EXPORT</span>
                                        <span class="triangle"></span>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-success" role="progressbar">
                                                <span class="sr-only"></span>
                                            </div>
                                            <div class="progress-label" style="display: none;"></div>
                                        </div>
                                    </div>

                                    <div id="review-output" class="col-sm-6 clearfix">
                                        <div class="pull-right">
                                            <a href="#previewOutput" data-toggle="modal" title="Preview data to be exported" class="file">
                                                <img src="images/file.png">
                                                <span>.CSV</span>
                                            </a>
                                            <small class="output-size">&nbsp;</small>
                                        </div>
                                        <div class="clearfix">
                                            <h4></h4>
                                            <p id="review-output-cols"><span class="output-cols loading"></span> columns</p>
                                            <p id="review-output-rows"><span class="output-rows loading"></span> records</p>
                                            <small></small>
                                        </div>
                                    </div>

                                </div>


                                <div id="report" class="row" style="display: none;">

                                    <div id="time-info" class="pull-left">
                                        <h4 id="time-elapsed">Time elapsed: <span>00:00:00</span></h4>
                                        <table class="log_table p0">
                                            <tbody>
                                                <tr class="date_begin">
                                                    <td>Date begin: </td>
                                                    <td></td>
                                                </tr>
                                                <tr class="date_end">
                                                    <td>Date end: </td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div id="results" class="pull-right text-right">

                                        <h4 class="total">Exported: <span id="results-rows"></span> / <span id="results-total-rows"></span></h4>
                                        <table class="pull-right">
                                            <tr class="success text-success">
                                                <td>SUCCESS:</td>
                                                <td>0</td>
                                            </tr>
                                            <tr class="error text-danger">
                                                <td>ERROR:</td>
                                                <td>0</td>
                                            </tr>
                                        </table>

                                    </div>

                                </div>

                                <div class="clearfix" style="margin-bottom: 40px;"></div>

                                <div id="btn-log-container" class="clearfix">
                                    <button id="btn-log" type="button" class="btn btn-outline-info" style="display: none;">View log</button>
                                </div>

                                <div id="log" style="display: none;">
                                    <h3 class="pull-left">Log</h3>
                                    <table id="result_table"></table>
                                </div>

                            </div>

                        </div>

                    </form>

                    <iframe id="downloadFrame" src=""></iframe>

                    <div id="btn-nav">
                        <button id="btn-back" type="button" class="btn btn-default prev-step" style="display: none;">Back</button>
                        <button id="btn-next" type="button" class="btn btn-default next-step pull-right">Next</button>
                        <button id="btn-run" type="button" class="btn btn-primary pull-right" style="display: none;">Export</button>
                    </div>

                </div>

            </div>    <!-- panel-body -->
        </div>    <!-- panel -->

        <?php } ?>

    </div>

    <!-- Modal -->
    <div id="previewInput" class="modal fade modal-fade-in-scale-up" aria-hidden="true" aria-labelledby="exampleModalTitle" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-full">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Preview input Database</h4>
                </div>
                <div class="modal-body">
                    <table id="previewInputTable" class="bs-table"></table>
                </div>
            </div>
        </div>
    </div>

    <div id="previewOutput" class="modal fade modal-fade-in-scale-up" aria-hidden="true" aria-labelledby="exampleModalTitle" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-full">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        Preview data to be exported
                    </h4>
                </div>
                <div class="modal-body">
                    <table id="previewOutputTable" class="bs-table"></table>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <script>
    // Global Config Vars
    var _export_csv_delimiter = '<?php echo addslashes($_export_csv_delimiter); ?>';
    var _export_csv_enclosure = '<?php echo addslashes($_export_csv_enclosure); ?>';
    var _export_csv_escape    = '<?php echo addslashes($_export_csv_escape); ?>';
    var _export_csv_encoding  = '<?php echo $_export_csv_encoding; ?>';
    </script>

    <!-- JavaScript Includes -->

    <!-- jQuery -->
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>-->
    <script src="vendor/jquery/jquery-1.12.4.min.js"></script>

    <!-- jQuery UI -->
    <script src="vendor/jquery-ui/jquery-ui.min.js"></script>

    <!-- Bootstrap -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Bootstrap Wizard -->
    <script src="vendor/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>

    <!-- Bootstrap Select -->
    <script src="vendor/bootstrap-select/bootstrap-select.min.js"></script>

    <!-- Bootstrap Table -->
    <script src="vendor/bootstrap-table/bootstrap-table.min.js"></script>

    <!-- iCheck -->
    <script src="vendor/icheck/icheck.min.js"></script>

    <!-- asProgress -->
    <script src="vendor/jquery-asProgress/jquery-asProgress.min.js"></script>

    <!-- Moment -->
    <script src="vendor/moment/moment.min.js"></script>

    <!-- jChronometer -->
    <script src="vendor/jchronometer/jchronometer.js"></script>

    <!-- Form Validations -->
    <script src="vendor/formvalidation/formValidation.min.js"></script>
    <script src="vendor/formvalidation/framework/bootstrap.js"></script>

    <!-- Our main JS file -->
    <script src="js/export.js"></script>

</body>

</html>