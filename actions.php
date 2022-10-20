<?php

// Execute the action function
if (isset($_POST['action']) and $_POST['action'] != '')
    $action = $_POST['action'];
elseif (isset($_GET['action']) and $_GET['action'] != '')
    $action = $_GET['action'];

if (function_exists($action))
    call_user_func($action);

exit;


// ===================================================================================
//      IMPORT LIST - GET
// ===================================================================================

function import_list_get()
{
    $arr = array();

    $search = isset($_POST['search']) ? $_POST['search'] : '';      // text
    $sort   = isset($_POST['sort']) ? $_POST['sort'] : '';          // id, name
    $order  = isset($_POST['order']) ? $_POST['order'] : '';        // asc, desc
    $offset = isset($_POST['offset']) ? $_POST['offset'] : '';      // offset
    $limit  = isset($_POST['limit']) ? $_POST['limit'] : '';        // limit

    $dir = new DirectoryIterator(IMPORTS_DIR);
    foreach ($dir as $fileinfo) {

        if ($fileinfo->isDir() and !$fileinfo->isDot() and $fileinfo != basename(IMPORTS_TEMP_DIR)) {

            $files = scandir($fileinfo->getPathname());

            // ID Import
            $id = substr($fileinfo->getFilename(), strrpos($fileinfo->getFilename(), '-') + 1);

            // Data
            if (file_exists($fileinfo->getPathname() . '/log.json')) {
                $log = file_get_contents($fileinfo->getPathname() . '/log.json');
                $log = json_decode($log);
            }

            if (isset($log->data) and isset($log->settings)) {
                $data       = $log->data;
                $settings   = $log->settings;

                // Remove miliseconds from date
                $date_begin = substr($data->date_begin, 0, strrpos($data->date_begin, '.'));
                $date_end   = substr($data->date_end, 0, strrpos($data->date_end, '.'));

                $arr[] = array(
                    'id'                => $id,
                    'filename'          => $data->filename,
                    'db'                => $data->db,
                    'output_rows'       => $data->output_rows,
                    'table'             => $data->table,
                    'total_imported'    => $data->total_imported,
                    'total_skipped'     => $data->total_skipped,
                    'total_inserted'    => $data->total_inserted,
                    'total_updated'     => $data->total_updated,
                    'total_deleted'     => $data->total_deleted,
                    'total_error'       => $data->total_error,
                    'date_begin'        => $date_begin,
                    'date_end'          => $date_end,
                    'time_elapsed'      => $data->time_elapsed,
                    'status'            => 'Complete',
                    'settings'          => json_encode($settings)
                );
            }
        }
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit(0);
}


// ===================================================================================
//      IMPORT LIST - DELETE
// ===================================================================================

function import_list_delete()
{
    $error = '';
    $ids = $_POST['ids'];

    if (is_array($ids)) {
        foreach ($ids as $id) {
            // If id is integer
            if (filter_var($id, FILTER_VALIDATE_INT) !== false) {
                // Delete the Import directory
                rrmdir(IMPORTS_DIR . 'import-' . $id);
            }
        }
    }

    if ($error != '') {
        $res["status"] = 'error';
        $res["message"] = 'Error found.';
    } else {
        $res["status"] = 'success';
        $res["message"] = 'Deleted';
    }

    echo json_encode($res);
}


// ===================================================================================
//      IMPORT - GET FILE FROM DESKTOP
// ===================================================================================

function import_getFileFromDesktop()
{
    $uuid = $_POST['uuid'];
    $filename = $_POST['file_desktop'];

    if ($error) {
        $res["status"] = 'error'; // warning, error, success
        $res["message"] = $error;
    } else {
        $res["status"] = 'success';
        $res["message"] = 'File uploaded to the temp directory';
        $res["data"]['filename'] = $filename;
    }
    $res['params']['filename'] = $filename;

    echo json_encode($res);
}


// ===================================================================================
//      IMPORT - GET FILE FROM URL
// ===================================================================================

function import_getFileFromUrl()
{
    $url = $_POST['url'];
    $filename = basename($url);
    $filepath = IMPORTS_TEMP_DIR . session_id() . '/url/' . $filename;

    if (function_exists('curl_init')) {

        set_time_limit(0);

        // Create a temp directory
        if (!is_dir(IMPORTS_DIR))
            mkdir(IMPORTS_DIR, 0755);
        if (!is_dir(IMPORTS_TEMP_DIR))
            mkdir(IMPORTS_TEMP_DIR, 0755);
        if (!is_dir(IMPORTS_TEMP_DIR . session_id()))
            mkdir(IMPORTS_TEMP_DIR . session_id(), 0755);
        if (!is_dir(IMPORTS_TEMP_DIR . session_id() . '/url'))
            mkdir(IMPORTS_TEMP_DIR . session_id() . '/url', 0755);

        //Here is the file we are downloading, replace spaces with %20
        $ch = curl_init(str_replace(" ", "%20", $url));

        if ($ch !== false) {

            //This is the file where we save the information
            $fp = fopen ($filepath, 'w+');

            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            // write curl response to file
            curl_setopt($ch, CURLOPT_FILE, $fp);
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            // get curl response
            curl_exec($ch);

            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($retcode == 0) {
                $error = 'URL not found.';
            } elseif ($retcode != 200) {
                $http_status_codes = array(100 => "Continue", 101 => "Switching Protocols", 102 => "Processing", 200 => "OK", 201 => "Created", 202 => "Accepted", 203 => "Non-Authoritative Information", 204 => "No Content", 205 => "Reset Content", 206 => "Partial Content", 207 => "Multi-Status", 300 => "Multiple Choices", 301 => "Moved Permanently", 302 => "Found", 303 => "See Other", 304 => "Not Modified", 305 => "Use Proxy", 306 => "(Unused)", 307 => "Temporary Redirect", 308 => "Permanent Redirect", 400 => "Bad Request", 401 => "Unauthorized", 402 => "Payment Required", 403 => "Forbidden", 404 => "Not Found", 405 => "Method Not Allowed", 406 => "Not Acceptable", 407 => "Proxy Authentication Required", 408 => "Request Timeout", 409 => "Conflict", 410 => "Gone", 411 => "Length Required", 412 => "Precondition Failed", 413 => "Request Entity Too Large", 414 => "Request-URI Too Long", 415 => "Unsupported Media Type", 416 => "Requested Range Not Satisfiable", 417 => "Expectation Failed", 418 => "I'm a teapot", 419 => "Authentication Timeout", 420 => "Enhance Your Calm", 422 => "Unprocessable Entity", 423 => "Locked", 424 => "Failed Dependency", 424 => "Method Failure", 425 => "Unordered Collection", 426 => "Upgrade Required", 428 => "Precondition Required", 429 => "Too Many Requests", 431 => "Request Header Fields Too Large", 444 => "No Response", 449 => "Retry With", 450 => "Blocked by Windows Parental Controls", 451 => "Unavailable For Legal Reasons", 494 => "Request Header Too Large", 495 => "Cert Error", 496 => "No Cert", 497 => "HTTP to HTTPS", 499 => "Client Closed Request", 500 => "Internal Server Error", 501 => "Not Implemented", 502 => "Bad Gateway", 503 => "Service Unavailable", 504 => "Gateway Timeout", 505 => "HTTP Version Not Supported", 506 => "Variant Also Negotiates", 507 => "Insufficient Storage", 508 => "Loop Detected", 509 => "Bandwidth Limit Exceeded", 510 => "Not Extended", 511 => "Network Authentication Required", 598 => "Network read timeout error", 599 => "Network connect timeout error");
                $error = 'Could not get the file on the URL (' . $http_status_codes[$retcode] . ')';
            } else {
                $error = curl_error($ch);
            }

            curl_close($ch);
            fclose($fp);

        } else {
            $error = 'Cold not open the file.';
        }

    } elseif (allow_url_fopen) {

    }

    if (isset($error) and $error != '') {
        $res["status"] = 'error'; // warning, error, success
        $res["message"] = $error;
    } else {
        $res["status"] = 'success';
        $res["message"] = 'File uploaded to the temp directory';
        $res["data"]['filename'] = $filename;
        $res["data"]['filesize'] = formatBytes(filesize($filepath));
    }

    echo json_encode($res);
}


// ===================================================================================
//      IMPORT - GET FILE FROM FTP
// ===================================================================================

function import_getFileFromFtp()
{
    $error = '';

    $address_url = $_POST['address_url'];
    $port        = $_POST['port'];
    $username    = $_POST['username'];
    $password    = $_POST['password'];
    $path        = $_POST['path'];

    $filename = basename($path);
    $filepath = IMPORTS_TEMP_DIR . session_id() . '/ftp/' . $filename;

    set_time_limit(0);

    // Create a temp directory
    if (!is_dir(IMPORTS_TEMP_DIR . session_id()))
        mkdir(IMPORTS_TEMP_DIR . session_id() ,0755);
    if (!is_dir(IMPORTS_TEMP_DIR . session_id() . '/ftp'))
        mkdir(IMPORTS_TEMP_DIR . session_id() . '/ftp' ,0755);

    // Opens an FTP connection
    $ftp = @ftp_connect($address_url, $port);

    if (!$ftp)
        $error = 'Couldn\'t connect to ' . $address_url . ' on port ' . $port;

    if ($error and function_exists('ftp_ssl_connect')) {
        if ($ftp = @ftp_ssl_connect($address_url, $port))
            $error = '';
        else
            $error = 'Couldn\'t connect to ssl ' . $address_url . ' on port ' . $port;
    }

    // try to login
    if (!$error and !@ftp_login($ftp, $username, $password))
        $error = 'Couldn\'t connect with that username and that password';

    if (!$error)
        ftp_pasv($ftp, true);

        // Download the file
    if (!$error and !@ftp_get($ftp, $filepath, $path, FTP_BINARY)) {
        $error = error_get_last();
        $error = substr($error['message'], strpos($error['message'], ':') + 2);
    }

    // close the connection
    if ($ftp)
        ftp_close($ftp);

    if ($error) {
        $res["status"] = 'error'; // warning, error, success
        $res["message"] = $error;
    } else {
        $res["status"] = 'success';
        $res["message"] = 'File uploaded to the temp directory';
        $res["data"]['filename'] = $filename;
        $res["data"]['filesize'] = formatBytes(filesize($filepath));
    }

    echo json_encode($res);
}


// ===================================================================================
//      IMPORT - GET FILE FROM SERVER
// ===================================================================================

function import_getFileFromServer()
{
    $error = '';
    $server_path = $_POST['server_path'];
    $filename = basename($server_path);
    $filepath = IMPORTS_TEMP_DIR . session_id() . '/server/' . $filename;

    // Create a temp directory
    if (!is_dir(IMPORTS_TEMP_DIR . session_id()))
        mkdir(IMPORTS_TEMP_DIR . session_id(), 0755);
    if (!is_dir(IMPORTS_TEMP_DIR . session_id() . '/server'))
        mkdir(IMPORTS_TEMP_DIR . session_id() . '/server', 0755);

    if (!file_exists($server_path) or !is_file($server_path))
        $error = 'File doesn\'t exist';

    if (!$error and !copy($server_path, $filepath))
        $error = "Failed to copy file";

    if ($error) {
        $res["status"] = 'error'; // warning, error, success
        $res["message"] = $error;
    } else {
        $res["status"] = 'success';
        $res["message"] = 'File uploaded to the temp directory';
        $res["data"]['filename'] = $filename;
        $res["data"]['filesize'] = formatBytes(filesize($filepath));
    }

    echo json_encode($res);
}


// ===================================================================================
//      IMPORT - GET CSV SETTINGS
// ===================================================================================

function import_getCsvSettings()
{
    global $_file_encoding_list;

    $file_source = $_POST['file_source'];   // desktop, url, ftp, server
    $uuid = $_POST['uuid'];
    $file = $_POST['file_' . $file_source];

    if ($file_source == 'desktop')
        $file_source .= '/files/' . $uuid;

    $file_path = IMPORTS_TEMP_DIR . session_id() . '/' . $file_source . '/' . $file;

    $csv = new parseCSV();
    $csv->delimiter = $_POST['delimiter'];
    $csv->enclosure = $_POST['enclosure'];
    $csv->encoding  = isset($_POST['encoding']) ? $_POST['encoding'] : '';
    $csv->auto_encoding = $_file_encoding_list;

    $csv->getCSVSettings($file_path);

    if (isset($error)) {
        $res["status"] = 'error'; // warning, error, success
        $res["message"] = $error;
    } else {
        $res["status"]                  = 'success';
        $res["message"]                 = 'Got CSV settings';
        $res["data"]["heading"]         = $csv->heading;
        $res["data"]["delimiter"]       = $csv->delimiter;
        $res["data"]["enclosure"]       = $csv->enclosure;
        $res["data"]["encoding"]        = $csv->encoding;
        $res["data"]["cols"]            = $csv->total_cols;
    }

    echo json_encode($res);
}


// ===================================================================================
//      IMPORT - GET INPUT
// ===================================================================================

function import_getInput()
{
    global $_import_rows_per_batch_method, $_import_rows_per_batch;

    $_SESSION['file_position'] = 0;
    $_SESSION['row_position'] = 0;

    $csv = new parseCSV();
    $csv->heading = (isset($_POST['header']) and $_POST['header']) ? true : false;
    $csv->delimiter = $_POST['delimiter'];
    $csv->enclosure = $_POST['enclosure'];
    $csv->encoding($_POST['encoding'], 'UTF-8');

    // Rows to import per batch
    if ($_import_rows_per_batch_method == 'auto')
        $csv->max_bytes = return_bytes(MEMORY_LIMIT);
    else
        $csv->max_rows = $_import_rows_per_batch;

    $csv->file_position = $_GET['file_position'];

    $file_path = IMPORTS_TEMP_DIR . session_id() . '/' . ($_POST['file_source'] == 'desktop' ? $_POST['file_source'] . '/files/' . $_POST['uuid'] : $_POST['file_source']) . '/' . $_POST['file_' . $_POST['file_source']];

    $csv->parseCSV($file_path);

    if (isset($error)) {
        $res["status"] = 'error'; // warning, error, success
        $res["message"] = $error;
    } else {
        if ($csv->file_eof) {
            $res["status"] = 'success';
            $res["message"] = 'Input data read with success';
        } else {
            $res["status"] = 'partial content';
            $res["message"] = 'Partial content read successfuly';
        }

        if ($csv->error == 2)
            //$res["warning_message"] = $csv->error_info;
            $res["warning_message"] = 'Error found on the encoding. Please make sure you did select the right file encoding.';
        elseif ($csv->different_columns_number)
            $res["warning_message"] = 'Your CSV has different number of columns for different rows. Are you sure you have chosen the right delimiter and/or enclosure?';
        else
            $res["warning_message"] = '';

        $res["data"]["header"]          = $csv->titles;
        $res["data"]["body"]            = $csv->data;
        $res["data"]["heading"]         = $csv->heading;
        $res["data"]["delimiter"]       = $csv->delimiter;
        $res["data"]["enclosure"]       = $csv->enclosure;
        $res["data"]["cols"]            = isset($csv->data[0]) ? count($csv->data[0]) : count($csv->titles);
        $res["data"]["rows"]            = count($csv->data);
        $res["data"]["file_position"]   = $csv->file_position;
        $res["data"]["file_size"]       = $csv->file_size;
    }

    echo json_encode($res);
}


// ===================================================================================
//      IMPORT - GET OUTPUT
// ===================================================================================

function import_getOutput()
{
    global $_import_rows_per_batch_method, $_import_rows_per_batch, $_databases;

    $csv = new parseCSV();
    $csv->heading = (isset($_POST['header']) and $_POST['header']) ? true : false;
    $csv->delimiter = $_POST['delimiter'];
    $csv->enclosure = $_POST['enclosure'];
    //$csv->encoding($_POST['encoding'], $db_encoding);

    $csv->filter = $_POST['filter_rows'];
    $csv->conditions = isset($_POST['conditions']) ? $_POST['conditions'] : null;

    // Rows to import per batch
    if ($_import_rows_per_batch_method == 'auto')
        $csv->max_bytes = return_bytes(MEMORY_LIMIT);
    else
        $csv->max_rows = $_import_rows_per_batch;

    $csv->file_position = $_GET['file_position'];
    $csv->row_position = $_GET['row_position'];

    $csv->mapping = $_POST['mapping'];
    //$csv->mapping_maitain_keys = false;

    $file_path = IMPORTS_TEMP_DIR . session_id() . '/' . ($_POST['file_source'] == 'desktop' ? $_POST['file_source'] . '/files/' . $_POST['uuid'] : $_POST['file_source']) . '/' . $_POST['file_' . $_POST['file_source']];

    $csv->parseCSV($file_path);

    if (isset($error)) {
        $res["status"] = 'error'; // warning, error, success
        $res["message"] = $error;
    } else {
        if ($csv->file_eof) {
            $res["status"] = 'success';
            $res["message"] = 'Import success';
        } else {
            $res["status"] = 'partial content';
            $res["message"] = 'Partial content read successfuly';
        }
        //$res["data"]["header"]          = $arr_mapped_columns['label'];
        $res["data"]["body"]            = $csv->data;
        $res["data"]["heading"]         = $csv->heading;
        $res["data"]["delimiter"]       = $csv->delimiter;
        $res["data"]["enclosure"]       = $csv->enclosure;
        $res["data"]["cols"]            = isset($csv->data[0]) ? count($csv->data[0]) : count($csv->titles);
        $res["data"]["rows"]            = count($csv->data);
        $res["data"]["file_position"]   = $csv->file_position;
        $res["data"]["file_size"]       = $csv->file_size;
    }

    echo json_encode($res);
}


// ===================================================================================
//      IMPORT - GET TABLES
// ===================================================================================

function import_getTables()
{
    global $_databases;

    $db_id = $_POST['db_id'];

    $core = new Core($_databases);
    $tables = $core->getGoodTables($db_id);

    foreach ($tables as $key => $table) {
        $res["data"]["ids"][] = $key;
        $res["data"]["names"][] = $table['properties'][0]['name'];
        $res["data"]["labels"][] = $table['label'];
    }

    if (!$res) {
        $res["status"] = 'error'; // warning, error, success
        $res["message"] = 'Could not get the tables.';
    } else {
        $res["status"] = 'success';
        $res["message"] = 'Data selected from database';
    }

    echo json_encode($res);
}


// ===================================================================================
//      IMPORT - GET COLUMNS
// ===================================================================================

function import_getColumns()
{
    getColumns();
}


// ===================================================================================
//      IMPORT - SAVE LOG
// ===================================================================================

function import_saveLog()
{
    $id_log = isset($_GET['id']) ? $_GET['id'] : '';
    $directory = 'import-' . $id_log . '/';

    $data_json = json_encode(array(
        'data' => array(
            'filename'          => $_POST['filename'],
            'input_cols'        => $_POST['input_cols'],
            'input_rows'        => $_POST['input_rows'],
            'size'              => $_POST['size'],
            'db'                => $_POST['db'],
            'table'             => $_POST['table'],
            'output_cols'       => $_POST['output_cols'],
            'output_rows'       => $_POST['output_rows'],
            'time_elapsed'      => $_POST['time_elapsed'],
            'date_begin'        => $_POST['date_begin'],
            'date_end'          => $_POST['date_end'],
            'total_imported'    => $_POST['total_imported'],
            'total_skipped'     => $_POST['total_skipped'],
            'total_inserted'    => $_POST['total_inserted'],
            'total_updated'     => $_POST['total_updated'],
            'total_deleted'     => $_POST['total_deleted'],
            'total_error'       => $_POST['total_error']
        )
    ));


    // Save the settings used
    $fp = fopen(IMPORTS_DIR . $directory . '/log.json', 'c');

    fseek($fp, -1, SEEK_END);
    $data_log = ',' . substr($data_json, 1);

    fwrite($fp, $data_log);
    fclose($fp);
}


// ===================================================================================
//      IMPORT - GET ID
// ===================================================================================

function import_getId()
{
    $id = setDirectory('import');

    if ($id === false) {
        $res["status"] = 'error';
        $res["message"] = 'Error found.';
    } else {
        $res["status"] = 'success';
        $res["data"] = array();
        $res["data"]['id'] = $id;
    }

    exit(json_encode($res));
}


// ===================================================================================
//      IMPORT
// ===================================================================================

function import()
{
    global $_databases, $_import_rows_per_batch_method, $_import_rows_per_batch, $_log_csv_delimiter, $_log_csv_enclosure, $_log_csv_escape, $_log_csv_encoding;

    //ob_start();

    $total_skipped = 0;
    $total_inserted = 0;
    $total_updated = 0;
    $total_deleted = 0;
    $total_error = 0;
    $total_duplicates = 0;
    $arr_all_id_duplicates = array();
    $arr_out_body = array();

    $db = $_POST['db'];
    $table = $_POST['table'];
    $mapping = $_POST['mapping'];
    $duplicates = $_POST['duplicates'];

    $file_name = $_POST['file_' . $_POST['file_source']];
    $file_path = IMPORTS_TEMP_DIR . session_id() . '/' . ($_POST['file_source'] == 'desktop' ? $_POST['file_source'] . '/files/' . $_POST['uuid'] : $_POST['file_source']) . '/' . $_POST['file_' . $_POST['file_source']];

    // CREATE THE IMPORT DIRECTORY
    $id_import = (is_int((int)$_GET['id']) and is_numeric($_GET['id'])) ? $_GET['id'] : 0;

    // The next good directory
    $directory = 'import-' . $id_import;

    // CSV
    $csv = new parseCSV();
    $csv->heading = (isset($_POST['header']) and $_POST['header']) ? true : false;
    $csv->delimiter = $_POST['delimiter'];
    $csv->enclosure = $_POST['enclosure'];
    //$csv->encoding($_POST['encoding'], $db_encoding);

    // On the first time the file is read
    if ($id_import > 0 and $_GET['file_position'] == 0 and $_GET['row_position'] == 0 and $_GET['retry'] == 0) {

        // Save the CSV from the tmp directory to the new location
        if (rename($file_path, IMPORTS_DIR . $directory . '/' . $file_name))
            rrmdir(IMPORTS_TEMP_DIR . session_id());
        else
            $error = 'Error copying from the tmp to the directory';

        // Save the settings used
        $fp = fopen(IMPORTS_DIR . $directory . '/log.json', 'w');

        $settings_array = array();

        $settings_array['file_source']          = $_POST['file_source'];

        if ($_POST['file_source'] == 'desktop') {
            $settings_array['file_desktop']     = $_POST['file_desktop'];
        } elseif ($_POST['file_source'] == 'url') {
            $settings_array['url']              = $_POST['url'];
        } elseif ($_POST['file_source'] == 'ftp') {
            $settings_array['address_url']      = $_POST['address_url'];
            $settings_array['port']             = $_POST['port'];
            $settings_array['username']         = $_POST['username'];
            $settings_array['password']         = $_POST['password'];
            $settings_array['path']             = $_POST['path'];
        } elseif ($_POST['file_source'] == 'server') {
            $settings_array['server_path']      = $_POST['server_path'];
        }

        $settings_array['header']               = isset($_POST['header']) ? $_POST['header'] : '';
        $settings_array['delimiter']            = $_POST['delimiter'];
        $settings_array['enclosure']            = $_POST['enclosure'];
        $settings_array['encoding']             = $_POST['encoding'];
        $settings_array['db']                   = $_POST['db'];
        $settings_array['table']                = $_POST['table'];

        $csv_titles = $csv->getTitles(IMPORTS_DIR . $directory . '/' . $file_name);
        $mapping_info = getMappedInfo($db, $table, $csv_titles, $_POST['mapping'], true);
        $settings_array['mapping']              = $mapping_info;

        $settings_array['filter_rows']          = $_POST['filter_rows'];

        $duplicate_info = array();
        if ($_POST['duplicates'] != '') {
            foreach ($mapping_info as $info) {
                if ($info['index'] == $_POST['duplicates'])
                    $duplicate_info = array(
                        'table' => $info['table'],
                        'index' => $info['index'],
                        'name'  => $info['name'],
                        'label' => $info['label']
                    );
            }
        }
        $settings_array['duplicates']           = $duplicate_info;
        $settings_array['actions_duplicates']   = $_POST['actions_duplicates'];
        $settings_array['actions_rest_create']  = isset($_POST['actions_rest_create']) ? $_POST['actions_rest_create'] : '';
        $settings_array['actions_rest_delete']  = isset($_POST['actions_rest_delete']) ? $_POST['actions_rest_delete'] : '';

        $settings_json = json_encode(array(
            'settings' => $settings_array
        ));

        fwrite($fp, $settings_json);
        fclose($fp);
    }

    if ($id_import > 0) {

        $csv->filter = $_POST['filter_rows'];
        $csv->conditions = isset($_POST['conditions']) ? $_POST['conditions'] : null;

        if ($_GET['retry'] > 0) {
            $max_bytes_retry = array(return_bytes(MEMORY_LIMIT), 1048576, 1024);
            $max_rows_retry = array(100, 10, 1);
        }

        // Rows to import per batch
        if ($_import_rows_per_batch_method == 'auto')
            $csv->max_bytes = return_bytes(MEMORY_LIMIT);
        else
            $csv->max_rows = $_import_rows_per_batch;

        // Set the last read position
        $csv->file_position = $_GET['retry'] > 0 ? $_SESSION['file_position'] : $_GET['file_position'];
        $csv->row_position = $_GET['retry'] > 0 ? $_SESSION['row_position'] : $_GET['row_position'];

        // Set the mapping for the CSV
        $csv->mapping = $_POST['mapping'];
        $csv->mapping_maitain_keys = true;


        // PARSE CSV
        $csv->parseCSV(IMPORTS_DIR . $directory . '/' . $file_name);

        // Mapping for the DB
        $core = new Core($_databases);
        $core->setTable($table, $db);
        $core->setMapping($mapping);

        // Row position
        $fp_row_position = fopen(IMPORTS_TEMP_DIR . 'row_position-' . $id_import . '.tmp', 'w');

        // Log details header
        $fp_log_details = fopen(IMPORTS_DIR . $directory . '/log_details.csv', 'a');
        $arr_out_header = array('ROW', 'STATUS', 'MESSAGE');
        $arr_mapped_columns = $core->getMappedColumns();
        foreach ($arr_mapped_columns['label'] as $value)
            $arr_out_header[] = $value;
        if (version_compare(PHP_VERSION, '5.5.4') >= 0)
            fputcsv($fp_log_details, $arr_out_header, $_log_csv_delimiter, $_log_csv_enclosure, $_log_csv_escape);
        else
            fputcsv($fp_log_details, $arr_out_header, $_log_csv_delimiter, $_log_csv_enclosure);

        // Values
        $i = 0;
        foreach ($csv->data as $key => $arr_values) {

            if ($duplicates != '') {

                // Duplicates
                $arr_id_duplicates = $core->getDuplicates($duplicates, $arr_values);

                if ($arr_id_duplicates) {

                    if ($_POST['actions_duplicates'] == 'skip') {
                        $res["status"] = 'skipped';
                        $res["message"] = 'Skipped a duplicate record';
                        $total_skipped++;
                    } elseif ($_POST['actions_duplicates'] == 'insert') {
                        $res = $core->insert($arr_values);
                    } elseif ($_POST['actions_duplicates'] == 'update') {
                        $res = $core->update($arr_values, $arr_id_duplicates);
                        $arr_id_duplicates = $core->getDuplicates($duplicates, $arr_values);
                    } elseif ($_POST['actions_duplicates'] == 'delete') {
                        $res = $core->delete($arr_id_duplicates);
                    }

                    // Add the duplicates ids to the list of ids not to delete
                    $arr_all_id_duplicates = array_merge($arr_all_id_duplicates, $arr_id_duplicates);

                    $total_duplicates++;

                } else {
                    if (isset($_POST['actions_rest_create']) and $_POST['actions_rest_create']) {
                        $res = $core->insert($arr_values);

                        $arr_id_duplicates = $core->getDuplicates($duplicates, $arr_values);

                        // Add the inserted id to the list of ids not to delete
                        $arr_all_id_duplicates = array_merge($arr_all_id_duplicates, $arr_id_duplicates);
                    } else {
                        $res["status"] = 'skipped';
                        $res["message"] = 'No action for the newly records present in your file';
                        $total_skipped++;
                    }
                }

            } else {

                // Insert
                $res = $core->insert($arr_values);
            }

            if ($res["status"] == 'inserted')
                $total_inserted++;
            elseif ($res["status"] == 'updated')
                $total_updated++;
            elseif ($res["status"] == 'deleted')
                $total_deleted++;
            elseif ($res["status"] == 'error')
                $total_error++;

            $row_position = $_GET['row_position'] + $i + 1;

            $status_translate = array(
                'inserted'=>'Inserted',
                'updated'=>'Updated',
                'deleted'=>'Updated',
                'error'=>'Error',
            );

            // Columns imported with an extra status and message column
            $arr_out_body[$i] = array_merge(array($row_position, $status_translate[$res["status"]], $res["message"]), $core->getMappedValues($arr_values));

            // Log details
            if (version_compare(PHP_VERSION, '5.5.4') >= 0)
                fputcsv($fp_log_details, array_merge(array($row_position, $status_translate[$res["status"]], $res["message"]), $core->getMappedValues($arr_values)), $_log_csv_delimiter, $_log_csv_enclosure, $_log_csv_escape);
            else
                fputcsv($fp_log_details, array_merge(array($row_position, $status_translate[$res["status"]], $res["message"]), $core->getMappedValues($arr_values)), $_log_csv_delimiter, $_log_csv_enclosure);

/*
            // ANOTHER APPROACH
            header_remove('Set-Cookie');
            session_write_close();
            session_start();
            $_SESSION['file_position'] = $csv->file_position;
            $_SESSION['row_position'] = $_GET['row_position'] + $i + 1;
            session_write_close();
*/

            // Progress
            if ($fp_row_position) {
                fseek($fp_row_position, 0);
                fwrite ($fp_row_position, $_GET['row_position'] + $i + 1);
                fflush($fp_row_position);
            }

            $i++;

        }

        fclose($fp_row_position);
        fclose($fp_log_details);

        // If the option "Delete records that are no longer present in your file" was selected
        if (isset($_POST['actions_rest_delete']) and $_POST['actions_rest_delete']) {
            if ($arr_all_id_duplicates)
                $res = $core->deleteNotIn($arr_all_id_duplicates);
        }

        // Columns names
        $arr_mapped_columns = $core->getMappedColumns();
        $arr_out_header = array_merge(array('ROW', 'STATUS', 'MESSAGE'), $arr_mapped_columns['label']);

    }

    if (isset($error)) {
        $res["status"] = 'error'; // warning, error, success
        $res["message"] = $error;
    } else {
        if ($csv->file_eof) {
            $res["status"] = 'success';
            $res["message"] = 'Import success';
        } else {
            $res["status"] = 'partial content';
            $res["message"] = 'Partial content read successfuly';
        }
        $res["data"] = array();
        $res["data"]["header"] = $arr_out_header;
        $res["data"]["body"] = $arr_out_body;
        $res["data"]["total_skipped"] = $total_skipped;
        $res["data"]["total_inserted"] = $total_inserted;
        $res["data"]["total_updated"] = $total_updated;
        $res["data"]["total_deleted"] = $total_deleted;
        $res["data"]["total_error"] = $total_error;
        $res["data"]["total_duplicates"] = $total_duplicates;
        $res["data"]["id_import"] = $id_import;
        $res["data"]["file_position"] = $csv->file_position;
        $res["data"]["row_position"] = $csv->row_position;
    }

    //ob_end_flush();

    echo json_encode($res);
}


// ===================================================================================
//      IMPORT DOWNLOAD
// ===================================================================================

function import_download()
{
    include_once('config/config.php');

    $id_import = $_REQUEST['id'];
    $filename = $_REQUEST['filename'];
    $file_path_name = IMPORTS_DIR . 'import-' . $id_import . '/' . $filename;

    if (file_exists($file_path_name)) {

        $content = file_get_contents($file_path_name);
        header('Content-Description: File Transfer');
        header("Cache-Control: ");
        header("Pragma: ");
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");

        ob_end_clean();
        ob_start();
        echo $content;
        ob_end_flush();
    }

    exit;
}


// ===================================================================================
//      EXPORT LIST - GET
// ===================================================================================

function export_list_get()
{
    $arr = array();

    $search = isset($_POST['search']) ? $_POST['search'] : '';      // text
    $sort   = isset($_POST['sort']) ? $_POST['sort'] : '';          // id, name
    $order  = isset($_POST['order']) ? $_POST['order'] : '';        // asc, desc
    $offset = isset($_POST['offset']) ? $_POST['offset'] : '';      // offset
    $limit  = isset($_POST['limit']) ? $_POST['limit'] : '';        // limit

    $dir = new DirectoryIterator(EXPORTS_DIR);
    foreach ($dir as $fileinfo) {

        if ($fileinfo->isDir() and !$fileinfo->isDot() and $fileinfo != basename(EXPORTS_TEMP_DIR)) {

            $files = scandir($fileinfo->getPathname());

            // Id
            $id = substr($fileinfo->getFilename(), strrpos($fileinfo->getFilename(), '-') + 1);

            // Data
            if (file_exists($fileinfo->getPathname() . '/log.json')) {
                $log = file_get_contents($fileinfo->getPathname() . '/log.json');
                $log = json_decode($log);
            }

            if (isset($log->data) and isset($log->settings)) {

                $data = $log->data;
                $settings = $log->settings;

                // Remove miliseconds from date
                $date_begin = substr($data->date_begin, 0, strrpos($data->date_begin, '.'));
                $date_end   = substr($data->date_end, 0, strrpos($data->date_end, '.'));

                 $arr[] = array(
                    'id'                => $id,
                    'filename'          => $data->filename,
                    'db'                => $data->db,
                    'table'             => $data->table,
                    'output_rows'       => $data->output_rows,
                    'output_cols'       => $data->output_cols,
                    'total_exported'    => $data->total_exported,
                    'total_success'     => $data->total_success,
                    'total_error'       => $data->total_error,
                    'date_begin'        => $date_begin,
                    'date_end'          => $date_end,
                    'time_elapsed'      => $data->time_elapsed,
                    'status'            => 'Complete',
                    'settings'          => json_encode($settings)
                );

            }


        }
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit(0);
}


// ===================================================================================
//      EXPORT LIST - DELETE
// ===================================================================================

function export_list_delete()
{
    $error = '';
    $ids = $_POST['ids'];

    if (is_array($ids)) {

        foreach ($ids as $id) {

            // If id is integer
            if (filter_var($id, FILTER_VALIDATE_INT) !== false) {

                // Delete the Import directory
                rrmdir(EXPORTS_DIR . 'export-' . $id);

            }

        }

    }

    if ($error != '') {
        $res["status"] = 'error';
        $res["message"] = 'Error found.';
    } else {
        $res["status"] = 'success';
        $res["message"] = 'Deleted';
    }

    echo json_encode($res);
}


// ===================================================================================
//      EXPORT - GET INPUT
// ===================================================================================

function export_getInput()
{
    global $_databases;

    $db_id      = isset($_GET['db']) ? $_GET['db'] : '';
    $table_id   = isset($_GET['table']) ? $_GET['table'] : '';

    $search     = isset($_GET['search']) ? $_GET['search'] : '';      // text
    $sort       = isset($_GET['sort']) ? $_GET['sort'] : '';          // id, name
    $order      = isset($_GET['order']) ? $_GET['order'] : '';        // asc, desc
    $offset     = isset($_GET['offset']) ? $_GET['offset'] : 0;      // offset
    $limit      = isset($_GET['limit']) ? $_GET['limit'] : 10;        // limit

    // SET DB & TABLE
    $core = new Core($_databases);
    $core->setTable($table_id, $db_id);

    $table_name = $core->dbs_structure[$db_id]['tables'][$table_id]['properties'][0]['name'];

    // COLUMNS
    $cols = $core->getColumns();
    $query_col = '';
    $query_where = '';
    $query_order = '';
    for ($i = 0; $i < count($cols['name']); $i++) {
        $query_col .= ($query_col ? ', ' : '') . '`' . $cols['name'][$i] . '` as `' . $cols['label'][$i] . '`';
        if ($search)
            $query_where .= ($query_where ? " OR " : " WHERE ") . "`" . $cols['name'][$i] . "` LIKE '%" . $search . "%'";
        if ($sort != '' and $sort == $i)
            $query_order .= " ORDER BY `" . $cols['name'][$i] . "` " . $order;
    }

    // LIMIT
    $query_limit = ' LIMIT ' . $offset . ',' . $limit;

    // QUERY
    $query = "SELECT " . $query_col . " FROM `" . $table_name . "`" . $query_where . $query_order . $query_limit;
    $data = $core->db->get_results($query, ARRAY_N);

    $total_rows = $core->db->get_var("SELECT COUNT(*) FROM `" . $table_name . "`" . $query_where);

    header('Content-Type: application/json; charset=utf-8');

    // TODO: We can pass only the needed records
    echo json_encode(
        array(
            'total' => $total_rows,
            'rows'  => $data
        ),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    exit(0);
}


// ===================================================================================
//      EXPORT - GET OUTPUT
// ===================================================================================

function export_getOutput()
{
    $search  = isset($_GET['search']) ? $_GET['search'] : '';      // text
    $sort    = isset($_GET['sort']) ? $_GET['sort'] : '';          // id, name
    $order   = isset($_GET['order']) ? $_GET['order'] : '';        // asc, desc
    $offset  = isset($_GET['offset']) ? $_GET['offset'] : 0;       // offset
    $limit   = isset($_GET['limit']) ? $_GET['limit'] : 10;        // limit

    $db_id       = isset($_GET['db']) ? $_GET['db'] : '';
    $table_id    = isset($_GET['table']) ? $_GET['table'] : '';
    $mapping     = isset($_GET['mapping']) ? $_GET['mapping'] : '';
    $filter_rows = isset($_GET['filter_rows']) ? trim($_GET['filter_rows']) : '';
    $conditions  = isset($_GET['conditions']) ? $_GET['conditions'] : null;

    $conditions_value = array();
    if ($conditions) {
        $i=0;
        foreach ($conditions as $item) {
            $conditions_value[$i][] = $item['value'];
            $i++;
            $i = $i >= 4 ? $i = 0 : $i;
        }
    }

    $output = _export_get_output_data($db_id, $table_id, $mapping, $search, $sort, $order, $offset, $limit, $filter_rows, $conditions_value);

    header('Content-Type: application/json; charset=utf-8');

    // TODO: We can pass only the needed records
    echo json_encode(
        array(
            'total' => $output['total_rows'],
            'rows'  => $output['data']
        ),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    exit(0);
}


// ===================================================================================
//      EXPORT - GET OUTPUT DATA
//      used on export_getOutput() and export()
// ===================================================================================

function _export_get_output_data($db_id, $table_id, $mapping, $search, $sort, $order, $offset, $limit, $filter_rows, $conditions)
{
    global $_databases;

    // SET DB & TABLE
    $core = new Core($_databases);
    $core->setTable($table_id, $db_id);
    $arr_columns = $core->getColumns();

    $table_name = $core->dbs_structure[$db_id]['tables'][$table_id]['properties'][0]['name'];

    // MAPPING
    $query_col = '';
    $query_where = '';
    $query_order = '';
    for ($i = 0; $i < count($mapping); $i++) {
        if (trim($mapping[$i]) != '')
            $query_col .= ($query_col ? ', ' : '') . '`' . $arr_columns['name'][$mapping[$i]] . '`';
        else
            $query_col .= ($query_col ? ', ' : '') . "''";

        if ($search)
            $query_where .= ($query_where ? " OR " : "") . "`" . $arr_columns['name'][$mapping[$i]] . "` LIKE '%" . $search . "%'";

        if ($sort != '' and $sort == $i)
            $query_order .= " ORDER BY `" . $arr_columns['name'][$mapping[$i]] . "` " . $order;
    }
    if($query_where)
        $query_where = ' WHERE (' . $query_where . ')';

    // CONDITIONS
    if($conditions){

        // Build an array with the 4 arguments (logical operator, field column, operator, value)
        $conditions = array_map(null, $conditions[0], $conditions[1], $conditions[2], $conditions[3]);

        $sub_query_where = '';
        $sub_query_where_col = '';
        foreach( $conditions as $condition ) {

            $logical =  $condition[0];
            $field =    $condition[1];
            $op  =      $condition[2];
            $value =    $condition[3];

            if ( $field != '' ) {

                if ( $op == 'CONTAINS') {
                    $op_value = " LIKE '%" . $value . "%'";
                } elseif ( $op == 'NOT CONTAINS') {
                    $op_value = " NOT LIKE '%" . $value . "%'";
                } else {
                    $op_value = " " . $op . " '" . $value . "'";
                }

                $sub_query_where .= ($sub_query_where ? " " . $logical . " " : "") . "`" . $arr_columns['name'][$field] . "`" . $op_value;

            }
        }

        if($sub_query_where)
            $query_where = ($query_where ? $query_where . ' AND (' : ' WHERE (') . $sub_query_where . ')';

    }

    // LIMIT
    $query_limit = '';
    if ($offset != '' and $limit != '' and $offset >= 0 and $limit >= 0)
        $query_limit = ' LIMIT ' . $offset . ',' . $limit;

    if ($filter_rows == '') {

        $query = "SELECT " . $query_col . " FROM `" . $table_name . "`" . $query_where . $query_order . $query_limit;

        $query_total = "SELECT COUNT(*) FROM `" . $table_name . "`" . $query_where;

    } else {

        // FILTER
        $filter = explode(',', str_replace(' ', '', $filter_rows));

        $query_filter_in = '';
        $query_filter_between = '';
        foreach ($filter as $value) {
            if (ctype_digit($value)) {
                $query_filter_in .= $query_filter_in ? ',' . $value : '(`@#@row_number@#@` IN (' . $value;
            } elseif (strpos($value, '-') !== false) {
                $conditions = explode('-', $value);
                $query_filter_between_aux = '';
                if (count($conditions) == 2 and (ctype_digit($conditions[0]) or $conditions[0] == '') and (ctype_digit($conditions[1]) or $conditions[1] == ''))
                    $query_filter_between .= $query_filter_between ? ' OR ' : '';
                    if ($conditions[0])
                        $query_filter_between_aux .= '(`@#@row_number@#@` >= ' . $conditions[0];
                    if ($conditions[1])
                        $query_filter_between_aux .= ($query_filter_between_aux ? ' AND ' : '(') . '`@#@row_number@#@` <= ' . $conditions[1];
                    $query_filter_between .= $query_filter_between_aux . ')';
            } else {
                $error_info[] = 'Error on filter expression: ' . $value;
            }
        }

        if ($query_filter_in or $query_filter_between) {
            $query_filter = $query_filter_in . ($query_filter_in ? '))' : '') . (($query_filter_in and $query_filter_between) ? ' OR ' : '') . $query_filter_between;

            $query_sub = "FROM (
                        SELECT l.*, @curRow := @curRow + 1 AS `@#@row_number@#@`
                        FROM (
                            SELECT * FROM `" . $table_name . "`
                        ) l
                        JOIN (
                            SELECT @curRow := 0
                        ) r
                    ) t
                    " . ($query_where ? $query_where . ' AND (' . $query_filter . ')' : ' WHERE ' . $query_filter);

            $query = "SELECT " . $query_col . " " . $query_sub . $query_limit;

            $query_total = "SELECT COUNT(*) " . $query_sub;
        }

    }

    $data = $core->db->get_results($query, ARRAY_N);

    $total_rows = $core->db->get_var($query_total);

    return array(
        'data' => $data,
        'total_rows' => $total_rows
    );
}


// ===================================================================================
//      IMPORT - GET TABLES
// ===================================================================================

function export_getTables()
{
    global $_databases;
    $db_id = $_POST['db_id'];

    $core = new Core($_databases);
    $tables = $core->getGoodTables($db_id);

    foreach ($tables as $key => $table) {
        $res["data"]["ids"][] = $key;
        $res["data"]["names"][] = $table['properties'][0]['name'];
        $res["data"]["labels"][] = $table['label'];
    }

    if (!isset($res)) {
        $res["status"] = 'error'; // warning, error, success
        $res["message"] = 'Error: Could not get the tables or no tables defined.';
    } else {
        $res["status"] = 'success';
        $res["message"] = 'Data selected from database';
    }

    echo json_encode($res);
}


// ===================================================================================
//      EXPORT - GET MAPPING TABLE
// ===================================================================================

function export_getColumns()
{
    getColumns();
}


// ===================================================================================
//      EXPORT - SAVE LOG
// ===================================================================================

function export_saveLog()
{
    $id_log = isset($_GET['id']) ? $_GET['id'] : '';
    $directory = 'export-' . $id_log . '/';

    $data_json = json_encode(array(
        'data' => array(
            'filename'          => $_POST['filename'],
            'input_cols'        => $_POST['input_cols'],
            'input_rows'        => $_POST['input_rows'],
            'size'              => $_POST['size'],
            'db'                => $_POST['db'],
            'table'             => $_POST['table'],
            'output_cols'       => $_POST['output_cols'],
            'output_rows'       => $_POST['output_rows'],
            'time_elapsed'      => $_POST['time_elapsed'],
            'date_begin'        => $_POST['date_begin'],
            'date_end'          => $_POST['date_end'],
            'total_exported'    => $_POST['total_exported'],
            'total_success'     => $_POST['total_success'],
            'total_error'       => $_POST['total_error']
        )
    ));


    // Save the settings used
    $fp = fopen(EXPORTS_DIR . $directory . '/log.json', 'c');

    fseek($fp, -1, SEEK_END);
    $data_log = ',' . substr($data_json, 1);

    fwrite($fp, $data_log);
    fclose($fp);
}


// ===================================================================================
//      EXPORT - GET ID
// ===================================================================================

function export_getId()
{
    $id = setDirectory('export');

    if ($id === false) {
        $res["status"] = 'error';
        $res["message"] = 'Error found.';
    } else {
        $res["status"] = 'success';
        $res["data"] = array();
        $res["data"]['id'] = $id;
    }

    exit(json_encode($res));
}


// ===================================================================================
//      EXPORT
// ===================================================================================

function export()
{
    global $core, $_export_rows_per_batch_method, $_export_rows_per_batch, $_log_csv_delimiter, $_log_csv_enclosure, $_log_csv_escape, $_log_csv_encoding;

    $arr_out_body = array();

    $id_export = (is_int((int)$_GET['id']) and is_numeric($_GET['id'])) ? $_GET['id'] : 0;
    $file_position = 0;

    $db         = $_POST['db'];
    $table      = $_POST['table'];
    $mapping    = $_POST['mapping'];
    $csv_header = $_POST['csv_header'];
    $header     = (isset($_POST['header']) and $_POST['header'] == 1) ? true : false;
    $delimiter  = $_POST['delimiter'];
    $enclosure  = $_POST['enclosure'];
    $encoding   = $_POST['encoding'];
    $file_name  = $_POST['filename'];
    $file_size  = '';

    $directory  = 'export-' . $id_export;
    $file_path  = EXPORTS_DIR . $directory . '/';

    // FIRST BATCH SAVES THE SETTINGS.JSON
    if ($id_export > 0 and $_GET['file_position'] == 0 and $_GET['row_position'] == 0 and $_GET['retry'] == 0) {

        $fp = fopen(EXPORTS_DIR . $directory . '/log.json', 'w');

        $settings_array = array();

        $settings_array['db']                   = $_POST['db'];
        $settings_array['table']                = $_POST['table'];

        $settings_array['file_destination']     = $_POST['file_destination'];

        if ($_POST['file_destination'] == 'desktop') {
            $settings_array['filename']         = $_POST['filename'];
        } elseif ($_POST['file_destination'] == 'ftp') {
            $settings_array['address_url']      = $_POST['address_url'];
            $settings_array['port']             = $_POST['port'];
            $settings_array['username']         = $_POST['username'];
            $settings_array['password']         = $_POST['password'];
            $settings_array['path']             = $_POST['path'];
        } elseif ($_POST['file_destination'] == 'server') {
            $settings_array['server_path']      = $_POST['server_path'];
        }

        $settings_array['header']               = $header;
        $settings_array['delimiter']            = $_POST['delimiter'];
        $settings_array['enclosure']            = $_POST['enclosure'];
        $settings_array['encoding']             = $_POST['encoding'];

        $mapping_info = getMappedInfo($db, $table, $header ? $csv_header : array(), $_POST['mapping'], false);

        $settings_array['mapping']              = $mapping_info;

        $settings_array['filter_rows']          = $_POST['filter_rows'];

        $settings_json = json_encode(array(
            'settings' => $settings_array
        ));

        fwrite($fp, $settings_json);
        fclose($fp);

    }

    // EXPORT
    if ($id_export > 0) {

        $row_position = $_GET['row_position']+1;

        $csv = new parseCSV();
        $csv->heading = $header ? true : false;
        $csv->delimiter = $_POST['delimiter'];
        $csv->enclosure = $_POST['enclosure'];
        //$csv->encoding($_POST['encoding'], $db_encoding);

        $filter_rows = trim($_POST['filter_rows']);
        $conditions = isset($_POST['conditions']) ? $_POST['conditions'] : null;

        // Rows to import per batch
        if ($_export_rows_per_batch_method == 'auto') {
            $csv->max_bytes = return_bytes(MEMORY_LIMIT);
            $offset = '';
            $limit = '';
        } else {
            $offset = $_GET['row_position'];
            $limit = $_export_rows_per_batch;
        }


        $output = _export_get_output_data($db, $table, $mapping, $search = '', $sort = '', $order = '', $offset, $limit, $filter_rows, $conditions);

        $fp = fopen($file_path . $file_name, 'a');


        // CSV header
        $csv_mapped_header = array();
        $csv_header_col_number = array();
        for ($i = 0; $i < count($mapping); $i++) {
            $csv_mapped_header[] = $csv_header[$i];
            $csv_header_col_number[$i] = 'COL#' . ($i+1);
        }
        if ($header and $row_position == 1) {
            fputcsv($fp, $csv_mapped_header, $delimiter, $enclosure);
        }

        // Log details
        $fp_log_details = fopen(EXPORTS_DIR . $directory . '/log_details.csv', 'a');
        $arr_out_header = array_merge(array('ROW', 'STATUS', 'MESSAGE'), $header ? $csv_mapped_header : $csv_header_col_number);
        if (version_compare(PHP_VERSION, '5.5.4') >= 0)
            fputcsv($fp_log_details, $arr_out_header, $_log_csv_delimiter, $_log_csv_enclosure, $_log_csv_escape);
        else
            fputcsv($fp_log_details, $arr_out_header, $_log_csv_delimiter, $_log_csv_enclosure);

        // Row position
        $fp_row_position = fopen(EXPORTS_TEMP_DIR . 'row_position-' . $id_export . '.tmp', 'w');

        foreach ($output['data'] as $fields) {

            // Saves into the CSV
            fputcsv($fp, $fields, $delimiter, $enclosure);

            $status = 'success';
            $message = 'CSV row: ' . ($header ? $row_position+1 : $row_position);

            $status_translate = array(
                'success'=>'Success'
            );

            // Columns imported with an extra status and message column
            $arr_out_body[] = array_merge(array($row_position, $status_translate[$status], $message), $fields);

            // Log details
            if (version_compare(PHP_VERSION, '5.5.4') >= 0)
                fputcsv($fp_log_details, array_merge(array($row_position, $status_translate[$status], $message), $fields), $_log_csv_delimiter, $_log_csv_enclosure, $_log_csv_escape);
            else
                fputcsv($fp_log_details, array_merge(array($row_position, $status_translate[$status], $message), $fields), $_log_csv_delimiter, $_log_csv_enclosure);

            // Progress
            if ($fp_row_position) {
                fseek($fp_row_position, 0);
                $res_write = fwrite ($fp_row_position, $row_position);
                fflush($fp_row_position);
            }

            $row_position++;
        }

        fclose($fp);
        fclose($fp_log_details);

        fclose($fp_row_position);


        $file_size = filesize($file_path . $file_name);

        // Delete the temporary progress file
        //unlink(EXPORTS_TEMP_DIR . 'row_position-' . $id_export . '.tmp');

    }

    $total_success = isset($output) ? count($output['data']) : 0;
    $total_error = 0;

    if (isset($error)) {
        $res["status"] = 'error'; // warning, error, success
        $res["message"] = $error;
    } else {
        if ($row_position >= $output['total_rows']) {
            $res["status"] = 'success';
            $res["message"] = 'Export success';

            // Create the exported file on the server or the ftp location
            if ($_POST['file_destination'] == 'server') {
                if (!copy($file_path . $file_name, $_POST['server_path'])) {
                    $res["status"] = 'success';
                    $res["message"] = 'Could not create the file on the server';
                }
            } else if ($_POST['file_destination'] == 'ftp') {
                $error = '';

                $address_url    = $_POST['address_url'];
                $port           = $_POST['port'];
                $username       = $_POST['username'];
                $password       = $_POST['password'];
                $path           = $_POST['path'];

                // Opens an FTP connection
                $ftp = @ftp_connect($address_url, $port);

                if (!$ftp)
                    $error = 'Couldn\'t connect to ' . $address_url . ' on port ' . $port;

                if ($error and function_exists('ftp_ssl_connect')) {
                    if ($ftp = @ftp_ssl_connect($address_url, $port))
                        $error = '';
                    else
                        $error = 'Couldn\'t connect to ssl ' . $address_url . ' on port ' . $port;
                }

                // try to login
                if (!$error and !@ftp_login($ftp, $username, $password))
                    $error = 'Couldn\'t connect with that username and that password';

                if (!$error)
                    ftp_pasv($ftp, true);

                // Upload the file
                if (!$error and !@ftp_put($ftp, $path, $file_path . $file_name, FTP_BINARY)) {
                    $error = error_get_last();
                    $error = substr($error['message'], strpos($error['message'], ':') + 2);
                }

                // close the connection
                if ($ftp)
                    ftp_close($ftp);

                if ($error) {
                    $res["status"] = 'error'; // warning, error, success
                    $res["message"] = $error;
                }

            }

        } else {
            $res["status"] = 'partial content';
            $res["message"] = 'Partial content read successfuly';
        }
        $res["data"] = array();
        $res["data"]["header"]          = $arr_out_header;
        $res["data"]["body"]            = $arr_out_body;
        $res["data"]["file_size"]       = $file_size;
        $res["data"]["id_export"]       = $id_export;
        $res["data"]["total_success"]   = $total_success;
        $res["data"]["total_error"]     = $total_error;
        $res["data"]["file_position"]   = $file_position;
        $res["data"]["row_position"]    = $row_position;
    }

    echo json_encode($res);
}


// ===================================================================================
//      EXPORT DOWNLOAD
// ===================================================================================

function export_download()
{
    include_once('config/config.php');

    $id_export = $_REQUEST['id'];
    $filename = $_REQUEST['filename'];

    $file_path_name = EXPORTS_DIR . 'export-' . $id_export . '/' . $filename;

    if (file_exists($file_path_name)) {

        $content = file_get_contents($file_path_name);
        header('Content-Description: File Transfer');
        header("Cache-Control: ");
        header("Pragma: ");
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");

        ob_end_clean();
        ob_start();
        echo $content;
        ob_end_flush();
    }

    exit;
}


// ===================================================================================
//      GET PROGRESS
// ===================================================================================

function getProgress()
{
    include_once('config/config.php');

    $row_position = 0;

    $id   = $_GET['id'];
    $type = $_GET['type'];

    if ($type == 'import')
        $directory = IMPORTS_TEMP_DIR;
    elseif ($type == 'export')
        $directory = EXPORTS_TEMP_DIR;

    $filename = 'row_position-' . $id . '.tmp';

    $fp = @fopen($directory . $filename, 'r');
    if ($fp) {
        $row_position = fgets($fp, 31);
        fclose($fp);
    }

    echo json_encode(array(
        'file_position' => '0',
        'row_position'  => $row_position
    ));
    exit;
}


// ===================================================================================
//      GET MAPPING TABLE
// ===================================================================================

function getColumns()
{
    global $_databases;

    $db_id = $_POST['db_id'];
    $table_id = $_POST['table_id'];

    $core = new Core($_databases);
    $res_columns = $core->getGoodColumns($db_id, $table_id);

    if (!$res_columns) {
        $res["status"] = 'error'; // warning, error, success
        $res["message"] = 'Could not get the columns.';
    } else {
        $res["status"] = 'success';
        $res["message"] = 'Data selected from database';
        $res["data"]["names"] = $res_columns['names'];
        $res["data"]["labels"] = $res_columns['labels'];
    }

    echo json_encode($res);
}


// ===================================================================================
//      GET LOG DETAILS
// ===================================================================================

function getLogDetails()
{
    global $_log_csv_delimiter, $_log_csv_enclosure, $_log_csv_escape;

    $search = isset($_POST['search']) ? $_POST['search'] : '';      // text
    $sort   = isset($_POST['sort']) ? $_POST['sort'] : '';          // id, name
    $order  = isset($_POST['order']) ? $_POST['order'] : '';        // asc, desc
    $offset = isset($_POST['offset']) ? $_POST['offset'] : '';      // offset
    $limit  = isset($_POST['limit']) ? $_POST['limit'] : '';        // limit

    $id_log = isset($_GET['id']) ? $_GET['id'] : '';
    $type   = isset($_GET['type']) ? $_GET['type'] : 'import';
    if ($type == 'export')
        $directory = EXPORTS_DIR . 'export-' . $id_log . '/';
    else
        $directory = IMPORTS_DIR . 'import-' . $id_log . '/';

    $file_path = $directory . 'log_details.csv';

    $out = array();
    if (($handle = fopen($file_path, "r")) !== FALSE) {
        $header = fgetcsv($handle, 1000, $_log_csv_delimiter, $_log_csv_enclosure, $_log_csv_escape);
        while (($data = fgetcsv($handle, 1000, $_log_csv_delimiter, $_log_csv_enclosure, $_log_csv_escape)) !== FALSE) {
            $out[] = $data;
        }
        fclose($handle);
    }

    echo json_encode($out);
}


// ===================================================================================
//      SAVE CONFIGURATIONS
// ===================================================================================

function save_configurations()
{
    $import_rows_per_batch_method   = (isset($_POST['import_rows_per_batch_method']) and $_POST['import_rows_per_batch_method'] == 'on') ? 'auto' : 'manual';
    $import_rows_per_batch          = protectVar($_POST['import_rows_per_batch'], $max_characters = 7, $type = 'int');
    $import_csv_settings_method     = (isset($_POST['import_csv_settings_method']) and $_POST['import_csv_settings_method'] == 'on') ? 'auto' : 'manual';
    $import_csv_delimiter           = protectVar($_POST['import_csv_delimiter']);
    $import_csv_enclosure           = protectVar($_POST['import_csv_enclosure']);
    $import_csv_escape              = protectVar($_POST['import_csv_escape']);
    $import_csv_encoding            = protectVar($_POST['import_csv_encoding'], 13);
    $export_rows_per_batch_method   = (isset($_POST['export_rows_per_batch_method']) and $_POST['export_rows_per_batch_method'] == 'on') ? 'auto' : 'manual';
    $export_rows_per_batch          = protectVar($_POST['export_rows_per_batch'], $max_characters = 7, $type = 'int');
    $export_csv_delimiter           = protectVar($_POST['export_csv_delimiter']);
    $export_csv_enclosure           = protectVar($_POST['export_csv_enclosure']);
    $export_csv_escape              = protectVar($_POST['export_csv_escape']);
    $export_csv_encoding            = protectVar($_POST['export_csv_encoding'], 13);

    // CONFIGURATIONS

    // read config file
    $content=file_get_contents(CONFIG_FILE);

    // saves the new configuration
    $content = preg_replace('/\$_import_rows_per_batch_method(\s*)=(\s*)\"(.*?)\";/', '$_import_rows_per_batch_method${1}=${2}"' . $import_rows_per_batch_method . '";', $content);
    $content = preg_replace('/\$_import_rows_per_batch(\s*)=(\s*)(.*?);/', '$_import_rows_per_batch${1}=${2}' . ((int)$import_rows_per_batch) . ';', $content);
    $content = preg_replace('/\$_import_csv_settings_method(\s*)=(\s*)\"(.*?)\";/', '$_import_csv_settings_method${1}=${2}"' . $import_csv_settings_method . '";', $content);
    $content = preg_replace('/\$_import_csv_delimiter(\s*)=(\s*)\"(.*?)\";/', '$_import_csv_delimiter${1}=${2}"' . $import_csv_delimiter . '";', $content);
    $content = preg_replace('/\$_import_csv_enclosure(\s*)=(\s*)\"(.*?)\";/', '$_import_csv_enclosure${1}=${2}"' . $import_csv_enclosure . '";', $content);
    $content = preg_replace('/\$_import_csv_escape(\s*)=(\s*)\"(.*?)\";/', '$_import_csv_escape${1}=${2}"' . str_replace('\\','\\\\',$import_csv_escape) . '";', $content);
    $content = preg_replace('/\$_import_csv_encoding(\s*)=(\s*)\"(.*?)\";/', '$_import_csv_encoding${1}=${2}"' . $import_csv_encoding . '";', $content);
    $content = preg_replace('/\$_export_rows_per_batch_method(\s*)=(\s*)\"(.*?)\";/', '$_export_rows_per_batch_method${1}=${2}"' . $export_rows_per_batch_method . '";', $content);
    $content = preg_replace('/\$_export_rows_per_batch(\s*)=(\s*)(.*?);/', '$_export_rows_per_batch${1}=${2}' . ((int)$export_rows_per_batch) . ';', $content);
    $content = preg_replace('/\$_export_csv_delimiter(\s*)=(\s*)\"(.*?)\";/', '$_export_csv_delimiter${1}=${2}"' . $export_csv_delimiter . '";', $content);
    $content = preg_replace('/\$_export_csv_enclosure(\s*)=(\s*)\"(.*?)\";/', '$_export_csv_enclosure${1}=${2}"' . $export_csv_enclosure . '";', $content);
    $content = preg_replace('/\$_export_csv_escape(\s*)=(\s*)\"(.*?)\";/', '$_export_csv_escape${1}=${2}"' . str_replace('\\', '\\\\', $export_csv_escape) . '";', $content);
    $content = preg_replace('/\$_export_csv_encoding(\s*)=(\s*)\"(.*?)\";/', '$_export_csv_encoding${1}=${2}"' . $export_csv_encoding . '";', $content);

    // write file
    file_put_contents(CONFIG_FILE, $content);


    // DATABASES
    $databases = json_decode($_POST['databases']);

    $fp = fopen(DATABASES_FILE, 'w');
    fwrite($fp, '<?php

//==================================================================================================
//  DATABASES
//==================================================================================================

$_databases = array(');

    $i = 0;
    $content="";
    foreach ($databases as $dkey => $database) {

        if ($i > 0)
            $content .= ",";

        $content .= "
    '" . $dkey . "' => array(
        'label'    => '" . protectVar($database->label, 255) . "',
        'name'     => '" . protectVar($database->name, 255) . "',
        'server'   => '" . protectVar($database->server, 255) . "',
        'username' => '" . protectVar($database->username, 255) . "',
        'password' => '" . protectVar($database->password, 255) . "',
        'port'     => '" . protectVar($database->port, 10) . "',
        'encoding' => '" . protectVar($database->encoding, 255) . "',
        'active'   => " . ((isset($database->active) and $database->active === false) ? 'false' : 'true');

        if (isset($database->tables)) {
            if (is_object($database->tables)) {

                $content .= ",
        'tables'   => array(";

        $j = 0;
        foreach ($database->tables as $tkey => $table) {

            if ($j > 0)
                $content .= ",";

            $content .= "
            '" . $tkey . "' => array(
                'label'      => '" . $table->label . "',
                'active'     => " . ((isset($table->active) and $table->active === false) ? 'false' : 'true') . ",
                'properties' => array(
                    array(
                        'name'    => '" . $table->properties[0]->name . "'";

            if (isset($table->properties[0]->columns)) {
                if (is_array($table->properties[0]->columns)) {

                    $content .= ",
                        'columns' => array(";

                    $k = 0;
                    foreach ($table->properties[0]->columns as $column) {

                        if ($k > 0)
                            $content .= ",";

                        $content .= "
                            array(
                                'label'  => '" . $column->label . "',
                                'name'   => '" . $column->name . "',
                                'active' => " . ((isset($column->active) and $column->active === false) ? 'false' : 'true') . "
                            )";

                        $k++;
                    }

                    $content .= "
                        )";

                } else {

                $content .= ",
                        'columns' => '*'";

                }
            }

            if (isset($table->properties[0]->hide_columns) and is_array($table->properties[0]->hide_columns))
            $content .= ",
                        'hide_columns' => array('" . implode("','", $table->properties[0]->hide_columns) . "')";


            $content .= "
                    )
                )
            )";

            $j++;
        }

        $content .= "
        )";

            } else {

        $content .= ",
        'tables'   => '*'";

            }
        }

        $content .= "
    )";

        $i++;
    }

    $content .= "
);

?>
";

    fwrite($fp, $content);

    fclose($fp);

    if (isset($error)) {
        $res["status"] = 'error';
        $res["message"] = $error;
    } else {
        $res["status"] = 'success';
        $res["message"] = 'Configurations saved';
    }

    echo json_encode($res);
}

function settings_getDBCharset()
{
    $db_port = isset($_POST['db_port']) ? $_POST['db_port'] : 3306;

    $core = new Core();
    $conn = $core->setDB($_POST['db_name'], $_POST['db_username'], $_POST['db_password'], $_POST['db_server'], $db_port);

    if ($conn) {
        $query = 'SELECT default_character_set_name FROM information_schema.SCHEMATA WHERE schema_name = "' . $_POST['db_name'] . '";';
        $db_charset = $core->db->get_var($query);

        $res["status"] = 'success';
        $res["data"]['db_charset'] = $db_charset;
    } else {
        $res["status"] = 'error';
    }

    echo json_encode($res);
}

function settings_getAllTables()
{
    $db_port = isset($_POST['db_port']) ? $_POST['db_port'] : 3306;
    $db_encoding = isset($_POST['db_encoding']) ? $_POST['db_encoding'] : 'utf8';

    $core = new Core();
    $conn = $core->setDB($_POST['db_name'], $_POST['db_username'], $_POST['db_password'], $_POST['db_server'], $db_port, $db_encoding);

    if ($conn) {
        $query = "SELECT table_name FROM information_schema.tables where table_schema='" . $_POST['db_name'] . "';";
        $tables = $core->db->get_col($query);

        foreach ($tables as $table)
            $result[$table] = $table;

        $res["status"] = 'success';
        $res["data"] = $result;
    } else {
        $res["status"] = 'error';
    }

    echo json_encode($res);
}

function settings_getAllColumns()
{
    global $_databases;

    $database       = $_POST['database'];
    $table          = $_POST['table'];
    $table_name     = isset($_databases[$database]['tables'][$table]['properties'][0]['name']) ? $_databases[$database]['tables'][$table]['properties'][0]['name'] : $table;
    $db_port        = isset($_POST['db_port']) ? $_POST['db_port'] : 3306;
    $db_encoding    = isset($_POST['db_encoding']) ? $_POST['db_encoding'] : 'utf8';

    $core = new Core();
    $conn = $core->setDB($_POST['db_name'], $_POST['db_username'], $_POST['db_password'], $_POST['db_server'], $db_port, $db_encoding);

    if ($conn) {
        $query = "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='" . $_POST['db_name'] . "' AND `TABLE_NAME`='" . $table_name . "'";
        $columns = $core->db->get_col($query);

        $res["status"] = 'success';
        $res["data"] = $columns;
    } else {
        $res["status"] = 'error';
    }

    echo json_encode($res);
}



//==================================================================================================
// FUNCTIONS
//==================================================================================================

// TODO: Be able to configure through the config.php
// $_directory = '@TYPE@-@INDEX@';
// @TYPE@
// @INDEX@
// @FILE@
function getDirectory($type)
{
    $directory = '';
    $max_index = 0;

    if ($type == 'import') {
        $dir = new DirectoryIterator(IMPORTS_DIR);
        $tmp_dir = IMPORTS_TEMP_DIR;
    } elseif ($type == 'export') {
        $dir = new DirectoryIterator(EXPORTS_DIR);
        $tmp_dir = EXPORTS_TEMP_DIR;
    }

    foreach ($dir as $fileinfo) {
        if ($fileinfo->isDir() and !$fileinfo->isDot() and $fileinfo->getFilename() != basename($tmp_dir)) {
            $index = substr($fileinfo->getFilename(), strrpos($fileinfo->getFilename(), '-') + 1);
            if ($index > $max_index)
                $max_index = $index;
        }
    }

    if ($type == 'import')
        $directory = 'import-' . ($max_index + 1);
    elseif ($type == 'export')
        $directory = 'export-' . ($max_index + 1);

    return $directory;
}



function getNextId($type)
{
    $directory = '';
    $max_index = 0;

    if ($type == 'import') {
        $dir = new DirectoryIterator(IMPORTS_DIR);
        $tmp_dir = IMPORTS_TEMP_DIR;
    } elseif ($type == 'export') {
        $dir = new DirectoryIterator(EXPORTS_DIR);
        $tmp_dir = EXPORTS_TEMP_DIR;
    }

    foreach ($dir as $fileinfo) {
        if ($fileinfo->isDir() and !$fileinfo->isDot() and $fileinfo->getFilename() != basename($tmp_dir)) {
            $index = substr($fileinfo->getFilename(), strrpos($fileinfo->getFilename(), '-') + 1);
            if ($index > $max_index)
                $max_index = $index;
        }
    }

    return $max_index+1;
}


function setDirectory($type)
{
    if ($type == 'import') {
        if (!is_dir(IMPORTS_DIR))
            mkdir(IMPORTS_DIR, 0755);
        if (!is_dir(IMPORTS_TEMP_DIR))
            mkdir(IMPORTS_TEMP_DIR, 0755);
    } elseif ($type == 'export') {
        if (!is_dir(EXPORTS_DIR))
            mkdir(EXPORTS_DIR, 0755);
        if (!is_dir(EXPORTS_TEMP_DIR))
            mkdir(EXPORTS_TEMP_DIR, 0755);
    }

    while (!isset($id_directory)) {

        $next_id_directory = getNextId($type);

        if ($type == 'import')
            $directory = IMPORTS_DIR . 'import-' . $next_id_directory;
        elseif ($type == 'export')
            $directory = EXPORTS_DIR . 'export-' . $next_id_directory;

        if (!is_dir($directory)) {

            if (mkdir($directory, 0755))
                $id_directory = $next_id_directory;
            else
                return false;

        }

    }

    return $id_directory;
}


function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object))
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        rmdir($dir);
    }
}


function protectVar($var, $max_characters = 2, $type = 'string')
{
    if ($type == 'int')
        $var = (int) $var;
    else
        $var = (string) $var;

    $protected = substr($var, 0, $max_characters);

    if (!get_magic_quotes_gpc()) {
        $protected = str_replace('\\', '\\\\', $protected);
        $protected = str_replace('"', '\"', $protected);
    }

    return $protected;
}


function getMappedInfo($db_id, $table_id, $csv_titles, $mapping, $clear_empty)
{
    global $_databases;
    $columns = array();

    $core = new Core($_databases);

    $core->setDBsStructure($db_id, $table_id);

    // Clear empty values
    if ($clear_empty)
        $mapping = array_diff($mapping, array(''));

    $i = 0;
    foreach ($core->dbs_structure[$db_id]['tables'][$table_id]['properties'] as $table_info) {

        foreach ($table_info['columns'] as $column_info) {

            if (array_key_exists($i, $mapping)) {

                $columns[] = array(
                    'table'         => $table_info['name'],
                    'index'         => $i,
                    'name'          => $mapping[$i] != '' ? $column_info['name'] : '',
                    'label'         => $mapping[$i] != '' ? $column_info['label'] : '',
                    'csv_column'    => $mapping[$i] != '' ? $mapping[$i]+1 : $i + 1,
                    'csv_header'    => isset($csv_titles[($mapping[$i] != '' ? $mapping[$i] : $i)]) ? $csv_titles[($mapping[$i] != '' ? $mapping[$i] : $i)] : ''
                );

            }

            $i++;
        }

    };

    return $columns;
}

?>