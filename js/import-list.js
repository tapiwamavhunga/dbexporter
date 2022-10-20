var input_data = [];
var total_rows = 0;

var delete_ids = [];

$(function() {

    //==================================================================================================
    //  TABLE
    //==================================================================================================

    $('#importsTable').bootstrapTable({
        url:                '?action=import_list_get',
        method:             'get', // get, post

        search:             true,

        sortable:           true,
        sortName:           'id',
        sortOrder:          'desc',

        clickToSelect:      true,

        pagination:         true,
        paginationLoop:     true,
        sidePagination:     'client', // TODO: 'server'
        pageSize:           10,
        pageList:           [10, 20, 50, 100, 500, 1000, 10000, 100000, 1000000, 'All'],
        onlyInfoPagination: false,
/*
        queryParams: function(params) {
            return {
                search: params.search,
                sort:   params.sort,
                order:  params.order,
                offset: params.offset,
                limit:  params.limit,
                my_var: 'test'
            }
        }
*/

        showColumns:        true,
        showToggle:         false,
        buttonsClass:       'default',

        toolbar:            '#toolbar',

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

        cookie:             true,
        cookieExpire:       '1m',
        cookieIdTable:      'importsTable',
        //cookiesEnabled:     ['bs.table.sortOrder', 'bs.table.sortName', 'bs.table.pageNumber', 'bs.table.pageList', 'bs.table.columns', 'bs.table.searchText', 'bs.table.filterControl'],

        formatShowingRows: function(pageFrom, pageTo, totalRows) {
            return 'Total: ' + totalRows;
        },
        formatRecordsPerPage: function(pageNumber) {
            return pageNumber;
        },
        onPreBody: function(data) {
            if ($('.fixed-table-toolbar > .page-list').length)
                $('.fixed-table-toolbar > .page-list').html($('.pagination-detail > .page-list')).find('.btn-group ').attr('title', 'Rows per page').removeClass('dropup').addClass('dropdown');
            else
                $('.pagination-detail .page-list').insertBefore('.fixed-table-toolbar > .columns').addClass('pull-right').show();

            $('.page-list .dropdown-menu li:last-child a').html('All');
            if ($('.page-list .dropdown-menu li:last-child').prev().find('a').html() == data.length)
                $('.page-list .dropdown-menu li:last-child').prev().remove();
        },
        onPostBody: function(data) {

            // Tooltips
            $('[data-tooltip="true"], .btn-group[title]').tooltip({
                container: 'body',
                delay: {"show": 1000, "hide": 100}
            });

            // Check the total output rows and see if is to hide or to show
            if ($('input[data-field=output_rows]').is(":checked"))
                $('.total_imported small').show();
            else
                $('.total_imported small').hide();

            // Update the Page list with the 'all' option
            $('.page-list .dropdown-menu li:last-child a').html('All');
            if ($('.page-list .dropdown-menu li:last-child').prev().find('a').html() == data.length)
                $('.page-list .dropdown-menu li:last-child').prev().remove();
        },
        onAll: function(name, args) {
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle="popover"]').popover();
        },
        onColumnSwitch: function(field, checked) {
            if(field == 'output_rows') {
                if (checked)
                    $('.total_imported small').show();
                else
                    $('.total_imported small').hide();
            }
        }
    });

});


// FORMATTERS
function totalImportedFormatter(value, row, index) {
    return value + '<small> / ' + row.output_rows + '</small>';
}

function outputRowsFormatter(value, row, index) {
    return value;
}

function timeElapsedFormatter(value, row, index) {
    var time_elapsed = moment.duration(parseInt(value));

    var result = time_elapsed.milliseconds() + ' <small>ms</small>';
    if (time_elapsed.seconds() > 0 || time_elapsed.minutes() > 0)
        result = time_elapsed.seconds() + ' <small>s</small> ' + (time_elapsed.milliseconds() < 100 ? '0' : '') + (time_elapsed.milliseconds() < 10 ? '0' : '') + result;
    if (time_elapsed.minutes() > 0)
        result = time_elapsed.minutes() + ' <small>m</small> ' + (time_elapsed.seconds() < 10 ? '0' : '') + result;

    return result;
}

function actionsFormatter(value, row, index) {
    return [
        '<button class="settings btn btn-sm btn-icon btn-flat btn-default" title="Settings" data-tooltip="true">',
            '<i class="icon wb-settings"></i>',
        '</button>',
        '<button class="log btn btn-sm btn-icon btn-flat btn-default" title="Log" data-tooltip="true">',
            '<i class="icon wb-clipboard"></i>',
        '</button>',
        '<button class="remove btn btn-sm btn-icon btn-flat btn-default" title="Remove" data-tooltip="true">',
            '<i class="icon wb-close"></i>',
        '</button>'
    ].join('');
}


// BULK DELETE
$('#toolbar .bulk-delete').on('click', function() {

    if ($('#importsTable th[data-field="state"]').length) {

        var obj_selected = $('#importsTable').bootstrapTable('getAllSelections');

        delete_ids = obj_selected.map(function(a) {return a.id;});

        if (delete_ids.length) {
            $('#deleteModal').modal('show');
        } else {
            $('#importsTable').bootstrapTable('hideColumn', 'state');
            $('#toolbar .bulk-delete').removeClass('active');
        }

    } else {
        $('#importsTable').bootstrapTable('showColumn', 'state');
        $('#toolbar .bulk-delete').addClass('active');
    }
});


// ACTION BUTTONS
window.actionsEvents = {
    'click .settings': function (e, value, row, index) {
        var settings = JSON.parse(row.settings);

        // Reset
        $('#settings_file_desktop, #settings_url, #settings_ftp, #settings_server_path').hide();

        // Fill the info
        $('#settings_file_source td:eq(1)').html(settings.file_source);

        if (settings.file_source == 'desktop')
            $('#settings_file_desktop td:eq(1)').html(settings.file_desktop).parent().show();

        if (settings.file_source == 'url')
            $('#settings_url td:eq(1)').html(settings.url).parent().show();

        if (settings.file_source == 'ftp') {
            $('#settings_address_url td:eq(1)').html(settings.address_url);
            $('#settings_port td:eq(1)').html(settings.port);
            $('#settings_username td:eq(1)').html(settings.username);
            $('#settings_password td:eq(1)').html(settings.password);
            $('#settings_path td:eq(1)').html(settings.path);
            $('#settings_ftp').show();
        }

        if (settings.file_source == 'server')
            $('#settings_server_path td:eq(1)').html(settings.server_path).parent().show();

        $('#settings_header td:eq(1)').html(settings.header ? 'Yes': 'No');
        $('#settings_delimiter td:eq(1)').html(settings.delimiter);
        $('#settings_enclosure td:eq(1)').html(settings.enclosure);
        $('#settings_encoding td:eq(1)').html(settings.encoding);

        $('#settings_db td:eq(1)').html(settings.db);
        $('#settings_table td:eq(1)').html(settings.table);

        var mapping_html = '';
        for (var i = 0; i < settings.mapping.length; i++)
            mapping_html += '<tr>' +
                             '<td>' + settings.mapping[i].label + '</td>' +
                             '<td>COL#' + settings.mapping[i].csv_column + ' <small>(' + settings.mapping[i].csv_header + ')</small></td>' +
                             '</tr>';

        $('#settings_mapping > td:eq(1) > table > tbody').html(mapping_html);

        $('#settings_filter_row td:eq(1)').html(settings.filter_rows == '' ? 'All' : settings.filter_rows);

        var duplicates_html = '';
        for (var i = 0; i < settings.duplicates.length; i++)
            duplicates_html += (duplicates_html ? ', ' : '') + settings.duplicates[i].label;
        $('#settings_duplicates td:eq(1)').html(duplicates_html);
        $('#settings_actions_duplicates td:eq(1)').html(settings.actions_duplicates);
        $('#settings_actions_rest_create td:eq(1)').html(settings.actions_rest_create);
        $('#settings_actions_rest_delete td:eq(1)').html(settings.actions_rest_delete);

        $('#settingsModal').modal('show');
    },
    'click .log': function (e, value, row, index) {
        window.location.href = 'import-log.php?id=' + row.id;
    },
    'click .remove': function (e, value, row, index) {
        delete_ids = [row.id];
        $('#deleteModal').modal('show');
    }
};

// DELETE
$('#deleteModal').on('click', '.delete', function(e) {
    var $modalDiv = $(e.delegateTarget);
    $modalDiv.addClass('loading');

    $('#importsTable').bootstrapTable('remove', {field: 'id', values: delete_ids});

    $.ajax({
        type: "POST",
        url: "?action=import_list_delete",
        data: {
            'ids': delete_ids
        }
    }).done(function( out ) {
        $modalDiv.modal('hide').removeClass('loading');

        $('#importsTable').bootstrapTable('remove', {field: 'id', values: delete_ids});

    });
});
