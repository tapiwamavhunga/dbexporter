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
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">

    <!-- iCheck -->
	<link rel="stylesheet" href="vendor/icheck/skins/flat/blue.css">

    <!-- Bootstrap Select -->
	<link rel="stylesheet" href="vendor/bootstrap-select/css/bootstrap-select.min.css">

    <!-- Form Validation -->
	<link rel="stylesheet" href="vendor/formvalidation/formValidation.min.css">

    <!-- Fine Uploader -->
	<link rel="stylesheet" href="vendor/fine-uploader/fine-uploader-new.css">

    <!-- Web Icons -->
	<link rel="stylesheet" href="css/web-icons.css">

    <!-- The main CSS file -->
	<link rel="stylesheet" href="css/styles.css">

    <script type="text/template" id="qq-simple-thumbnails-template">
        <div class="qq-uploader-selector qq-uploader">
            <div class="qq-upload-drop-area-selector qq-upload-drop-area" qq-hide-dropzone>
                <span class="qq-upload-drop-area-text-selector">
                    <i class="wb-upload"></i>
                    <br>
                    <span class="out">Drop file here</span>
                    <span class="in">Drop it</span>
                </span>
            </div>
            <span class="qq-drop-processing-selector qq-drop-processing">
                <span>Processing dropped files...</span>
                <span class="qq-drop-processing-spinner-selector qq-drop-processing-spinner"></span>
            </span>
            <ul class="qq-upload-list-selector qq-upload-list" aria-live="polite" aria-relevant="additions removals">
                <li>

                    <div class="row">

                        <div class="col-sm-5">
                            <div class="pull-left">
                                <!--<span class="qq-upload-spinner-selector qq-upload-spinner"></span>-->
                                <a href="#previewInput" data-toggle="modal" title="View input file"><img class="qq-thumbnail-selector" qq-max-size="50" qq-server-scale></a>
                                <small class="preview-file-size"></small>
                            </div>
                            <div class="clearfix">
                                <h4 class="qq-upload-file-selector qq-upload-file"></h4>
                                <!--
                                <div class="btn-group pull-right" role="group">
                                    <button type="button" aria-label="Delete" class="btn btn-delete">
                                        <i class="icon wb-trash" aria-hidden="true"></i>
                                    </button>
                                </div>
                                -->
                                <p class="preview-file-cols"><span class="loading"></span> columns</p>
                                <p class="preview-file-rows"><span class="loading"></span> records</p>
                            </div>
                        </div>

                        <div class="col-sm-7">

                            <div class="btn-group" role="group">
                                <button type="button" aria-label="Cancel" class="btn qq-btn qq-upload-cancel-selector qq-upload-cancel">
                                    <i class="icon wb-close" aria-hidden="true"></i>
                                </button>
                                <button type="button" aria-label="Retry" class="btn qq-btn qq-upload-retry-selector qq-upload-retry">
                                    <i class="icon wb-replay" aria-hidden="true"></i>
                                </button>
                                <button type="button" aria-label="Delete" class="btn qq-btn qq-upload-delete-selector qq-upload-delete">
                                    <i class="icon wb-trash" aria-hidden="true"></i>
                                </button>
                                <button type="button" aria-label="Pause" class="btn qq-btn qq-upload-pause-selector qq-upload-pause">
                                    <i class="icon wb-pause" aria-hidden="true"></i>
                                </button>
                                <button type="button" aria-label="Continue" class="btn qq-btn qq-upload-continue-selector qq-upload-continue">
                                    <i class="icon wb-play" aria-hidden="true"></i>
                                </button>
                            </div>

                            <div class="progress progress qq-progress-bar-container-selector">
                                <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="progress-bar qq-progress-bar-selector">
                                    <span class="qq-upload-size-selector qq-upload-size"></span>
                                </div>
                            </div>

                        </div>

                    </div>

                    <span role="status" class="qq-upload-status-text-selector qq-upload-status-text"></span>
                </li>
            </ul>

            <div class="qq-upload-drop-area-text">
                <i class="wb-upload"></i>
                <br>
                Drag & drop file here
                <small>or</small>
            </div>

            <div class="qq-upload-button-selector qq-upload-button btn btn-outline-primary">
                <div>Browse files</div>
            </div>

            <dialog class="qq-alert-dialog-selector">
                <div class="qq-dialog-message-selector"></div>
                <div class="qq-dialog-buttons">
                    <button type="button" class="qq-cancel-button-selector">Close</button>
                </div>
            </dialog>

            <dialog class="qq-confirm-dialog-selector">
                <div class="qq-dialog-message-selector"></div>
                <div class="qq-dialog-buttons">
                    <button type="button" class="qq-cancel-button-selector">No</button>
                    <button type="button" class="qq-ok-button-selector">Yes</button>
                </div>
            </dialog>

            <dialog class="qq-prompt-dialog-selector">
                <div class="qq-dialog-message-selector"></div>
                <input type="text">
                <div class="qq-dialog-buttons">
                    <button type="button" class="qq-cancel-button-selector">Cancel</button>
                    <button type="button" class="qq-ok-button-selector">Ok</button>
                </div>
            </dialog>
        </div>
    </script>

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

        <h1>Import</h1>

        <div class="panel clearfix">
            <div class="panel-body">

                <div id="preview">
                    <a id="previewInputButton" href="#previewInput" title="View input file"><i class="icon wb-file"></i></a>
                    <i class="icon wb-arrow-right"></i>
                    <a id="previewOutputButton" href="#previewOutput" title="Preview data to be imported"><i class="icon wb-table"></i></a>
                </div>

                <div id="wizard">

                    <div class="wizard-inner">

                        <ul class="nav nav-pills" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#step1" data-toggle="tab" aria-controls="step1" role="tab" title="File">
                                    <span class="round-tab">
                                        <i class="icon wb-file"></i>
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
                                <a href="#step3" data-toggle="tab" aria-controls="step3" role="tab" title="Import">
                                    <span class="round-tab">
                                        <i class="icon wb-check"></i>
                                    </span>
                                </a>
                            </li>
                        </ul>

                    </div>

                    <form id="cie-form" role="form">
                        <input id="file_source" type="hidden" name="file_source" value="desktop" />

                        <div class="tab-content">

                            <div id="step1" class="tab-pane fade in active">

                                <h2>
                                    File to import
                                    <small>Choose the location of the file to import</small>
                                </h2>

                                <ul class="nav nav-tabs">
                                    <li class="active"><a data-toggle="tab" href="#desktop" rel="Desktop">Desktop</a></li>
                                    <li><a data-toggle="tab" href="#url" rel="Url">URL</a></li>
                                    <li><a data-toggle="tab" href="#ftp" rel="Ftp">FTP</a></li>
                                    <li><a data-toggle="tab" href="#server" rel="Server">Server</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div id="desktop" class="tab-pane fade in active">
                                        <div id="uploader"></div>
                                        <div class="form-group" style="margin: 0;">
                                            <input id="uuid" type="hidden" name="uuid" />
                                            <input id="file_desktop" type="hidden" name="file_desktop" value="" />
                                        </div>
                                    </div>
                                    <div id="url" class="tab-pane fade">
                                        <div class="form-group">
                                            <input id="file_url" type="hidden" name="file_url" value="" />
                                            <label>URL</label>
                                            <input type="text" name="url" class="form-control" value="" />
                                            <small class="help-block">ex: http://www.mydomain.com/downloads/file.csv</small>
                                        </div>
                                    </div>
                                    <div id="ftp" class="tab-pane fade">
                                        <div class="row">
                                            <div class="form-group col-sm-6">
                                                <input id="file_ftp" type="hidden" name="file_ftp" value="" />
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
                                                <label>Remote Path</label>
                                                <input type="text" name="path" value="" class="form-control" />
                                                <small class="help-block">ex: /httpdocs/folder/file.csv</small>
                                            </div>
                                        </div>

                                    </div>
                                    <div id="server" class="tab-pane fade">
                                        <div class="row">
                                            <div class="form-group col-sm-12">
                                                <input id="file_server" type="hidden" name="file_server" value="" />
                                                <label>Server Path</label>
                                                <input type="text" name="server_path" value="" class="form-control" />
                                                <small class="help-block">ex: /home/folder/file.csv</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="preview-file" class="well overflow-hidden" style="display: none;">

                                    <div class="row">

                                        <div class="col-md-5">
                                            <div class="pull-left" style="margin-right: 15px;">
                                                <a href="#previewInput" data-toggle="modal" title="View input file" class="file">
                                                    <img src="images/file.png">
                                                    <span>.CSV</span>
                                                </a><small class="input-size"></small>
                                            </div>
                                            <div class="clearfix">
                                                <h4 class="input-title"></h4>
                                                <p class="preview-file-cols"><span class="input-cols loading"></span> columns</p>
                                                <p class="preview-file-rows"><span class="input-rows loading"></span> records</p>
                                            </div>
                                            <button type="button" aria-label="Delete" class="btn btn-delete">
                                                <i class="icon wb-trash" aria-hidden="true"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-7">
                                            <div id="preview-file-progress">
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-success" role="progressbar">
                                                        <span class="progress-label"></span>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div id="csv" class="col-md-7">

                                            <button type="button" aria-label="Delete" class="btn btn-delete">
                                                <i class="icon wb-trash" aria-hidden="true"></i>
                                            </button>

                                            <div class="row">
                                                <div class="form-group col-sm-2 text-center">
                                                    <label>Header</label>
                                                    <input id="csv-header" type="checkbox" name="header" value="1" checked />
                                                </div>
                                                <div class="form-group col-sm-3">
                                                    <label>Delimiter</label>
                                                    <input id="delimiter" type="text" name="delimiter" value="<?php if ($_import_csv_settings_method == 'manual') echo $_import_csv_delimiter; ?>" class="form-control" />
                                                </div>
                                                <div class="form-group col-sm-3">
                                                    <label>Enclosure</label>
                                                    <input id="enclosure" type="text" name="enclosure" value="<?php if ($_import_csv_settings_method == 'manual') echo $_import_csv_enclosure; ?>" class="form-control" />
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Encoding</label>
                                                    <select id="encoding" name="encoding" class="selectpicker form-control">
                                                        <option value=""></option>
                                                        <?php
                                                        sort($_file_encoding_list, SORT_NATURAL);
                                                        foreach($_file_encoding_list as $encoding)
                                                            echo '<option value="' . $encoding . '"' . (($_import_csv_settings_method=='manual' and $_import_csv_encoding == $encoding) ? ' selected="selected"' : '') . '>' . $encoding . '</option>';
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <div class="alert alert-warning" style="display: none; margin-top: 30px;">
                                    <strong>Warning!</strong> Indicates a warning that might need attention.
                                </div>

                            </div>

                            <div id="step2" class="tab-pane">

                                <?php if (count($_dbs) == 1 and $_hide_only_one_database) { ?>
                                <input id="db" type="hidden" name="db" value="<?php echo $first_database_key; ?>">
                                <?php } ?>

                                <?php if (count($_dbs) == 1 and count($_tables) == 1 and $_hide_only_one_table) { ?>
                                <input id="table" type="hidden" name="table" value="<?php echo key($_tables); ?>">
                                <?php } ?>

                                <?php if (count($_dbs) > 1 or !$_hide_only_one_database or count($_tables) > 1 or !$_hide_only_one_table) { ?>
                                <h2>
                                    Table
                                    <small>Select the database table where you want to import the data</small>
                                </h2>

                                <div class="row">

                                    <?php if (count($_dbs) > 1 or !$_hide_only_one_database) { ?>
                                    <div class="form-group col-sm-6">
                                        <label>DB</label>
                                        <select id="db" name="db" class="selectpicker btn-default">
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
                                <?php } ?>

                                <h2>
                                    Mapping
                                    <small>Assign the columns of the CSV to each DB field</small>
                                </h2>

                                <table id="mapping_table" style="width: 100%;" class="table table-hover table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th>DB</th>
                                            <th>CSV</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Message container -->
                                        <tr class="messageContainerRow">
                                            <td></td>
                                            <td>
                                                <div class="form-group">
                                                    <div id="messageContainer"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="clearfix" style="margin-bottom: 40px;"></div>

                                <h2>
                                    Filter
                                    <small>Filter the data you want to import</small>
                                </h2>

                                <div class="row">
                                    <div class="form-group col-sm-12">
                                        <label>Rows to import</label>
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
                                                    <th>CSV column</th>
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

                                <div class="clearfix" style="margin-bottom: 40px;"></div>

                                <h2>
                                    Duplicates
                                    <small>Choose how to handle duplicates</small>
                                </h2>

                                <div class="row">

                                    <div class="form-group col-sm-6">
                                        <label>Field that defines a duplicate</label>
                                        <select id="duplicates" name="duplicates" class="selectpicker form-control">
                                            <option></option>
                                        </select>
                                    </div>

                                </div>

                                <div id="duplicates_rest_actions" class="row" style="display: none;">

                                    <div class="form-group col-sm-6">
                                        <label>Actions for the duplicates</label>
                                        <div class="radio-block"><input type="radio" name="actions_duplicates" value="skip" />&nbsp;Skip record</div>
                                        <div class="radio-block"><input type="radio" name="actions_duplicates" value="insert" />&nbsp;Insert as a new record</div>
                                        <div class="radio-block"><input type="radio" name="actions_duplicates" value="update" checked />&nbsp;Update</div>
                                        <div class="radio-block"><input type="radio" name="actions_duplicates" value="delete" />&nbsp;Delete</div>
                                    </div>

                                    <div class="form-group col-sm-6">
                                        <label>Actions for the rest</label>
                                        <div class="checkbox-block"><input type="checkbox" name="actions_rest_create" value="1" checked /> Create new records from data newly present in your file<br></div>
                                        <div class="checkbox-block"><input type="checkbox" name="actions_rest_delete" value="1" /> Delete records that are no longer present in your file</div>
                                    </div>

                                </div>


                            </div>

                            <div id="step3" class="tab-pane">

                                <h2>
                                    Import
                                    <small>Review if everything is ok and import</small>
                                </h2>

                                <div id="review" class="row">

                                    <div id="review-input" class="col-sm-6 clearfix">
                                        <div class="pull-left">
                                            <a href="#previewInput" data-toggle="modal" title="View input file" class="file">
                                                <img src="images/file.png">
                                                <span>.CSV</span>
                                            </a>
                                            <small class="input-size"></small>
                                        </div>
                                        <div class="clearfix">
                                            <h4 class="input-title"></h4>
                                            <p class="review-input-cols"><span class="input-cols loading"></span> columns</p>
                                            <p class="review-input-rows"><span class="input-rows loading"></span> records</p>
                                        </div>
                                    </div>

                                    <div id="review-progress">
                                        <span class="review-text">IMPORT</span>
                                        <span class="triangle"></span>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-success" role="progressbar">
                                                <span class="sr-only"></span>
                                            </div>
                                            <div class="progress-label" style="display: none;"></div>
                                        </div>
                                    </div>

                                    <div id="review-output" class="col-sm-6 clearfix">
                                        <a href="#previewOutput" data-toggle="modal" title="Preview data to be imported"><img src="images/db.png"></a>
                                        <div class="clearfix">
                                            <h4><?php $current_table = current($_tables); echo $current_table['label']; ?></h4>
                                            <p id="review-output-cols"><span class="output-cols loading"></span> columns</p>
                                            <p id="review-output-rows"><span class="output-rows loading"></span> records</p>
                                            <small></small>
                                        </div>
                                    </div>

                                </div>


                                <div id="report" style="display: none;">

                                    <div id="time-info" class="pull-left">
                                        <h4 id="time-elapsed">Time elapsed: <span>00:00:00</span></h4>
                                        <div class="date_begin"><span>Date begin: </span><span></span></div>
                                        <div class="date_end"><span>Date end: </span><span></span></div>
                                    </div>

                                    <div id="results" class="pull-right text-right">

                                        <h4 class="total">Imported: <span><span id="results-rows"></span> / <span id="results-total-rows"></span></span></h4>
                                        <table class="pull-right">
                                            <tr class="skipped text-info">
                                                <td>SKIPED: </td>
                                                <td>0</td>
                                            </tr>
                                            <tr class="inserted text-success">
                                                <td>INSERTED: </td>
                                                <td>0</td>
                                            </tr>
                                            <tr class="updated text-primary">
                                                <td>UPDATED: </td>
                                                <td>0</td>
                                            </tr>
                                            <tr class="deleted text-warning">
                                                <td>DELETED: </td>
                                                <td>0</td>
                                            </tr>
                                            <tr class="error text-danger">
                                                <td>ERROR: </td>
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

                    <div id="btn-nav">
                        <button id="btn-back" type="button" class="btn btn-default prev-step" style="display: none;">Back</button>
                        <button id="btn-next" type="button" class="btn btn-default next-step pull-right">Next</button>
                        <button id="btn-run" type="button" class="btn btn-primary pull-right" style="display: none;">Import</button>
                    </div>

                </div>

            </div>  <!-- panel-body -->
        </div>  <!-- panel -->

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
                    <h4 class="modal-title">Preview input file</h4>
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
                        Preview data to be imported
                        <small class="d-block">This doesn't show the duplicates actions</small>
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
    var _import_csv_delimiter = '<?php echo addslashes($_import_csv_delimiter); ?>';
    var _import_csv_enclosure = '<?php echo addslashes($_import_csv_enclosure); ?>';
    var _import_csv_escape    = '<?php echo addslashes($_import_csv_escape); ?>';
    var _import_csv_encoding  = '<?php echo $_import_csv_encoding; ?>';
    </script>

    <!-- JavaScript Includes -->

    <!-- jQuery -->
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>-->
    <script src="vendor/jquery/jquery-1.12.4.min.js"></script>

    <!-- jQuery UI -->
    <script src="vendor/jquery-ui/jquery-ui.min.js"></script>

    <!-- Underscore -->
    <script src="vendor/underscore/underscore-min.js"></script>

    <!-- Bootstrap -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- iCheck -->
    <script src="vendor/icheck/icheck.min.js"></script>

    <!-- Bootstrap Wizard -->
    <script src="vendor/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>

    <!-- Bootstrap Select -->
    <script src="vendor/bootstrap-select/bootstrap-select.min.js"></script>

    <!-- Bootstrap Table -->
    <script src="vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="vendor/bootstrap-table/extensions/cookie/bootstrap-table-cookie.min.js"></script>

    <!-- asProgress -->
    <script src="vendor/jquery-asProgress/jquery-asProgress.min.js"></script>

    <!-- Moment -->
    <script src="vendor/moment/moment.min.js"></script>

    <!-- jChronometer -->
    <script src="vendor/jchronometer/jchronometer.js"></script>

    <!-- Fine Uploader -->
    <script src="vendor/fine-uploader/fine-uploader.min.js"></script>

    <!-- Form Validations -->
    <script src="vendor/formvalidation/formValidation.min.js"></script>
    <script src="vendor/formvalidation/framework/bootstrap.js"></script>

    <!-- Our main JS file -->
    <script src="js/import.js"></script>

</body>

</html>