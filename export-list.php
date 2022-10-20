<?php

// Initialization
include_once('includes/load.php');

?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>PHP export & EXPORT</title>
	<meta name="description" content="PHP export & Export">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Google web fonts -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500" rel="stylesheet">

    <!-- Bootstrap -->
	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.css">

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

        <h1>Export list</h1>

        <div class="panel clearfix">
            <div class="panel-body">

                <div id="toolbar" class="btn-group hidden-xs ng-scope" role="group">
                    <a href="export.php" title="New" class="btn btn-default" data-tooltip="true">
                        <i class="icon wb-plus" aria-hidden="true"></i>
                    </a>
                    <button type="button" title="Bulk Delete" class="bulk-delete btn btn-default" data-tooltip="true">
                        <i class="icon wb-trash" aria-hidden="true"></i>
                    </button>
                </div>

                <table id="exportsTable" class="bs-table">
                    <thead>
                        <tr>
                            <th data-field="state" data-checkbox="true" data-visible="false"></th>
                            <th data-field="id" data-sortable="true" data-width="55">ID</th>
                            <th data-field="db" data-sortable="true" data-visible="false">Database</th>
                            <th data-field="table" data-sortable="true">Table</th>
                            <th data-field="filename" data-sortable="true">File</th>
                            <th data-field="total_exported" class="total_exported" data-formatter="totalexportedFormatter" data-sortable="true" data-align="right" title="exported" data-width="160">Exported</th>
                            <th data-field="output_rows" class="output_rows" data-formatter="outputRowsFormatter" data-sortable="true" data-align="right">/ Total</th>
                            <th data-field="total_success" class="inserted" data-sortable="true" data-align="right" title="Success" data-width="75" data-visible="false">Success</th>
                            <th data-field="total_error" class="error" data-sortable="true" data-align="right" title="Error" data-width="75" data-visible="false">Error</th>
                            <th data-field="date_begin" data-sortable="true" data-width="150">Date</th>
                            <th data-field="date_end" data-sortable="true" data-width="150" data-visible="false">End</th>
                            <th data-field="time_elapsed" data-formatter="timeElapsedFormatter" data-sortable="true" data-width="150" data-align="right" data-visible="false">Time Elapsed</th>
                            <th data-field="status" data-sortable="true" data-width="100">Status</th>
                            <th data-field="actions" data-formatter="actionsFormatter" data-events="actionsEvents" data-width="110" data-halign="center" data-align="center" class="actions">Actions</th>
                        </tr>
                    </thead>
                </table>

            </div>    <!-- panel-body -->
        </div>    <!-- panel -->

    </div>

    <!-- Modal -->
    <div id="previewInput" class="modal fade modal-fade-in-scale-up" aria-hidden="true" aria-labelledby="exampleModalTitle" role="dialog" tabindex="-1">
        <div class="modal-dialog" style="width: auto;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Preview input file</h4>
                </div>
                <div class="modal-body">

                    <table id="previewInputTable"></table>

                </div>
            </div>
        </div>
    </div>

    <div id="settingsModal" class="modal fade modal-fade-in-scale-up" aria-hidden="true" aria-labelledby="Settings" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        Settings
                    </h4>
                </div>
                <div class="modal-body">

                    <div class="col-sm-6">
                        <table class="log_table">
                            <tbody>
                                <tr id="settings_db">
                                    <td>Database: </td>
                                    <td></td>
                                </tr>
                                <tr id="settings_table">
                                    <td>Table: </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-sm-6">
                        <table class="log_table">
                            <tbody>
                                <tr id="settings_file_source">
                                    <td>File name: </td>
                                    <td></td>
                                </tr>
                                <tr id="settings_file_desktop" style="display: none;">
                                    <td>File: </td>
                                    <td></td>
                                </tr>
                                <tr id="settings_url" style="display: none;">
                                    <td>Url: </td>
                                    <td></td>
                                </tr>

                                <tr id="settings_address_url" style="display: none;">
                                    <td>Address or Url: </td>
                                    <td></td>
                                </tr>
                                <tr id="settings_port" style="display: none;">
                                    <td>Port: </td>
                                    <td></td>
                                </tr>
                                <tr id="settings_username" style="display: none;">
                                    <td>Username: </td>
                                    <td></td>
                                </tr>
                                <tr id="settings_password" style="display: none;">
                                    <td>Password: </td>
                                    <td></td>
                                </tr>
                                <tr id="settings_path" style="display: none;">
                                    <td>Remote Path: </td>
                                    <td></td>
                                </tr>
                                <tr id="settings_server_path" style="display: none;">
                                    <td>Server Path: </td>
                                    <td></td>
                                </tr>

                                <tr id="settings_header" class="space">
                                    <td>Header: </td>
                                    <td></td>
                                </tr>
                                <tr id="settings_delimiter">
                                    <td>Delimiter: </td>
                                    <td></td>
                                </tr>
                                <tr id="settings_enclosure">
                                    <td>Enclosure: </td>
                                    <td></td>
                                </tr>
                                <tr id="settings_encoding">
                                    <td>Encoding: </td>
                                    <td></td>
                                </tr>
                                <tr id="settings_mapping" class="space">
                                    <td>Mapping: </td>
                                    <td>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>DB</th>
                                                    <th>CSV</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr id="settings_filter_row" class="space">
                                    <td>Rows to export: </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="modal-footer">
                    <!--<button type="button" class="btn btn-sm btn-outline-primary">New export with this settings</button>-->
                </div>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="modal fade modal-fade-in-scale-up" aria-hidden="true" aria-labelledby="exampleModalTitle" role="dialog" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        Delete
                    </h4>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default margin-0" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <!-- JavaScript Includes -->

    <!-- jQuery -->
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>-->
    <script src="vendor/jquery/jquery-1.12.4.min.js"></script>

    <!-- Bootstrap -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Bootstrap Table -->
    <script src="vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="vendor/bootstrap-table/extensions/cookie/bootstrap-table-cookie.min.js"></script>

    <!-- Moment -->
    <script src="vendor/moment/moment.min.js"></script>

    <!-- Our main JS file -->
    <script src="js/export-list.js"></script>

</body>

</html>