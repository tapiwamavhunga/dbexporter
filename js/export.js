var mapping_tr_select_boxes_options = '';
var conditions_tr_select_boxes_options = '';

var db_cols_names = [];
var db_cols_labels = [];

var input_header = [];
var input_data = [];
var input_settings = {
    'cols':     0,
    'rows':     0
};
var input_file_position = 0;
var input_row_position = 0;

var mapping = [];

var output_header = [];
var output_data = [];
var output_settings = {
    'filename':     '',
    'cols':         0,
    'rows':         0,
    'size':         0,
    'header':       true,
    'delimiter':    _export_csv_delimiter,
    'enclosure':    _export_csv_enclosure,
    'encoding':     _export_csv_encoding
};
var total_output_rows = 0;
var output_file_position = 0;
var output_row_position = 0;
var output_ajax;

var results = {
    'total_exported':   0,
    'total_success':    0,
    'total_error':      0
};
var results_class_colors = {
    'Success': 'text-success'
};

var export_id = 0;
var export_file_position = 0;
var export_row_position = 0;
var import_progress_ajaxcall;
var import_progress;
var export_retry = 0;

var date_begin = '';
var date_begin_format = 'YYYY-MM-DD HH:mm:ss.SSS';
var date_end = '';
var date_end_format = 'YYYY-MM-DD HH:mm:ss.SSS';
var time_elapsed = 0;


// Basename of a string
function basename(str) {
    if (str.lastIndexOf('/') !== -1)
        var base = new String(str).substring(str.lastIndexOf('/') + 1);
    else if(str.lastIndexOf('\\') !== -1)
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

    $('#step2 .nav a').on('click', function() {
        $('#file_destination').val($(this).attr('href').replace('#', ''));
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

    var chronometer = new Chronometer({
        precision: 10,
        ontimeupdate: function (t) {
            $('#time-elapsed span').html(humanFormat(t, ':', '.'));
        }
    });


    //==================================================================================================
    //  EVENTS LISTNERS
    //==================================================================================================

    $('#db').on('change', function() {
        var db = $(this).val();

        if (!$('#table option[data-rel="' + db + '"]').length) {

            $('#table').html('').selectpicker('refresh');
            $('#table').parent().addClass('loading');

            var ajaxcall = $.ajax({
                type: "POST",
                url: "?action=export_getTables",
                data: {
                    'db_id' : db
                },
                dataType: 'json'
            }).done(function( out ) {

                $('#table').parent().removeClass('loading');

                if (out.status == 'success') {

                    var out_options = '';
                    for(i = 0; i < out.data.names.length; i++)
                        out_options += '<option value="' + out.data.ids[i] + '" data-rel="' + db + '">' + out.data.labels[i] + '</option>';

                    $('#table option').prop("disabled", true);
                    $('#table').append(out_options);
                    $('#table option[data-rel="' + db + '"]:eq(0)').prop('selected', true);
                    $('#table').selectpicker('refresh').trigger('change');

                    // Hide the other select boxes of other DB tables
                    //$('#mapping_table tbody tr:not(.mapping_' + $('#table').val() + ',.messageContainerRow)').hide().find('select').attr('disabled','disabled');

                } else if (out.status == 'error') {

                    $('#table').parent().find('.dropdown-toggle span.filter-option').html(out.message);

                }

            });

        } else {

            $('#table option').prop("disabled", false).not('[data-rel="' + db + '"]').prop("disabled", true);
            $('#table option[data-rel="' + db + '"]:eq(0)').prop('selected', true);
            $('#table').selectpicker('refresh').trigger('change');

        }

    });



    $('#desktop input').on('change', function() {
        $('#review-output h4').html($(this).val());

        var input_value = $('#desktop input').val();
        var in_options = false;
        if (input_value != '') {
            $('#table option').each(function(index) {
                if ($(this).attr('value') + '.csv' == input_value)
                    in_options = true;
            });
            if (!in_options)
                $(this).addClass('touched');
        } else {
            $(this).removeClass('touched');
        }
    });

    $('#filter_row').on('change', function() {
        // TODO: AJAX OR JS TO SET THE FILTER
        if ($(this).val() != '') {
            getOutput(true);
        } else {

        }
    });

    $('#header').on('ifChanged', function(event) {

        $('#previewOutputTable').bootstrapTable('refreshOptions', {
            showHeader: $(this).is(':checked')
        });

        if ($(this).is(':checked')) {
            $('#mapping_table').removeClass('header_hidden');
        } else {
            $('#mapping_table').addClass('header_hidden');
        }

    });


    $('#btn-maping-add').click(function() {

        output_settings.cols++;
        $('#review-output .output-cols').html(output_settings.cols);

        $('#mapping_table tbody > tr.mapping_' + $('#table').val() + ':eq(0) button.remove').show();

        var tr_class = $('#mapping_table tbody > tr.mapping_' + $('#table').val() + ':last').attr("class");
        var new_tr = '<tr class="' + tr_class + '">' + mapping_tr_select_boxes_options + '</tr>';
        $('#mapping_table tbody > tr.mapping_' + $('#table').val() + ':last').after(new_tr);

        $('#mapping_table tr.mapping_' + $('#table').val() + ' select').on('change', function() {

            // Auto fill the input with the selected option
            var input_value = $(this).parents('tr').find('input').val();
            var in_options = false;
            if (input_value != '') {
                $(this).find('option').each(function(index) {
                    if ($(this).html() == input_value)
                        in_options = true;
                });
            }
            if (input_value == '' || in_options)
                $(this).parents('tr').find('input').val($(this).find('option:selected').html());

        /*
            // updates the total and the DB fields that are selected
            var index = $(this).attr('rel').substring($(this).attr('rel').lastIndexOf('_') + 1);

            if ($(this).val() != '') {
                mapping[index]=$(this).val();

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


            // Activates or not the Preview Data To Be Imported button
            //var output_header_reseted = output_header.filter(function() {return true;}); // reset the indexes of the array, because there are undefined keys
            //if (output_header_reseted.length > 0)
                //$('#previewOutputButton').addClass('active');
            //else
                //$('#previewOutputButton').removeClass('active');

            //updateOutput();
            getOutput(true);
        */
            resetOutput();

        }).selectpicker({
            windowPadding: [64, 30, 30, 30],
            noneSelectedText: ''
        });

        $('#mapping_table tr.mapping_' + $('#table').val() + ' input').on('change', function() {
            resetOutput();
        });

        // Remove mapping line
        $('#mapping_table tbody > tr.mapping_' + $('#table').val() + ':last button.remove').click(function(event) {
            event.preventDefault();

            var $row = $(this).parents('tr'),
                $option = $row.find('[name="mapping[]"]');
            $('#cie-form').formValidation('removeField', $option);

            $(this).parent().parent().remove();
            if ($('#mapping_table tbody > tr.mapping_' + $('#table').val() + '').length == 1)
                $('#mapping_table tbody > tr.mapping_' + $('#table').val() + ' a').hide();

            getOutput(true);
        });

        resetOutput();

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


    $('#btn-log').on('click', function() {
        $('#log').toggle();
        if ($(this).html() == 'VIEW LOG')
            $(this).html('HIDE LOG');
        else
            $(this).html('VIEW LOG');
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
            filename: {
                validators: {
                    notEmpty: {
                        message: 'The filename is required'
                    }
                }
            },
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
            server_path: {
                validators: {
                    notEmpty: {
                        message: 'Server path is required'
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
                    /*
                    notEmpty: {
                        message: 'The Mapping is required'
                    },
                    */
                    callback: {
                        callback: function(value, validator, $field) {

                            var $mapping         = $('#mapping_table tr.mapping_' + $('#table').val() + ' select'),
                                numMapping       = $mapping.length,
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

            for(var i = numTabs - 1; i >= currentIndex; i--)
                $('#wizard .wizard-inner .nav > li').eq(i).removeClass('validated');

            if (currentIndex == 0) {
                $('#btn-back, #btn-run').hide();
                $('#btn-next').show();
            } else if (currentIndex == numTabs - 1) {
                $('#btn-next').hide();
                $('#btn-back, #btn-run').show();
            } else {
                $('#btn-run').hide();
                $('#btn-back,#btn-next').show();
            }
        }
    });

    function validateTab(index) {
        var fv   = $('#cie-form').data('formValidation'), // FormValidation instance

        // The current tab
        $tab = $('#cie-form > .tab-content > .tab-pane').eq(index);

        if (index == 1) {
            $tab = $tab.find('.tab-pane.active');
            $tab = $tab.add($('#delimiter').parent());
            $tab = $tab.add($('#enclosure').parent());
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

        if (index == 0)
            $('#wizard .wizard-inner .nav > li').eq(2).removeClass('disabled');

        $('#wizard .wizard-inner .nav > li').eq(index).addClass('validated');

        return true;
    }

    function changeTab(currentIndex, nextIndex) {

        // If import started then don't change tab
        if (date_begin)
            return false;

        // Disabled tabs don't change tab
        if ($('#wizard .wizard-inner .nav > li').eq(nextIndex).hasClass('disabled'))
            return false;

        // Disable tab 3 if tab 2 has errors and change tab going to tab 1
        if (currentIndex == 1 && nextIndex == 0 && !validateTab(1))
            $('#wizard .wizard-inner .nav > li').eq(2).addClass('disabled');

        // Validate Tab
        var all_tabs_valid = true;
        if (currentIndex < nextIndex) {
            for(i = 0; i < nextIndex; i++) {
                all_tabs_valid = all_tabs_valid && validateTab(i);
            }
            if (!all_tabs_valid)
                return false;
        }

        return true;
    }


    //==================================================================================================
    //  GET INPUT
    //==================================================================================================

    function getInput(reset) {

        if (reset) {
            input_header = [];
            input_data = [];
            input_settings.cols = 0;
            input_settings.rows = 0;
        }

        // Set the input cols
        if (input_settings.cols == 0) {
            input_settings.cols = $('#mapping_table tbody > tr.mapping_' + $('#table').val() + ' select').first().find('option').length-1;
            $('#review-input .input-cols').html(input_settings.cols);
        }

        // Preview input columns names
        var columns_obj = [];
        $('#mapping_table tbody > tr.mapping_' + $('#table').val() + ' select').first().find('option').each(function(index) {
            if (index > 0) {
                columns_obj.push({
                    //'field': $(this).find('input').val(),
                    'title': $(this).text(),
                    'sortable': true
                });
            }
        });

        // Create the preview input file table
        $('#previewInputTable').bootstrapTable('destroy').bootstrapTable({
            url:                '?action=export_getInput&db=' + $('#db').val() + '&table=' + $('#table').val(),
            method:             'get', // get, post
            columns:            columns_obj,
            search:             true,
            sortable:           true,
            pagination:         true,
            sidePagination:     'server',
            pageSize:           10,
            pageList:           [10, 20, 50, 100, 500, 1000, 10000, 100000, 1000000, 'All'],
            onlyInfoPagination: false,
            showColumns:        true,
            showToggle:         false,
            buttonsClass:       'default',
            iconsPrefix:        'icon',
            icons:              {
                paginationSwitchDown:   'wb-chevron-down',
                paginationSwitchUp:     'wb-chevron-up',
                refresh:                'wb-refresh',
                toggle:                 'wb-list',
                columns:                'wb-grid-4',
                detailOpen:             'wb-plus',
                detailClose:            'wb-minus'
            },
//            cookie:                     true,
//            cookieExpire:               '1m',
//            cookieIdTable:              'importsTable',
            //cookiesEnabled:             ['bs.table.sortOrder', 'bs.table.sortName', 'bs.table.pageNumber', 'bs.table.pageList', 'bs.table.columns', 'bs.table.searchText', 'bs.table.filterControl'],
            formatShowingRows: function(pageFrom, pageTo, totalRows) {
                if (input_settings.rows == 0) {
                    input_settings.rows = totalRows;
                    $('#review-input .review-input-rows span').html(input_settings.rows);
                }
                return 'Total: ' + totalRows;
            },
            formatRecordsPerPage: function(pageNumber) {
                return pageNumber;
            },
            onPreBody: function(data) {
                if ($('#previewInput .fixed-table-toolbar > .page-list').length)
                    $('#previewInput .fixed-table-toolbar > .page-list').html($('#previewInput .pagination-detail > .page-list')).find('.btn-group ').removeClass('dropup').addClass('dropdown');
                else
                    $('#previewInput .pagination-detail .page-list').insertBefore('#previewInput .fixed-table-toolbar > .columns').addClass('pull-right').show();

                $('#previewInput .page-list .dropdown-menu li:last-child a').html('All');
                if ($('#previewInput .page-list .dropdown-menu li:last-child').prev().find('a').html() == data.length)
                    $('#previewInput .page-list .dropdown-menu li:last-child').prev().remove();

            }
        });
        $('#previewInputButton').addClass('active');

    }


    //==================================================================================================
    //  DB TABLE SELECTBOX CHANGE
    //==================================================================================================

    $('#table').on('change', function() {

        // Auto add filename
        if (!$('#desktop input[name=filename]').hasClass('touched')) {
            output_settings.filename = $('#table').val() + '.csv';
            $('#desktop input[name=filename]').val(output_settings.filename);
            $('#review-output h4').html(output_settings.filename);
            $('#cie-form').formValidation('revalidateField', $('#desktop input'));
        }

        // Add Mapping
        if (!$('.mapping_' + $('#table').val()).length) {

            var ajaxcall = $.ajax({
                type: "POST",
                url: "?action=export_getColumns",
                data: {
                    "db_id":    $('#db').val(),
                    "table_id": $('#table').val()
                },
                dataType: 'json'
            }).done(function( out ) {

                if ($('#table option:selected').text() != '')
                    $('#review-input h4').html($('#table option:selected').text());

                if (out.status == 'success') {

                    // Global vars
                    db_cols_names = out.data.names;
                    db_cols_labels = out.data.labels;

                    var out_select_options = '<option value=""></option>';
                    for(i = 0; i < db_cols_names.length; i++) {
                        out_select_options += '<option value="' + i + '">' + db_cols_labels[i] + '</option>';
                    }

                    mapping_tr_select_boxes_options = '';
                    mapping_tr_select_boxes_options += '<td><i class="icon wb-menu"></i></td>';
                    mapping_tr_select_boxes_options += '<td><select name="mapping[]" class="selectpicker form-control">' + out_select_options + '</select></td>';
                    mapping_tr_select_boxes_options += '<td class="form-group"><input name="csv_header[]" value="" class="form-control"></td>';
                    mapping_tr_select_boxes_options += '<td><button class="remove btn btn-sm btn-icon btn-flat btn-default" title="" data-tooltip="true" data-original-title="Remove"><i class="icon wb-close"></i></button></td>';

                    var out_mapping = '';
                    for( i = 0; i < db_cols_names.length; i++) {
                        out_mapping += '<tr class="mapping_' + $('#table').val() + '">' + mapping_tr_select_boxes_options + '</tr>';
                    }

                    // Hide the other select boxes of other DB tables
                    $('#mapping_table tbody tr:not(.mapping_' + $('#table').val() + ', .messageContainerRow)').hide().find('select, input').attr('disabled', 'disabled');

                    // Add the new select boxes for the current DB
                    $('#mapping_table tbody .messageContainerRow').before(out_mapping);

                    // Reoder Rows
                    $("#mapping_table tbody").sortable({
                        handle: ".icon.wb-menu",
                        helper: function(e, tr) {
                            var $originals = tr.children();
                            var $helper = tr.clone();
                            $helper.children().each(function(index) {
                                $(this).width($originals.eq(index).width())
                            });
                            return $helper;
                        },
                        stop: function(e, ui) {
                            //getOutput(true);
                            resetOutput();
                        }
                    });

                    // On removing a mapping
                    $("#mapping_table tbody button.remove").click(function(event) {
                        event.preventDefault();

                        var $row = $(this).parents('tr'),
                            $option = $row.find('[name="mapping[]"]');
                        $('#cie-form').formValidation('removeField', $option);

                        $(this).parent().parent().remove();
                        if ($('#mapping_table tbody > tr.mapping_' + $('#table').val() + '').length == 1)
                            $('#mapping_table tbody > tr.mapping_' + $('#table').val() + ' button.remove').hide();
                        output_settings.cols--;
                        $('#review-output .output-cols').html(output_settings.cols);

                        getOutput(true);
                    });


                    // Auto Mapping
                    $('#mapping_table tr.mapping_' + $('#table').val() + ' select').each(function(index) {
                        $(this).val($(this).find('option:eq(' + (index + 1) + ')').attr('value'));
                        $(this).parent().parent().find('input').val($(this).find('option:eq(' + (index + 1) + ')').html());
                    });


                    // On Mapping change update the Selected DB Fields and updates the Preview Data to be Imported
                    $('#mapping_table tr.mapping_' + $('#table').val() + ' select').on('change', function() {

                        // Auto fill the input with the selected option
                        var input_value = $(this).parents('tr').find('input').val();
                        var in_options = false;
                        if (input_value != '') {
                            $(this).find('option').each(function(index) {
                                if ($(this).html() == input_value)
                                    in_options = true;
                            });
                        }
                        if (input_value == '' || in_options)
                            $(this).parents('tr').find('input').val($(this).find('option:selected').html());



/*
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


                        // Activates or not the Preview Data To Be Imported button
                        //var output_header_reseted = output_header.filter(function() {return true;}); // reset the indexes of the array, because there are undefined keys
                        //if (output_header_reseted.length > 0)
                            //$('#previewOutputButton').addClass('active');
                        //else
                            //$('#previewOutputButton').removeClass('active');

                        //updateOutput();
*/

                        getOutput(false);

                    }).selectpicker({
                        windowPadding: [64, 30, 30, 30],
                        noneSelectedText: ''
                    });

                    $('#mapping_table tr.mapping_' + $('#table').val() + ' input').on('change', function() {
                        resetOutput();
                    });

                    getInput(true);
                    getOutput(true);

                    // Add new fields to the Form Validation
                    //var $select = $('#mapping_table tr.mapping_' + $('#table').val()).find('select');
                    //$('#cie-form').formValidation('addField', $select);


                    // Conditions

                    // Hide the other select boxes of other DB tables
                    $("#conditions_table tbody").html('').parent().addClass('empty');
                    conditions_tr_select_boxes_options = '<tr>';
                    conditions_tr_select_boxes_options += '<td><i class="icon wb-menu"></i></td>';
                    conditions_tr_select_boxes_options += '<td><select name="conditions[0][]"><option value="AND">AND</option><option value="OR">OR</option></select></td>';
                    conditions_tr_select_boxes_options += '<td><select name="conditions[1][]" class="selectpicker form-control">' + out_select_options + '</select></td>';
                    conditions_tr_select_boxes_options += '<td><select name="conditions[2][]"><option value="=">=</option><option value="!=">!=</option><option value="&gt;">&gt;</option><option value="&lt;">&lt;</option><option value="&gt;=">&gt;=</option><option value="&lt;=">&lt;=</option><option value="CONTAINS">CONTAINS</option><option value="NOT CONTAINS">NOT CONTAINS</option></select></td>';
                    conditions_tr_select_boxes_options += '<td class="form-group"><input name="conditions[3][]" value="" class="form-control"></td>';
                    conditions_tr_select_boxes_options += '<td><button class="remove btn btn-sm btn-icon btn-flat btn-default" title="" data-tooltip="true" data-original-title="Remove"><i class="icon wb-close"></i></button></td>';
                    conditions_tr_select_boxes_options += '</tr>';

                }

            });

        } else {

            // Hide the other select boxes of other DB
            $('#mapping_table tbody tr:not(.mapping_' + $('#table').val() + ', .messageContainerRow)').hide().find('select').attr('disabled', 'disabled');

            // Add the new select boxes for the current DB
            $('#mapping_table tbody tr.mapping_' + $('#table').val()).show().find('select, input').removeAttr('disabled');

            getInput(true);
            getOutput(true);

            // Set the output cols
            output_settings.cols = $('#mapping_table tr.mapping_' + $('#table').val()).length;
            $('#review-output .output-cols').html(output_settings.cols);

        }

         //$('#taskForm').formValidation('revalidateField', $('#mapping_table').find('select'));

    });

    $('#table').trigger('change');


    //==================================================================================================
    //  getOutput
    //==================================================================================================

    function getOutput(reset) {

        if (reset) {
            output_header = [];
            output_data = [];
            total_output_rows = 0;
            output_settings.cols = 0;
            output_settings.rows = 0;
        }

        // Columns
        var columns_obj = [];
        $('#mapping_table tbody > tr.mapping_' + $('#table').val()).each(function(index) {
            columns_obj.push({
                //'field': $(this).find('input').val(),
                'title': $(this).find('input').val(),
                'sortable': true
            });
        });

        // Set the output cols
        output_settings.cols = $('#mapping_table tbody > tr.mapping_' + $('#table').val()).length;
        $('#review-output .output-cols').html(output_settings.cols);

        // Output Table
        $('#previewOutputTable').bootstrapTable('destroy').bootstrapTable({
            url:                '?action=export_getOutput',
            method:             'get', // get, post

            queryParams:        function(params) {
                return {
                    search:         params.search,
                    sort:           params.sort,
                    order:          params.order,
                    offset:         params.offset,
                    limit:          params.limit,
                    db:             $('#db').val(),
                    table:          $('#table').val(),
                    mapping:        $('select[name="mapping[]"]').map(function () { return $(this).val(); }).get(),
                    filter_rows:    $('input[name="filter_rows"]').val(),
                    conditions:     $('#conditions_table select, #conditions_table input').serializeArray()
                }
            },
            /*
            queryParams: function (params) {},
              ajaxOptions: {
                url: '/echo/json/',
                method: 'post',
                contentType: 'application/json',
                data: data
              },
              ajax: function (request) {
                $.ajax(request);
              },
            */

            columns:                columns_obj,
            search:                 true,
            sortable:               true,
            pagination:             true,
            sidePagination:         'server',
            pageSize:               10,
            pageList:               [10, 20, 50, 100, 500, 1000, 10000, 100000, 1000000, 'All'],
            onlyInfoPagination:     false,
            showColumns:            true,
            showToggle:             false,
            buttonsClass:           'default',
            iconsPrefix:            'icon',
            icons: {
                paginationSwitchDown:   'wb-chevron-down',
                paginationSwitchUp:     'wb-chevron-up',
                refresh:                'wb-refresh',
                toggle:                 'wb-list',
                columns:                'wb-grid-4',
                detailOpen:             'wb-plus',
                detailClose:            'wb-minus'
            },
//            cookie:                     true,
//            cookieExpire:               '1m',
//            cookieIdTable:              'importsTable',
            //cookiesEnabled:             ['bs.table.sortOrder', 'bs.table.sortName', 'bs.table.pageNumber', 'bs.table.pageList', 'bs.table.columns', 'bs.table.searchText', 'bs.table.filterControl'],
            formatShowingRows: function(pageFrom, pageTo, totalRows) {
                if (output_settings.rows == 0) {
                    output_settings.rows = totalRows;
                    $('.output-rows, #results-total-rows').html(output_settings.rows);
                }
                return 'Total: ' + totalRows;
            },
            formatRecordsPerPage: function(pageNumber) {
                return pageNumber;
            },
            onPreBody: function(data) {
                if ($('#previewOutput .fixed-table-toolbar > .page-list').length)
                    $('#previewOutput .fixed-table-toolbar > .page-list').html($('#previewOutput .pagination-detail > .page-list')).find('.btn-group ').removeClass('dropup').addClass('dropdown');
                else
                    $('#previewOutput .pagination-detail .page-list').insertBefore('#previewOutput .fixed-table-toolbar > .columns').addClass('pull-right').show();

                $('#previewOutput .page-list .dropdown-menu li:last-child a').html('All');
                if ($('#previewOutput .page-list .dropdown-menu li:last-child').prev().find('a').html() == data.length)
                    $('#previewOutput .page-list .dropdown-menu li:last-child').prev().remove();

            }
        });
        $('#previewOutputButton').addClass('active');

    }


    //==================================================================================================
    //  Change Columns
    //==================================================================================================

    function resetOutput() {

        // Columns
        var columns_obj = [];
        $('#mapping_table tbody > tr.mapping_' + $('#table').val()).each(function(index) {
            columns_obj.push({
                //'field': $(this).find('input').val(),
                'title': $(this).find('input').val(),
                'sortable': true
            });
        });

        $('#previewOutputTable').bootstrapTable('refreshOptions',{
            columns: columns_obj
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

        $('#review .review-text').html('EXPORTING');

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

        export_file_position = 0;

        // Get the id of the next export
        $.ajax({
            type: "POST",
            url: '?action=export_getId',
            dataType: 'json'
        }).done(function( out ) {
            if (out.status == 'error') {
                clearInterval(import_progress);
            } else {
                export_id = out.data.id;
                exportCsv();
            }
        });

        // Get the import progress
        import_progress = setInterval(function() {
            if (import_progress_ajaxcall != undefined)
                import_progress_ajaxcall.abort();
            import_progress_ajaxcall = $.ajax({
                type: "POST",
                url: "actions.php?action=getProgress&id=" + export_id + '&type=export',
                dataType: 'json'
            }).done(function( out ) {
                var percentage = Math.round(100 * out.row_position / output_settings.rows);
                if (parseInt(out.row_position) > 0)
                    $('#review-progress .progress').asProgress('go', percentage + '%');
                $('#results-rows').html(out.row_position);
            });
        }, 250);

    });

    function exportCsv() {
        var ajaxcall = $.ajax({
            type: "POST",
            url: "?action=export&id=" + export_id + "&file_position=" + export_file_position + "&row_position=" + export_row_position + '&retry=' + export_retry,
            data: $('#cie-form').serialize(),
            dataType: 'json'
            //processData: false
        }).done(function( out ) {

            if (out.status == 'success' || out.status == 'partial content') {

                export_retry = 0;

                export_file_position = parseInt(out.data.file_position);
                export_row_position = parseInt(out.data.row_position);

                if (date_begin)
                    $('#time-info tr.date_begin > td:eq(1)').html(date_begin.format(date_begin_format)).parent().show();

                results.total_exported += (out.data.total_success + out.data.total_error);
                results.total_success  += out.data.total_success;
                results.total_error    += out.data.total_error;

                if (out.data.total_success)
                    $('#results tr.inserted > td:eq(1)').html(results.total_success).parent().show();
                if (out.data.total_error)
                    $('#results tr.error > td:eq(1)').html(results.total_error).parent().show();

                $('#results-rows').html(results.total_exported);

                if (out.status == 'success') {

                    // Export done
                    chronometer.pause();
                    clearInterval(import_progress);
                    $('#review .review-text').html('EXPORTED');
                    $('#review-progress .progress').asProgress('finish');
                    time_elapsed = chronometer.getElapsedTime();
                    date_end = moment(date_begin).add(time_elapsed, 'ms');

                    if (date_end)
                        $('#time-info tr.date_end > td:eq(1)').html(date_end.format(date_end_format)).parent().show();


                    output_settings.size = out.data.file_size;
                    $('#review-output .output-size').html(formatBytes(output_settings.size));

                    output_settings.filename = $('#desktop input[name=filename]').val();

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
                        url: "?action=export_saveLog&id=" + export_id,
                        data: {
                            'input_cols':       input_settings.cols,
                            'input_rows':       input_settings.rows,
                            'db':               $('#db').val(),
                            'table':            $('#table').val(),
                            'filename':         output_settings.filename,
                            'output_cols':      output_settings.cols,
                            'output_rows':      output_settings.rows,
                            'size':             output_settings.size,
                            'time_elapsed':     time_elapsed,
                            'date_begin':       date_begin.format(date_begin_format),
                            'date_end':         date_end.format(date_end_format),
                            'total_exported':   results.total_exported,
                            'total_success':    results.total_success,
                            'total_error':      results.total_error
                        }
                    }).done(function( out ) {
                    });

                    if($('#file_destination').val()=='desktop')
                        $('#downloadFrame').attr('src' , "actions.php?action=export_download&id=" + export_id + '&filename=' + output_settings.filename);

                } else {
                    // Partial
                    exportCsv();
                }

            } else {
                // warning
                chronometer.pause();
                clearInterval(import_progress);

                if (out.status == 'error') {
                    $('#review .review-text').html('ERROR FOUND');
                    $('#review-progress .progress-bar').removeClass('progress-bar-success').addClass('progress-bar-danger');
                    $('#review-progress .triangle').removeClass('success').addClass('danger');
                    $('#results tr.error > td:eq(1)').html(out.message).parent().show();
                }
            }

        }).fail(function() {

            // Retry
            export_retry++;

            if (export_retry <= 3) {
                exportCsv();
            } else {
                chronometer.pause();
                clearInterval(import_progress);
                $('#review .review-text').html('ERRORS FOUND');
                $('#review-progress .progress-bar').removeClass('progress-bar-success').addClass('progress-bar-danger');
                $('#review-progress .triangle').removeClass('success').addClass('danger');
                //$('#results tr.error > td:eq(1)').html(out.message).parent().show();
            }
        });

    }

});