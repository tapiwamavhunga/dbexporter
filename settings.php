<?php

// Initialization
include_once('includes/load.php');

if (!isset($_databases))
    $_databases = array();

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

    <!-- Toggles -->
	<link rel="stylesheet" href="vendor/toggles/css/toggles.css">
	<link rel="stylesheet" href="vendor/toggles/css/themes/toggles-light.css">

    <!-- Bootstrap Select -->
	<link rel="stylesheet" href="vendor/bootstrap-select/css/bootstrap-select.min.css">

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

        <h1>Settings</h1>

        <div class="panel clearfix">
            <div class="panel-body">

                <form id="cie-form" role="form">

                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#databases_tables">Databases</a></li>
                        <li><a data-toggle="tab" href="#import">Import</a></li>
                        <li><a data-toggle="tab" href="#export">Export</a></li>
                        <li><a data-toggle="tab" href="#info">Info</a></li>
                    </ul>

                    <div class="tab-content">

                        <div id="databases_tables" class="tab-pane fade in active">

                            <h2>
                                Databases
                                <small>Define the settings of the databases</small>
                            </h2>

                            <div id="databases_template">
                                <div class="row">
                                    <div class="col-md-6">

                                        <div id="databases_container">
                                            <label>Databases</label>
                                            <select id="databases" name="databases" class="selectpicker" multiple="multiple">
                                                <?php
                                                foreach ($_databases as $key => $db)
                                                    echo '<option value="' . $key . '"' . ((isset($db['active']) and $db['active'] === false) ? '' : ' selected="selected"')  . '>' . ($db['label'] != '' ? $db['label'] : $db['name']) . '</option>';
                                                ?>
                                            </select>
                                            <div id="databases_msg" class="selectpicker_msg" data-msg-1="No database configured, please add one."></div>
                                            <div id="database_disabled"></div>
                                        </div>

                                        <button id="btn-databases-add" type="button" class="btn btn-sm btn-outline-primary">Add</button>
                                        <button id="btn-databases-edit" type="button" class="btn btn-sm btn-outline-primary" style="display: none;">Edit</button>
                                        <button id="btn-databases-delete" type="button" class="btn btn-sm btn-outline-primary" style="display: none;">Delete</button>

                                        <div id="confirm-delete" class="modal modal-danger fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <div class="modal-title">
                                                            DELETE DATABASE
                                                        </div>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you don't want to handle with this database no more?
                                                        <br><br>
                                                        <small>The database will NOT be erased! You just don't import or export on it.</small>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                        <button id="btn-databases-confirm-delete" type="button" class="btn btn-danger btn-ok">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-6">
                                        <div id="database_info" style="display: none;">
                                            <div class="form-group">
                                                <label>Label</label>
                                                <input type="text" id="db_label" name="db_label" value="" class="form-control" />
                                                <small class="help-block">A text that you can quickly identify the database (can be the same as the DB name)</small>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-sm-9">
                                                    <label>Server</label>
                                                    <input type="text" id="db_server" name="db_server" value="" class="form-control" />
                                                </div>
                                                <div class="form-group col-sm-3">
                                                    <label>Port</label>
                                                    <input type="text" id="db_port" name="db_port" value="3306" placeholder="" class="form-control" />
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Database Name</label>
                                                <input type="text" id="db_name" name="db_name" value="" class="form-control" />
                                            </div>
                                            <div class="form-group">
                                                <label>Username</label>
                                                <input type="text" id="db_username" name="db_username" value="" class="form-control" />
                                            </div>
                                            <div class="form-group">
                                                <label>Password</label>
                                                <input type="text" id="db_password" name="db_password" value="" class="form-control" />
                                            </div>
                                            <button id="btn-databases-ok" type="button" class="btn btn-sm btn-outline-primary push-right">Ok</button>
                                            <button id="btn-databases-cancel" type="button" class="btn btn-sm btn-default push-right">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="clearfix" style="margin-bottom: 40px;"></div>

                            <h2>
                                Tables & columns
                                <small>Select which tables and corresponding columns you want to use</small>
                            </h2>

                            <div class="row">
                                <div id="tables_container" class="form-group col-sm-6">
                                    <label>Tables</label>
                                    <select  id="tables" name="tables" class="selectpicker" multiple="multiple"></select>
                                    <div id="tables_msg" class="selectpicker_msg" data-msg-1="Please select a database to see their tables." data-msg-2="No tables on this database."></div>
                                    <button id="btn-tables-select-all" type="button" class="btn btn-sm btn-outline-primary" style="display: none;">Select all</button>
                                    <button id="btn-tables-deselect-all" type="button" class="btn btn-sm btn-outline-primary" style="display: none;">Deselect all</button>
                                </div>

                                <div id="columns_container" class="form-group col-sm-6">
                                    <label>Columns</label>
                                    <select  id="columns" name="columns" class="selectpicker" multiple="multiple"></select>
                                    <div id="columns_msg" class="selectpicker_msg" data-msg-1="Please select a table to see their columns." data-msg-2="No tables on this database."></div>
                                    <button id="btn-columns-select-all" type="button" class="btn btn-sm btn-outline-primary" style="display: none;">Select all</button>
                                    <button id="btn-columns-deselect-all" type="button" class="btn btn-sm btn-outline-primary" style="display: none;">Deselect all</button>
                                </div>

                            </div>

                        </div>


                        <div id="import" class="tab-pane fade">

                            <h2>
                                Rows per batch
                                <small>Define how many rows you want to import by each time</small>
                            </h2>

                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <label>Method</label>
                                    <div class="toggle toggle-light<?php if ($_import_rows_per_batch_method == 'manual') echo ' inactive'; ?>" data-toggle-on="<?php if ($_import_rows_per_batch_method == 'manual') echo 'false'; else echo 'true' ?>" data-toggle-width="120" data-toggle-height="36"></div>
                                    <input name="import_rows_per_batch_method" type="checkbox" class="toggle-checkbox" style="display: none;"<?php if ($_import_rows_per_batch_method == 'auto') echo ' checked="checked"'; ?> />
                                    <small class="help-block">Auto - will determine the best value to improve speed</small>
                                </div>
                                <div id="import_rows_per_batch_manual" class="form-group col-sm-8"<?php if ($_import_rows_per_batch_method == 'auto') echo ' style="display: none;"'; ?>>
                                    <label>Numbers of rows</label>
                                    <input type="text" name="import_rows_per_batch" class="form-control" value="<?php echo $_import_rows_per_batch; ?>" />
                                </div>
                            </div>

                            <div class="clearfix" style="margin-bottom: 40px;"></div>

                            <h2>
                                CSV settings
                                <small>Define the default CSV settings</small>
                            </h2>

                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <label>Method</label>
                                    <div class="toggle toggle-light<?php if ($_import_csv_settings_method == 'manual') echo ' inactive'; ?>" data-toggle-on="<?php if ($_import_csv_settings_method == 'manual') echo 'false'; else echo 'true' ?>" data-toggle-width="120" data-toggle-height="36"></div>
                                    <input name="import_csv_settings_method" type="checkbox" class="toggle-checkbox" style="display: none;"<?php if ($_import_csv_settings_method == 'auto') echo ' checked="checked"'; ?> />
                                    <small class="help-block">Auto - will try to find the CSV settings by itself</small>
                                </div>
                                <div id="import_csv_settings_manual" class="col-sm-8"<?php if ($_import_csv_settings_method == 'auto') echo ' style="display: none;"'; ?> >
                                    <div class="row">
                                        <div class="form-group col-md-3">
                                            <label>Delimiter</label>
                                            <input type="text" name="import_csv_delimiter" class="form-control" value="<?php echo $_import_csv_delimiter; ?>" />
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Enclosure</label>
                                            <input type="text" name="import_csv_enclosure" class="form-control" value="<?php echo htmlentities($_import_csv_enclosure); ?>" />
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Escape</label>
                                            <input type="text" name="import_csv_escape" class="form-control" value="<?php echo $_import_csv_escape; ?>" />
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Encoding</label>
                                            <select name="import_csv_encoding" class="selectpicker form-control">
                                                <option value=""></option>
                                                <?php
                                                sort($_file_encoding_list, SORT_NATURAL);
                                                foreach ($_file_encoding_list as $encoding)
                                                    echo '<option value="' . $encoding . '"' . ($_import_csv_encoding == $encoding ? ' selected="selected"' : '') . '>' . $encoding . '</option>';
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <div id="export" class="tab-pane fade">

                            <h2>
                                Rows per batch
                                <small>Define how many rows you want to export by each time</small>
                            </h2>

                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <label>Method</label>
                                    <div class="toggle toggle-light<?php if ($_export_rows_per_batch_method == 'manual') echo ' inactive'; ?>" data-toggle-on="<?php if ($_export_rows_per_batch_method == 'manual') echo 'false'; else echo 'true' ?>" data-toggle-width="120" data-toggle-height="36"></div>
                                    <input name="export_rows_per_batch_method" type="checkbox" class="toggle-checkbox" style="display: none;"<?php if ($_export_rows_per_batch_method == 'auto') echo ' checked="checked"'; ?> />
                                    <small class="help-block">Auto - will determine the best value to improve speed</small>
                                </div>
                                <div id="export_rows_per_batch_manual" class="form-group col-sm-8"<?php if ($_export_rows_per_batch_method == 'auto') echo ' style="display: none;"'; ?>>
                                    <label>Numbers of rows</label>
                                    <input type="text" name="export_rows_per_batch" class="form-control" value="<?php echo $_export_rows_per_batch; ?>" />
                                </div>
                            </div>

                            <div class="clearfix" style="margin-bottom: 40px;"></div>

                            <h2>
                                CSV settings
                                <small>Define the default CSV settings</small>
                            </h2>

                            <div class="row">
                                <div id="csv_settings_manual" class="col-md-8">
                                    <div class="row">
                                        <div class="form-group col-sm-3">
                                            <label>Delimiter</label>
                                            <input type="text" name="export_csv_delimiter" class="form-control" value="<?php echo $_export_csv_delimiter; ?>" />
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label>Enclosure</label>
                                            <input type="text" name="export_csv_enclosure" class="form-control" value="<?php echo htmlentities($_export_csv_enclosure); ?>" />
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label>Escape</label>
                                            <input type="text" name="export_csv_escape" class="form-control" value="<?php echo $_export_csv_escape; ?>" />
                                        </div>
                                        <div class="form-group col-sm-3">
                                            <label>Encoding</label>
                                            <select name="export_csv_encoding" class="selectpicker form-control">
                                                <option value=""></option>
                                                <?php
                                                sort($_file_encoding_list, SORT_NATURAL);
                                                foreach ($_file_encoding_list as $encoding)
                                                    echo '<option value="' . $encoding . '"' . ($_export_csv_encoding == $encoding ? ' selected="selected"' : '') . '>' . $encoding . '</option>';
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <div id="info" class="tab-pane fade">
                            <table id="ct" class="table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>upload_max_filesize</td>
                                        <td><?php echo UPLOAD_MAX_FILESIZE . ' <small>(' . return_bytes(UPLOAD_MAX_FILESIZE) . ' bytes)</small>'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>max_file_uploads</td>
                                        <td><?php echo MAX_FILE_UPLOADS; ?></td>
                                    </tr>
                                    <tr>
                                        <td>post_max_size</td>
                                        <td><?php echo POST_MAX_SIZE . ' <small>(' . return_bytes(POST_MAX_SIZE) . ' bytes)</small>'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>memory_limit</td>
                                        <td><?php echo MEMORY_LIMIT . ' <small>(' . return_bytes(MEMORY_LIMIT) . ' bytes)</small>'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>max_execution_time</td>
                                        <td><?php echo MAX_EXECUTION_TIME . ' s'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>max_input_time</td>
                                        <td><?php echo MAX_INPUT_TIME . ' s'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>allow_url_fopen</td>
                                        <td><?php echo (ALLOW_URL_FOPEN ? 'Yes' : 'No'); ?></td>
                                    </tr>
                                    <tr>
                                        <td>curl</td>
                                        <td><?php echo (CURL_INIT ? 'Yes' : 'No'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                    </div>

                    <div id="alert-msg" class="alert alert-warning" style="display: none;">
                        <strong>Warning!</strong> <span>Indicates a warning that might need attention.</span>
                    </div>

                    <div id="modal-msg" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header label-success text-white">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <div class="modal-title">
                                        SUCCESS
                                    </div>
                                </div>
                                <div class="modal-body">
                                    Settings saved successfully!
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="btn-nav">
                        <button id="btn-save" type="button" class="btn btn-primary btn-info-full">Save</button>
                    </div>

                </form>

            </div>    <!-- panel-body -->
        </div>    <!-- panel -->

    </div>

    <script>
    var selected_databases = <?php if (empty($_databases)) echo '{}'; else echo json_encode($_databases); ?>;
    </script>

    <!-- JavaScript Includes -->

    <!-- jQuery -->
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>-->
    <script src="vendor/jquery/jquery-1.12.4.min.js"></script>

    <!-- Underscore -->
    <script src="vendor/underscore/underscore-min.js"></script>

    <!-- Bootstrap -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Bootstrap Select -->
    <script src="vendor/bootstrap-select/bootstrap-select.min.js"></script>

    <!-- Toggles -->
    <script src="vendor/toggles/toggles.min.js"></script>

    <!-- Our main JS file -->
    <script src="js/settings.js"></script>

</body>
</html>