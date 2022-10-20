var first_time = true;
var mapping_select_boxes_options = '';
var conditions_tr_select_boxes_options = '';

var db_cols_names = [];
var db_cols_labels = [];

var input_header = [];
var input_data = [];
var input_settings = {
    'filename':     '',
    'cols':         0,
    'rows':         0,
    'size':         0,
    'header':       true,
    'delimiter':    _import_csv_delimiter,
    'enclosure':    _import_csv_enclosure,
    'encoding':     _import_csv_encoding
};
var input_file_position = 0;
var input_row_position = 0;
var input_retry = 0;

var mapping = [];
var reload_mapping = false;

var output_header = [];
var output_data = [];
var total_output_rows = 0;
var output_file_position = 0;
var output_row_position = 0;
var output_ajax;

var results = {
    'total_imported':   0,
    'total_skipped':    0,
    'total_inserted':   0,
    'total_updated':    0,
    'total_deleted':    0,
    'total_error':      0,
};
var results_class_colors = {
    'Skipped':  'text-info',
    'Inserted': 'text-success',
    'Updated':  'text-primary',
    'Deleted':  'text-warning',
    'Error':    'text-danger'
};
var import_id = 0;
var import_file_position = 0;
var import_row_position = 0;
var import_progress_ajaxcall;
var import_progress;
var import_retry = 0;

var date_begin = '';
var date_begin_format = 'YYYY-MM-DD HH:mm:ss.SSS';
var date_end = '';
var date_end_format = 'YYYY-MM-DD HH:mm:ss.SSS';
var time_elapsed = 0;

// Capitalize First Letter
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Basename of a string
function basename(str) {
    if (str.lastIndexOf('/') !== -1)
        var base = new String(str).substring(str.lastIndexOf('/') + 1);
    else if (str.lastIndexOf('\\') !== -1)
        var base = new String(str).substring(str.lastIndexOf('\\') + 1);
    return base;
}

// Convert bytes to other sizes
function formatBytes(bytes) {
   var sizes = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
   if (bytes == 0) return '0 bytes';
   var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
   return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
};

// Convert miliseconds to human format
function humanFormat(t, sep, msep) {
    if ('undefined' === typeof sep) {
        sep = ':';
    }
    var date = new Date(t);
    var msec = date.getMilliseconds();
    var sec = date.getSeconds();
    var min = date.getMinutes();
    var hr = date.getHours();
    if (hr < 10) {
        hr = "0" + hr;
    }
    if (min < 10) {
        min = "0" + min;
    }
    if (sec < 10) {
        sec = "0" + sec;
    }
    if (msec < 10) {
        msec = "00" + msec;
    } else if (msec < 100) {
        msec = "0" + msec;
    }
    return hr + sep + min + sep + sec + msep + '<small>' + msec + '</small>';
}

$(function() {

    //==================================================================================================
    //  CHECKBOXES AND RADIOBOXES
    //==================================================================================================

    $('input[type=radio], input[type=checkbox]').iCheck({
        checkboxClass: 'icheckbox_flat-blue',
        radioClass: 'iradio_flat-blue'
    });


    //==================================================================================================
    //  SELECTBOXES
    //==================================================================================================

    $('.selectpicker').selectpicker({
        windowPadding: [64, 30, 30, 30],
        hideDisabled: true,
        noneSelectedText: ''
    });


    //==================================================================================================
    //  TOOLTIPS
    //==================================================================================================

    $('.wizard-inner > .nav-pills > li a[title]').tooltip();
    $('#preview a[title]').tooltip();


    //==================================================================================================
    //  PREVIEW CLICK ACTIVATE MODAL
    //==================================================================================================

    $('#preview a').on("click", function(e) {
        e.preventDefault();
        var link = $(this).attr('href');
        if ($(this).hasClass('active'))
            $(link).modal();
    });


    //==================================================================================================
    //  ON SUB NAV CLICK (UNDER STEP 1) CHANGE THE FILE SOURCE
    //==================================================================================================
    $('#step1 .nav a').on('click', function() {
        $('#file_source').val($(this).attr('href').replace('#', ''));
        if ($('#file_' + $('#file_source').val()).val() != '')
            $('#preview-file').show();
        else
            $('#preview-file').hide();
    });


    //==================================================================================================
    //  PROGRESS BAR
    //==================================================================================================

    $('#preview-file .progress').asProgress({
        namespace: 'progress',
        bootstrap: true,
        min: 0,
        max: 100,
        goal: 100,
        speed: 1, // speed of 1/100
        easing: 'linear',
/*
        labelCallback(n) {
            const percentage = this.getPercentage(n);
            return `${percentage}%`;
        }
        onStart: function() {},
        onStop: function() {},
        onUpdate: function() {},
*/
        onFinish: function() {
            //$('#review-progress .triangle').addClass('success');
        }
        //onReset: function() {}
    });


    //==================================================================================================
    //  CHRONOMETER
    //==================================================================================================

    //var jPickZone = $('#pickzone');
    var chronometer = new Chronometer({
        precision: 10,
        ontimeupdate: function (t) {
            $('#time-elapsed span').html(humanFormat(t, ':', '.'));
        }
    });


    //==================================================================================================
    //  EVENTS LISTNERS
    //==================================================================================================

    $('#csv-header').on('ifChanged', function(event) {

        // Create the preview input file table
        var columns_obj = [];
        var columns_arr = [];
        for (var i = 0; i < input_header.length; i++) {
            columns_obj.push({'title': input_header[i]});
            columns_arr.push(input_header[i]);
        }

        var rows = parseInt($('#preview-file .preview-file-rows span').html());
        if ($(this).is(':checked')) {
            $('#preview-file .preview-file-rows span, #review-input .review-input-rows span').html(rows-1);
            input_data.shift();
        } else {
            $('#preview-file .preview-file-rows span, #review-input .review-input-rows span').html(rows + 1);
            input_data = _.union([columns_arr], input_data);
        }

        $('#previewInputTable').bootstrapTable('destroy').bootstrapTable({
            columns: columns_obj,
            showHeader: $(this).is(':checked'),
            data: input_data,
            search : true,
            pagination: true
        });

        getOutput(true);

    });

    $('#delimiter, #enclosure, #encoding').on('change', function() {
        input_settings.cols = 0;
        input_settings.rows = 0;
        input_file_position = 0;
        input_header = [];
        input_data = [];
        reload_mapping = true;
        getCsvSettings();
    });

    $('#db').on('change', function() {
        var db = $(this).val();

        if (!$('#table option[data-rel="' + db + '"]').length) {

            var ajaxcall = $.ajax({
                type: "POST",
                url: "?action=import_getTables",
                data: {
                    'db_id': db
                },
                dataType: 'json'
            }).done(function( out ) {

                if (out.status == 'success') {

                    var out_options = '';
                    for (i = 0; i < out.data.names.length; i++)
                        out_options += '<option value="' + out.data.ids[i] + '" data-rel="' + db + '">' + out.data.labels[i] + '</option>';

                    $('#table option').prop("disabled", true);
                    $('#table').append(out_options);
                    $('#table option[data-rel="' + db + '"]:eq(0)').prop('selected', true);
                    $('#table').selectpicker('refresh').trigger('change');

                    // Hide the other select boxes of other DB tables
                    //$('#mapping_table tbody tr:not(.mapping_' + $('#table').val() + ', .messageContainerRow)').hide().find('select').attr('disabled', 'disabled');

                }

            });

        } else {

            $('#table option').prop("disabled", false).not('[data-rel="' + db + '"]').prop("disabled", true);
            $('#table option[data-rel="' + db + '"]:eq(0)').prop('selected', true);
            $('#table').selectpicker('refresh').trigger('change');

        }

    });

    $('#filter_row').on('change', function() {
        getOutput(true);
    });


    $('#btn-conditions-add').click(function() {

        $('#conditions_table').removeClass('empty');

        if(!$('#conditions_table tbody > tr').length){
            $('#conditions_table tbody').append(conditions_tr_select_boxes_options);
        }else{
            $('#conditions_table tbody > tr:last').after(conditions_tr_select_boxes_options);
        }

        $('#conditions_table tbody > tr:last > td select, #conditions_table tbody > tr:last > td input').on('change', function() {
            getOutput(true);
        });

        $('#conditions_table tr:last select').selectpicker({
            windowPadding: [64, 30, 30, 30],
            noneSelectedText: ''
        });

        // On removing a condition
        $("#conditions_table tbody button.remove").click(function(event) {
            event.preventDefault();
            //var $row = $(this).parents('tr'),
                //$option = $row.find('[name="condition[]"]');
            //$('#cie-form').formValidation('removeField', $option);
            $(this).parent().parent().remove();
            if ($('#conditions_table tbody > tr').length == 0)
                $('#conditions_table').addClass('empty');
            getOutput(true);
        });
    });
    $("#conditions_table tbody").sortable({
        handle: ".icon.wb-menu",
        helper: function(e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function(index) {
                $(this).width($originals.eq(index).width())
            });
            return $helper;
        },
        update: function( event, ui ) {
            getOutput(true);
        }
    });


    $('#duplicates').on('change', function() {
        if ($(this).val() != '') {
            $('#duplicates_rest_actions').show();
        } else {
            $('#duplicates_rest_actions').hide();
        }
    });

    $('#btn-log').on('click', function() {
        $('#log').toggle();
        if ($(this).html() == 'VIEW LOG')
            $(this).html('HIDE LOG');
        else
            $(this).html('VIEW LOG');
    });


    //==================================================================================================
    //  FINE UPLOADER
    //==================================================================================================

    if ($('#uploader').length) {

        var uploader = new qq.FineUploader({
            debug: false,
            element: document.getElementById("uploader"),
            //autoUpload: false,
            template: 'qq-simple-thumbnails-template',
            multiple: false,
            maxConnections: 1,
            chunking: {
                enabled: true,
                partSize: 20097152,
                concurrent: {
                    enabled: true
                },
                success: {
                    endpoint: "includes/endpoint.php?done"
                }
            },
            request: {
                endpoint: 'includes/endpoint.php'
            },
            resume: {
                enabled: true
            },
            deleteFile: {
                enabled: true,
                endpoint: 'includes/endpoint.php'
            },
            retry: {
               enableAuto: false,
               showButton: true
            },
            thumbnails: {
                placeholders: {
                    //waitingPath: 'images/csv-512.png',
                    notAvailablePath: 'images/file.png'
                }
            },
            validation: {
                //allowedExtensions: ['jpeg', 'jpg', 'gif', 'png', 'csv'],
                itemLimit: 1
                //sizeLimit: 51200 // 50 kB = 50 * 1024 bytes
            },
            display: {
                fileSizeOnSubmit: true
            },
            text: {
                formatProgress: "{percent}%" // "{percent}% of {total_size}"
            },
            callbacks: {
                onValidate: function(data, buttonContainer) {
                    $('#preview-file .input-title, #review-input .input-title').html(data.name).attr('title', data.name).attr('href', data.name);
                    input_settings.filename = data.name;
                    input_settings.size = data.size;
                    $('.input-size').html(formatBytes(input_settings.size));

                    $('#uploader').hide();
                    $('#preview-file').show();

                    $('#preview-file .progress').attr('data-goal', data.size);

                    $('#uploader').addClass('validated');
                    $('.qq-upload-drop-area-selector, .qq-upload-drop-area-text, .qq-upload-button-selector').addClass('dni');
                },
                onValidateBatch: function(fileOrBlobDataArray, buttonContainer) {
                    //return false;
                },
                onProgress: function(id, name, uploadedBytes, totalBytes) {
                    $('#preview-file .progress .sr-only').html((uploadedBytes/totalBytes*100) + '%');
                    $('#preview-file .progress').asProgress('go', (uploadedBytes/totalBytes*100) + '%');
                },
                onTotalProgress: function(totalUploadedBytes, totalBytes) {
                },
                onDeleteComplete: function(id, xhr, isError) {
                    $('#preview-file .progress').asProgress('reset');
                    $('#uploader').removeClass('validated');
                    $('#uploader').removeClass('uploaded');
                    $('.qq-upload-drop-area-selector, .qq-upload-drop-area-text, .qq-upload-button-selector').removeClass('dni');
                    $('#uuid').val('');
                    $('#file_desktop').val('');
                    $('#cie-form').formValidation('resetField', $('#file_desktop'));
                },
                onCancel: function(id, xhr, isError) {
                    $('#preview-file .progress').asProgress('reset');
                    $('#uploader').removeClass('validated');
                    $('#uploader').removeClass('uploaded');
                    $('.qq-upload-drop-area-selector, .qq-upload-drop-area-text, .qq-upload-button-selector').removeClass('dni');
                    $('#uuid').val('');
                    $('#file_desktop').val('');
                    $('#cie-form').formValidation('resetField', $('#file_desktop'));
                },
                onComplete: function(id, name, response) {
                    if (response.success) {
                        $('#preview-file .progress').asProgress('finish');
                        $('#uuid').val(response.uuid);
                        $('#file_desktop').val(name);
                        $('#step1 .tab-pane input').prop('readonly', false);
                        $('#step1 .tab-pane.active input').prop('readonly', true)

                        $('#cie-form').formValidation('resetField', $('#file_desktop'));
                        getCsvSettings();
                    }
                },
                onAllComplete: function(succeeded, failed) {
                    $('#uploader').addClass('uploaded');
                },
                onError: function(id, name, errorReason) {
                    //console.log(errorReason);
                }
            }
        });

    }

    $('#preview-file button.btn-delete').on('click', function() {
        resetInput();
        reload_mapping = true;
        if ($('#file_source').val() == 'desktop') {
            if ($('#uploader .qq-upload-list li').hasClass('qq-in-progress'))
                $('#uploader .qq-upload-cancel').trigger('click');
            else
                $('#uploader .qq-upload-delete').trigger('click');
        }
        $('#step1 .tab-pane.active input').prop('readonly', false);
        $('#wizard .wizard-inner .nav > li').eq(2).addClass('disabled');
        $('#preview-file').hide().addClass('overflow-hidden');
        $('#uploader, #preview-file-progress').show();
        $('#csv').attr('style', '');
    });


    //==================================================================================================
    //  FORM VALIDATION
    //==================================================================================================

    $('#cie-form').formValidation({
        framework: 'bootstrap',
        /*
        icon: {
            valid: 'icon wb-check',
            invalid: 'icon wb-close',
            validating: 'icon wb-refresh'
        },
        */
        // This option will not ignore invisible fields which belong to inactive panels
        excluded: ':disabled',
        fields: {
            file_desktop: {
                validators: {
                    notEmpty: {
                        message: 'A file is required'
                    }
                }
            },
/*
            file_url: {
                validators: {
                    notEmpty: {
                        message: 'The file url is required'
                    }
                }
            },
*/
            url: {
                validators: {
                    notEmpty: {
                        message: 'The Url is required'
                    },
                    /*
                    regexp: {
                        regexp: /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/,
                        message: 'That\'s not a valid url, you need http or https on the beginning.'
                    },
                    */
                    uri: {
                        message: 'That\'s not a valid url'
                    }
                }
            },
/*
            file_ftp: {
                validators: {
                    notEmpty: {
                        message: 'The file ftp is required'
                    }
                }
            },
*/
            address_url: {
                validators: {
                    notEmpty: {
                        message: 'Address or Url is required'
                    }
                }
            },
            port: {
                validators: {
                    notEmpty: {
                        message: 'Port is required'
                    }
                }
            },
            username: {
                validators: {
                    notEmpty: {
                        message: 'Username is required'
                    }
                }
            },
            password: {
                validators: {
                    notEmpty: {
                        message: 'Password is required'
                    }
                }
            },
            path: {
                validators: {
                    notEmpty: {
                        message: 'Path is required'
                    }
                }
            },
            delimiter: {
                validators: {
                    notEmpty: {
                        message: 'this is required'
                    }
                }
            },
            enclosure: {
                validators: {
                    notEmpty: {
                        message: 'this is required'
                    }
                }
            },
            /*
            encoding: {
                validators: {
                    notEmpty: {
                        message: 'The Encoding is required'
                    }
                }
            },
            */
            'mapping[]': {
                // The children's full name are inputs with class .childFullName
                //selector: '#mapping_table .selectpicker',
                // The field is placed inside .selectContainer div instead of .form-group
                //row: '.selectContainer',
                //row: '.bootstrap-select',
                err: '#messageContainer',
                validators: {
                    //notEmpty: {
                        //message: 'The Mapping is required'
                    //}
                    callback: {
                        callback: function(value, validator, $field) {

                            var $mapping         = $('#mapping_table tr.mapping_' + $('#table').val() + ' select'),
                                numMapping        = $mapping.length,
                                notEmptyCount    = 0,
                                obj              = {},
                                duplicateRemoved = [];

                            for (var i = 0; i < numMapping; i++) {
                                var v = $mapping.eq(i).val();
                                if (v !== '') {
                                    obj[v] = 0;
                                    notEmptyCount++;
                                }
                            }

                            for (i in obj) {
                                duplicateRemoved.push(obj[i]);
                            }

                            if (duplicateRemoved.length === 0) {
                                return {
                                    valid: false,
                                    message: 'You must fill at least one Mapping'
                                };
                            }

                            validator.updateStatus('mapping[]', validator.STATUS_VALID, 'callback');
                            return true;
                        }
                    }
                }
            }

        }
    });


    //==================================================================================================
    //                                          WIZARD
    //==================================================================================================

    $('#wizard').bootstrapWizard({
        previousSelector: '#btn-back',
        nextSelector: '#btn-next',
        onTabClick: function(activeTab, navigation, currentIndex, clickedIndex, clickedTab) {
            return changeTab(currentIndex, clickedIndex);
        },
        onNext: function(activeTab, navigation, nextIndex) {
            return changeTab(nextIndex - 1, nextIndex);
        },
        onPrevious: function(activeTab, navigation, previousIndex) {
            return changeTab(previousIndex + 1, previousIndex);
        },
        onTabShow: function(activeTab, navigation, currentIndex) {
            // Update the label of Next button when we are at the last tab
            var numTabs = $('#wizard > .wizard-inner > .nav-pills > li').length;

            for (var i = numTabs - 1; i >= currentIndex; i--)
                $('#wizard .wizard-inner .nav > li').eq(i).removeClass('validated');

            if (currentIndex == 0) {
                $('#btn-back, #btn-run').hide();
                $('#btn-next').show();
            } else if (currentIndex == numTabs-1) {
                $('#btn-next').hide();
                $('#btn-back, #btn-run').show();
            } else {
                $('#btn-run').hide();
                $('#btn-back, #btn-next').show();
            }
        }
    });

    function validateTab(index) {
        var fv = $('#cie-form').data('formValidation'), // FormValidation instance

        // The current tab
        $tab = $('#cie-form > .tab-content > .tab-pane').eq(index);

        if (index == 0) {
            $tab = $tab.find('.tab-pane.active');

            if ($('#file_' + $('#file_source').val()).val() != '')
                $tab = $tab.add('#csv');

        }

        // Validate the container
        fv.validateContainer($tab);

        var isValidStep = fv.isValidContainer($tab);
        if (isValidStep === false || isValidStep === null) {
            if (index == 0)
                $('#wizard .wizard-inner .nav > li').eq(2).addClass('disabled');
            // Do not jump to the target tab
            return false;
        }

        if (index == 0 && $('#file_' + $('#file_source').val()).val() != '' || index > 0) {
            $('#wizard .wizard-inner .nav > li').eq(index).addClass('validated');
            $('#wizard .wizard-inner .nav > li').eq(2).removeClass('disabled');
        }

        return true;
    }

    function changeTab(currentIndex, nextIndex) {

        // If import started then don't change tab
        if (date_begin)
            return false;

        if ($('#wizard .wizard-inner .nav > li').eq(nextIndex).hasClass('disabled'))
            return false;

        // Disable tab 3 if tab 2 has errors and change tab going to tab 1
        if (currentIndex == 1 && nextIndex == 0 && !validateTab(1))
            $('#wizard .wizard-inner .nav > li').eq(2).addClass('disabled');

        // Validate Tab
        var all_tabs_valid = true;
        if (currentIndex < nextIndex) {
            for (i = 0; i < nextIndex; i++) {
                all_tabs_valid = all_tabs_valid && validateTab(i);
            }
            if (!all_tabs_valid)
                return false;
        }

        // If its going to the step 2 then get the file and see if it is ok
        if (currentIndex == 0 && nextIndex == 1 && $('#file_' + $('#file_source').val()).val() == '') {

            // if (changed) {

                resetInput();

                $('#preview-file .progress').asProgress('reset').parent().show().find('.progress').asProgress('start');

                if ($('#file_source').val() == 'url')
                    var file = $('#url input[name=url]').val();
                if ($('#file_source').val() == 'ftp')
                    var file = $('#ftp input[name=path]').val();
                if ($('#file_source').val() == 'server')
                    var file = $('#server input[name=server_path]').val();
                $('#preview-file .input-title').html(basename(file)).attr('title', file);
                $('#preview-file').show();

                //==================================================================================================
                //  GET FILE FROM (URL, FTP, SERVER)
                //==================================================================================================

                var ajax_action = 'import_getFileFrom' + capitalizeFirstLetter($('#file_source').val());

                // Remove the Syncronous Warning
                //$.ajaxPrefilter(function( options, originalOptions, jqXHR ) { options.async = true; });
                var ajaxcall = $.ajax({
                    type: "POST",
                    url: "?action=" + ajax_action,
                    data: $('#cie-form').serialize(),
                    //contentType: "application/json; charset=utf-8",
                    dataType: 'json'
                    //async: false
                }).done(function( out ) {
                    if (out.status == 'success') {

                        $('#step1 .tab-pane input').prop('readonly', false);
                        $('#step1 .tab-pane.active input').prop('readonly', true)

                        $('#preview-file-progress .progress').asProgress('finish');

                        // Global vars
                        $('#file_desktop, #file_url, #file_ftp, #file_server').val('');
                        $('#uploader .qq-upload-delete').trigger('click');
                        $('#file_' + $('#file_source').val()).val(out.data.filename);

                        // Review Import (Step 3)
                        //$('#review-input-file img').attr('src', out.data.file);

                        $('#review-input .input-title').html(out.data.filename).attr('title', out.data.filename).attr('href', out.data.filename);
                        input_settings.filename = out.data.filename;
                        input_settings.size = parseInt(out.data.filesize);
                        $('.input-size').html(formatBytes(input_settings.size));

                        getCsvSettings();

                    } else {

                        // If it has a warning
                        if (out.message != '') {
                            $('#preview-file button.btn-delete').trigger('click');
                            $('#step1 .alert').addClass('alert-danger').html('<strong>Error!</strong> ' + out.message).show();
                        }

                    }
                });

                return false;

            //} else {


            //}

        }

        return true;
    }


    function resetInput() {

        mapping_select_boxes_options = '';

        input_header = [];
        input_data = [];

        input_settings = {
            'filename':     '',
            'cols':         0,
            'rows':         0,
            'size':         0,
            'header':       true,
            'delimiter':    _import_csv_delimiter,
            'enclosure':    _import_csv_enclosure,
            'encoding':     _import_csv_encoding
        };
        input_file_position = 0;
        $('#file_desktop, #file_url, #file_ftp, #file_server').val('');

        $('#delimiter').val(_import_csv_delimiter);
        $('#enclosure').val(_import_csv_enclosure);
        $('#encoding').val(_import_csv_encoding);

        $('.input-rows').addClass('loading').html('');
        $('.input-cols').addClass('loading').html('');

        var $select = $('#mapping_table tr.mapping_' + $('#table').val()).find('select');
        $('#cie-form').formValidation('removeField', $select);
        $('#mapping_table tbody tr').not('.messageContainerRow').remove();
    }

    function previewInput() {

        $('#delimiter').val(input_settings.delimiter);
        $('#enclosure').val(input_settings.enclosure);
        $('#encoding').val(input_settings.encoding).selectpicker('refresh');

        $('#cie-form').formValidation('revalidateField', $('#delimiter, #enclosure'));

        $('.input-cols').removeClass('loading').html(input_settings.cols);

        $('#preview-file-progress').hide();
        $('#csv').animate({
            right: 0
        }, 500, function() {
            $('#preview-file').removeClass('overflow-hidden');
        });

    }


    function getCsvSettings() {

        $.ajax({
            type: "POST",
            url: "?action=import_getCsvSettings",
            data: $('#cie-form').serialize(),
            dataType: 'json'
        }).done(function( out ) {

            if (out.status == 'success') {

                input_settings.delimiter = out.data.delimiter;
                input_settings.enclosure = out.data.enclosure;
                //input_settings.escape = out.data.escape;
                input_settings.encoding = out.data.encoding;
                input_settings.cols = out.data.cols;

                previewInput();

                getInput();
            }

        });

    }


    //==================================================================================================
    //  GET INPUT
    //==================================================================================================

    function getInput() {

        $('#step1 .alert').hide();

        $.ajax({
            type: "POST",
            url: "?action=import_getInput&file_position=" + input_file_position + "&row_position=" + input_row_position + '&retry=' + input_retry,
            data: $('#cie-form').serialize(),
            dataType: 'json'
        }).done(function( out ) {

            if (out.status == 'success' || out.status == 'partial content') {

                input_retry = 0;

                input_file_position = parseInt(out.data.file_position);
                //input_row_position = parseInt(out.data.row_position);
                input_settings.rows += out.data.rows;
                input_data = input_data.concat(out.data.body);

                // Global Vars
                if (!input_header.length) {
                    input_header = out.data.header;

                    // Set the global var mapping_select_boxes_options
                    mapping_select_boxes_options = '<option value=""></option>';
                    for (var i = 0; i < out.data.header.length; i++)
                        mapping_select_boxes_options += '<option value="' + i + '">' + out.data.header[i] + '</option>';
                }
                $('#table').trigger('change');

                if (out.status == 'success') {

                    $('.input-rows').removeClass('loading').html(input_settings.rows);

                    // Create the preview input file table
                    var columns_obj = [];
                    for (var i = 0; i < input_header.length; i++)
                        columns_obj.push({'title': input_header[i]});

                    $('#previewInputTable').bootstrapTable('destroy').bootstrapTable({
                        columns:    columns_obj,
                        showHeader: out.data.heading,
                        data:       input_data,
                        search:     true,
                        pagination: true
                    });
                    $('#previewInputButton').addClass('active');


                    // Conditions
                    $("#conditions_table tbody").html('').parent().addClass('empty');
                    conditions_tr_select_boxes_options = '<tr>';
                    conditions_tr_select_boxes_options += '<td><i class="icon wb-menu"></i></td>';
                    conditions_tr_select_boxes_options += '<td><select name="conditions[0][]"><option value="AND">AND</option><option value="OR">OR</option></select></td>';
                    conditions_tr_select_boxes_options += '<td><select name="conditions[1][]" class="selectpicker form-control">' + mapping_select_boxes_options + '</select></td>';
                    conditions_tr_select_boxes_options += '<td><select name="conditions[2][]"><option value="=">=</option><option value="!=">!=</option><option value="&gt;">&gt;</option><option value="&lt;">&lt;</option><option value="&gt;=">&gt;=</option><option value="&lt;=">&lt;=</option><option value="CONTAINS">CONTAINS</option><option value="NOT CONTAINS">NOT CONTAINS</option></select></td>';
                    conditions_tr_select_boxes_options += '<td class="form-group"><input name="conditions[3][]" value="" class="form-control"></td>';
                    conditions_tr_select_boxes_options += '<td><button class="remove btn btn-sm btn-icon btn-flat btn-default" title="" data-tooltip="true" data-original-title="Remove"><i class="icon wb-close"></i></button></td>';
                    conditions_tr_select_boxes_options += '</tr>';

                } else {
                    getInput();
                }

                // If it has a warning
                if (out.warning_message != '') {
                    $('#step1 .alert').html('<strong>Warning!</strong> ' + out.warning_message).show();
                }

            }
        }).fail(function() {

            // Retry
            input_retry++;

            if (input_retry <= 3) {
                getInput();
            } else {
                $('#step1 .alert-warning').html('ERRORS FOUND - Try again with setting a manual rows per batch method.').show();
                $('.input-cols').removeClass('loading').html('-');
            }
        });

    }


    //==================================================================================================
    //  Update the select boxes items that are similar to the name of the DB columns
    //==================================================================================================

    function autoMapping() {
        $('#mapping_table tr.mapping_' + $('#table').val() + ' select').each(function(index) {
            // Auto select the option which has the same name has the DB column
            for (var i = 0; i < input_header.length; i++) {
                if (db_cols_labels[index].toLowerCase() == input_header[i].toLowerCase())
                    $(this).selectpicker('val', i).trigger('change');
            }
        })
    }


    //==================================================================================================
    //  DB TABLE SELECTBOX CHANGE
    //==================================================================================================

    $('#table').on('change', function() {

        if (!$('.mapping_' + $('#table').val()).length || reload_mapping) {

            mapping = [];
            reload_mapping = false;

            var ajaxcall = $.ajax({
                type: "POST",
                url: "?action=import_getColumns",
                data: {
                    'db_id':    $('#db').val(),
                    'table_id': $('#table').val()
                },
                dataType: 'json'
            }).done(function( out ) {

                if ($('#table option:selected').text() != '')
                    $('#review-output h4').html($('#table option:selected').text());

                if (out.status == 'success') {

                    // Global vars
                    db_cols_names = out.data.names;
                    db_cols_labels = out.data.labels;

                    var out_mapping = '';
                    for (i = 0; i < db_cols_names.length; i++) {
                        out_mapping += '<tr id="mapping_' + $('#table').val() + '_' + i + '" class="mapping_' + $('#table').val() + '">';
                        out_mapping += '<td>' + db_cols_labels[i] + '</td>';
                        out_mapping += '<td class="form-group"><select name="mapping[]" rel="mapping_' + $('#table').val() + '_' + i + '" class="selectpicker form-control"><option value=""></option></select></td>';
                        //out_mapping += '<td class="form-group"><select name="mapping[' + db_cols_names[i] + ']" rel="mapping_' + $('#table').val() + '_' + i + '" class="selectpicker form-control"><option value=""></option></select></td>';
                        out_mapping += '</tr>';
                    }

                    // Hide the other select boxes of other DB tables
                    $('#mapping_table tbody tr:not(.mapping_' + $('#table').val() + ', .messageContainerRow)').hide().find('select').attr('disabled', 'disabled');

                    // Remove old values if exist
                    $('#mapping_table tbody tr.mapping_' + $('#table').val()).remove();

                    // Add the new select boxes for the current DB
                    $('#mapping_table tbody .messageContainerRow').before(out_mapping);

                    // On Mapping change update the Selected DB Fields and updates the Preview Data to be Imported
                    $('#mapping_table tr.mapping_' + $('#table').val() + ' select').html(mapping_select_boxes_options).on('change', function() {

                        // updates the total and the DB fields that are selected
                        var index = $(this).attr('rel').substring($(this).attr('rel').lastIndexOf('_') + 1);

                        if ($(this).val() != '') {
                            mapping[index] = $(this).val();

                            if ($.inArray(db_cols_names[index], output_header) == -1)
                                output_header[index] = db_cols_names[index];
                        } else {
                            delete mapping[index];
                            delete output_header[index];
                        }

                        // Fills the duplicates options with only DB fields that are mapped
                        duplicates_options = '<option value=""></option>';
                        for (i = 0; i < db_cols_labels.length; i++) {
                            if (mapping[i] != undefined)
                                duplicates_options += '<option value="' + i + '"' + (i == $('#duplicates').val() && $('#duplicates').val() != '' ? 'selected="selected"' : '') + '>' + db_cols_labels[i] + '</option>';
                        }
                        $('#duplicates').html(duplicates_options);
                        $('#duplicates').selectpicker('refresh');

/*
                        // Activates or not the Preview Data To Be Imported button
                        var output_header_reseted = output_header.filter(function() {return true;}); // reset the indexes of the array, because there are undefined keys
                        if (output_header_reseted.length > 0)
                            $('#previewOutputButton').addClass('active');
                        else
                            $('#previewOutputButton').removeClass('active');
*/

                        //updateOutput();

                        getOutput(true);

                    }).selectpicker({
                        windowPadding: [64, 30, 30, 30],
                        noneSelectedText: ''
                    });

                    // Update the select boxes items that are similar to the name of the DB columns
                    autoMapping();

                    // Add new fields to the Form Validation
                    var $select = $('#mapping_table tr.mapping_' + $('#table').val()).find('select');
                    $('#cie-form').formValidation('addField', $select);

                }

            });

        } else {

            // Hide the other select boxes of other DB
            $('#mapping_table tbody tr:not(.mapping_' + $('#table').val() + ', .messageContainerRow)').hide().find('select').attr('disabled', 'disabled');

            // Add the new select boxes for the current DB
            $('#mapping_table tbody tr.mapping_' + $('#table').val()).show().find('select').removeAttr('disabled');

        }

         //$('#taskForm').formValidation('revalidateField', $('#mapping_table').find('select'));

    });



    //==================================================================================================
    //  getOutput
    //==================================================================================================

    function getOutput(reset) {

        if (reset) {
            output_header = [];
            output_data = [];
            total_output_rows = 0;
            output_file_position = 0;
            output_row_position = 0;
        }

        if (output_ajax != undefined)
            output_ajax.abort();

        output_ajax = $.ajax({
            type: "POST",
            url: "?action=import_getOutput&file_position=" + output_file_position + "&row_position=" + output_row_position,
            data: $('#cie-form').serialize(),
            dataType: 'json'
        }).done(function( out ) {

            if (out.status == 'success' || out.status == 'partial content') {

                if (reset) {
                    for (i = 0; i < db_cols_labels.length; i++) {
                        if (mapping[i] != undefined)
                            output_header.push(db_cols_labels[i]);
                    }
                }

                output_data = output_data.concat(out.data.body);

                total_output_rows += out.data.rows;

                output_file_position = out.data.file_position;

                if (out.status == 'success') {

                    $('.output-rows').removeClass('loading').html(total_output_rows);

                    // Create the preview output file table
                    var columns_obj = [];
                    for (var i = 0; i < output_header.length; i++)
                        columns_obj.push({'title': output_header[i]});

                    $('#previewOutputTable').bootstrapTable('destroy').bootstrapTable({
                        columns:    columns_obj,
                        showHeader: true,
                        data:       output_data,
                        search:     true,
                        pagination: true
                    });

                    $('.output-cols').html(columns_obj.length);
                    $('.output-rows, #results-total-rows').html(output_data.length);

                    $('#previewOutputButton').addClass('active');

                } else {
                    getOutput();
                }

                // If it has a warning
                if (out.status == 'warning') {
                    $('#step1 .alert').html('<strong>Warning!</strong> ' + out.message).show();
                }

            }
        });

    }


    //==================================================================================================
    //  SUBMIT FORM
    //==================================================================================================

    $('#btn-run').on('click', function() {
        date_begin = moment();
        chronometer.start();
        $('#btn-back, #btn-run').hide();
        $('#report, #btn-log, #review-progress .progress-label').show();

        $('#review .review-text').html('IMPORTING');

        // Progress bar
        $('#preview-file .progress').asProgress('destroy');
        $('#review-progress .progress').asProgress({
            namespace: 'progress',
            bootstrap: true,
            min: 0,
            max: 100,
            goal: 100,
            speed: 1, // speed of 1/100
            easing: 'linear',
            onFinish: function() {
                $('#review-progress .triangle').addClass('success');
            }
        });

        import_file_position = 0;

        // Get the id of the next export
        $.ajax({
            type: "POST",
            url: '?action=import_getId',
            dataType: 'json'
        }).done(function( out ) {
            if (out.status == 'error') {
                clearInterval(import_progress);
            } else {
                import_id = out.data.id;
                importCsv();
            }
        });

        // Get the import progress
        import_progress = setInterval(function() {
            if (import_progress_ajaxcall != undefined)
                import_progress_ajaxcall.abort();
            import_progress_ajaxcall = $.ajax({
                type: "POST",
                url: "actions.php?action=getProgress&id=" + import_id + '&type=import',
                dataType: 'json'
            }).done(function( out ) {
                var percentage = Math.round(100 * out.row_position / total_output_rows);
                if (parseInt(out.row_position) > 0)
                    $('#review-progress .progress').asProgress('go', percentage + '%');
                $('#results-rows').html(out.row_position);
            });
        }, 250);

    });


    function importCsv() {

        var ajaxcall = $.ajax({
            type: "POST",
            url: "?action=import&id=" + import_id + "&file_position=" + import_file_position + "&row_position=" + import_row_position + '&retry=' + import_retry,
            data: $('#cie-form').serialize(),
            dataType: 'json'
        }).done(function( out ) {

            if (out.status == 'success' || out.status == 'partial content') {

                import_retry = 0;

                import_file_position = parseInt(out.data.file_position);
                import_row_position = parseInt(out.data.row_position);

                if (date_begin)
                    $('#time-info .date_begin > span:eq(1)').html(date_begin.format(date_begin_format)).parent().show();

                results.total_imported   += (out.data.total_skipped + out.data.total_inserted + out.data.total_updated + out.data.total_deleted + out.data.total_error);
                results.total_skipped   += out.data.total_skipped;
                results.total_inserted += out.data.total_inserted;
                results.total_updated  += out.data.total_updated;
                results.total_deleted  += out.data.total_deleted;
                results.total_error    += out.data.total_error;

                if (out.data.total_skipped)
                    $('#results tr.skipped > td:eq(1)').html(results.total_skipped).parent().show();
                if (out.data.total_inserted)
                    $('#results tr.inserted > td:eq(1)').html(results.total_inserted).parent().show();
                if (out.data.total_updated)
                    $('#results tr.updated > td:eq(1)').html(results.total_updated).parent().show();
                if (out.data.total_deleted)
                    $('#results tr.deleted > td:eq(1)').html(results.total_deleted).parent().show();
                if (out.data.total_error)
                    $('#results tr.error > td:eq(1)').html(results.total_error).parent().show();

                $('#results-rows').html(results.total_imported);

                if (out.status == 'success') {

                    // Import done
                    chronometer.pause();
                    clearInterval(import_progress);
                    $('#review .review-text').html('IMPORTED');
                    $('#review-progress .progress').asProgress('finish');
                    time_elapsed = chronometer.getElapsedTime();
                    date_end = moment(date_begin).add(time_elapsed, 'ms');

                    if (date_end)
                        $('#time-info .date_end > span:eq(1)').html(date_end.format(date_end_format)).parent().show();

                    // create the reports table
                    var columns_obj = [];
                    for (i = 0; i < out.data.header.length; i++) {
                        columns_obj.push({
                            'title': out.data.header[i],
                            'class': (i == 0 ? 'row' : (i == 1 ? 'status' : (i == 2 ? 'message' : '')))
                        });
                    }

                    $('#result_table').bootstrapTable({
                        columns:            columns_obj,
                        data:               out.data.body,
                        search :            true,
                        pagination:         true,
                        showColumns:        true,
                        pageSize:           10,
                        pageList:           [10, 20, 50, 100, 500, 1000, 10000, 100000, 1000000, 'All'],
                        onlyInfoPagination: false,
                        showColumns:        true,
                        showToggle:         false,
                        buttonsClass:       'default',
                        iconsPrefix:        'icon',
                        icons: {
                            paginationSwitchDown:   'wb-chevron-down',
                            paginationSwitchUp:     'wb-chevron-up',
                            refresh:                'wb-refresh',
                            toggle:                 'wb-list',
                            columns:                'wb-grid-4',
                            detailOpen:             'wb-plus',
                            detailClose:            'wb-minus'
                        },
                        rowStyle: function(row, index) {
                          return {
                            classes: results_class_colors[row[1]]
                            //classes: row[1]
                            //css: {"color": "blue", "font-size": "50px"}
                          };
                        },
                        formatShowingRows: function(pageFrom, pageTo, totalRows) {
                            return 'Total: ' + totalRows;
                        },
                        formatRecordsPerPage: function(pageNumber) {
                            return pageNumber;
                        },
                        onPreBody: function(data) {
                            if ($('#log .fixed-table-toolbar > .page-list').length)
                                $('#log .fixed-table-toolbar > .page-list').html($('#log .pagination-detail > .page-list')).find('.btn-group ').removeClass('dropup').addClass('dropdown');
                            else
                                $('#log .pagination-detail .page-list').insertBefore('#log .fixed-table-toolbar > .columns').addClass('pull-right').show().find('.btn-group ').removeClass('dropup').addClass('dropdown');

                            $('#log .page-list .dropdown-menu li:last-child a').html('All');
                            if ($('#log .page-list .dropdown-menu li:last-child').prev().find('a').html() == data.length)
                                $('#log .page-list .dropdown-menu li:last-child').prev().remove();

                        }
                    });

                    //$('#wizard').hide();
                    $('#report').show();

                    // Save log
                    $.ajax({
                        type: "POST",
                        url: "?action=import_saveLog&id=" + import_id,
                        data: {
                            'filename':         input_settings.filename,
                            'input_cols':       input_settings.cols,
                            'input_rows':       input_settings.rows,
                            'size':             input_settings.size,
                            'db':               $('#db').val(),
                            'table':            $('#table').val(),
                            'output_cols':      output_header.length,
                            'output_rows':      total_output_rows,
                            'time_elapsed':     time_elapsed,
                            'date_begin':       date_begin.format(date_begin_format),
                            'date_end':         date_end.format(date_end_format),
                            'total_imported':   results.total_imported,
                            'total_skipped':    results.total_skipped,
                            'total_inserted':   results.total_inserted,
                            'total_updated':    results.total_updated,
                            'total_deleted':    results.total_deleted,
                            'total_error':      results.total_error
                        }
                    }).done(function( out ) {
                    });


                } else {
                    importCsv();
                }

            } else {
                // warning
            }

        }).fail(function() {

            // Retry
            import_retry++;

            if (import_retry <= 3) {
                importCsv();
            } else {
                chronometer.pause();
                clearInterval(import_progress);
                $('#review .review-text').html('ERRORS FOUND');
            }
        });

    }

});