var input_data = [];
var total_rows = 0;
var results_class_colors = {
    'Skipped':  'text-info',
    'Inserted': 'text-success',
    'Updated':  'text-primary',
    'Deleted':  'text-warning',
    'Error':    'text-danger',
};

$(function() {

    //==================================================================================================
    //  PROGRESS BAR
    //==================================================================================================

    var percentage = Math.round(100 * total_imported / output_rows);
    $('#review-progress .progress .progress-bar').addClass('no-transition').css({width: percentage + '%'});
    if (percentage == 100)
        $('#review-progress .triangle').addClass('success');
    $('#review-progress .progress .progress-label').html(percentage + '%');


    //==================================================================================================
    //  DETAILS
    //==================================================================================================

    // create the reports table
    var data_header = ['ROW', 'STATUS', 'MESSAGE'];

    var columns_obj = [];
    for (i = 0; i < data_header.length; i++) {
        columns_obj.push({
            'title': data_header[i],
            'class': (i == 1 ? 'status' : (i == 2 ? 'message' : '')),
            'cellStyle': function(value, row, index, field) {
                return {
                    classes: (field == 1 || field == 2) ? results_class_colors[row[1]] : '',
                    //css: {"color": "blue", "font-size": "50px"}
                };
            }
        });
    }

    $('#logDetailsTable').bootstrapTable({
        url:                '?action=getLogDetails&id=' + id_log + '&type=import',
        method:             'get', // get, post

        search:             true,

        sortable:           true,
        sortName:           'id',
        sortOrder:          'desc',

        pagination:         true,
        sidePagination:     'client', // TODO: 'server'
        pageSize:           10,
        pageList:           [10, 20, 50, 100, 1000, 10000],
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

        cookie:                     true,
        cookieExpire:               '1m',
        cookieIdTable:              'logTable',
        //cookiesEnabled:             ['bs.table.sortOrder', 'bs.table.sortName', 'bs.table.pageNumber', 'bs.table.pageList', 'bs.table.columns', 'bs.table.searchText', 'bs.table.filterControl'],

        toolbar: '#toolbar',

        rowStyle: function(row, index) {
          return {
            classes: results_class_colors[row[1]]
            //css: {"color": "blue", "font-size": "50px"}
          };
        },
        formatShowingRows: function(pageFrom, pageTo, totalRows) {
            return 'Total: ' + totalRows;
        },
        formatRecordsPerPage: function(pageNumber) {
            return pageNumber;
        },
        onLoadSuccess: function() {
            if (!$('.fixed-table-toolbar > .page-list').length)
                $('.pagination-detail .page-list').insertBefore('.fixed-table-toolbar > .columns').addClass('pull-right');
        },
        onPreBody: function() {
            if ($('.fixed-table-toolbar > .page-list').length)
                $('.pagination-detail .page-list').remove();
        },
        onPostBody: function(data) {
            // Tooltips
            $('[data-tooltip="true"]').tooltip({
                container: 'body'
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
        onAll: function() {
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle="popover"]').popover();
        }
    });


    //=================================================================================================
    //  DOWNLOAD FILES
    //==================================================================================================

    $('#btn-download-file, #btn-download-settings, #btn-download-details').click(function(e) {
        e.preventDefault();
        $('#downloadFrame').attr('src', "actions.php?action=import_download&id=" + id_log + '&filename=' + $(this).data('filename'));
    });

});