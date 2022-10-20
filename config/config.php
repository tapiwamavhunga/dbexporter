<?php

//==================================================================================================
//  CONFIGURATIONS
//==================================================================================================

// PATHES & URLS
define('ABSPATH',                   dirname(dirname(__FILE__)) . '/');  // The absolute path of the application
define('CONFIG_DIR_NAME',           'config');                          // Configurations directory name
define('CONFIG_DIR',                ABSPATH.CONFIG_DIR_NAME.'/');       // Configurations path
define('CONFIG_FILE',               CONFIG_DIR.'config.php');           // Configurations file path
define('DATABASES_FILE',            CONFIG_DIR.'databases.php');        // Databases file path
define('IMPORTS_DIR_NAME',          '_imports');                        // Import directory name
define('IMPORTS_DIR',               ABSPATH.IMPORTS_DIR_NAME.'/');      // Import path - where the imports gets saved
define('IMPORTS_TEMP_DIR',          IMPORTS_DIR.'tmp/');                // Import temporary path - allocate necessary files on import(s)
define('EXPORTS_DIR_NAME',          '_exports');                        // Export directory name
define('EXPORTS_DIR',               ABSPATH.EXPORTS_DIR_NAME.'/');      // Export path - where the exports gets saved
define('EXPORTS_TEMP_DIR',          EXPORTS_DIR.'tmp/');                // Export temporary path - allocate necessary files on export(s)
define('ABSURL',                    '');                                // The absolute url of the application
define('IMPORTS_URL',               ABSURL.IMPORTS_DIR_NAME.'/');       // Url with the location of the imports
define('EXPORTS_URL',               ABSURL.EXPORTS_DIR_NAME.'/');       // Url with the location of the exports

// GENERAL
$_hide_only_one_database            = true;                             // To hide the databases select boxes when only 1 database exist
$_hide_only_one_table               = true;                             // To hide the tables select boxes when only 1 table exist
$_file_encoding_list                = array(                            // Most used file encoding charsets ordered by world usage (IMPORTANT: put in order of preference)
    'UTF-8',
    'ISO-8859-1',
    'Windows-1251',
    'SJIS',
    'Windows-1252',
    'GB2312',
    'EUC-KR',
    'EUC-JP',
    'GBK',
    'ISO-8859-2',
    //'Windows-1250',
    'ISO-8859-15',
    //'Windows-1256',
    'BIG-5',
    'ISO-8859-9',
    'Windows-1254'
    //'Windows-874'
);

// IMPORT
$_import_rows_per_batch_method      = "auto";                           // auto, manual ("auto" - will determine the best number of rows to improve the speed of the import)
$_import_rows_per_batch             = 300;                              // 1, 100, 1000, ...
$_import_csv_settings_method        = "auto";                           // auto, manual ("auto" - will search on the file what's the most probable value)
$_import_csv_delimiter              = ",";                              // ",", ";", "\t", "|", ".", ":", "^" - (attention: on the string use " and not ')
$_import_csv_enclosure              = "\"";                             // "\"", "'" - (attention: on the string use " and not ')
$_import_csv_escape                 = "\\";                             // "\\" - (attention: on the string use " and not ')
$_import_csv_encoding               = "UTF-8";                          // Default CSV encoding
$_import_allowed_file_extensions    = array('csv');                     // Allowed file extensions

// EXPORT
$_export_rows_per_batch_method      = "auto";                         // auto, manual ("auto" - will determine the best number of rows to improve the speed of the export)
$_export_rows_per_batch             = 300;                              // 1, 100, 1000, ...
$_export_csv_delimiter              = ",";                              // ",", ";", "\t", "|", ".", ":", "^" - (attention: on the string use " and not ')
$_export_csv_enclosure              = "\"";                             // "\"", "'" - (attention: on the string use " and not ')
$_export_csv_escape                 = "\\";                             // "\\" - (attention: on the string use " and not ')
$_export_csv_encoding               = "UTF-8";

// LOGS
$_log_data_format                   = "Y-m-d H:i:s";                    // Date format
$_log_csv_delimiter                 = ";";                              // ",", ";", "\t", "|", ".", ":", "^" - (attention: on the string use " and not ')
$_log_csv_enclosure                 = "\"";                             // "\"", "'" - (attention: on the string use " and not ')
$_log_csv_escape                    = "\\";                             // "\\" - (attention: on the string use " and not ')
$_log_csv_encoding                  = "UTF-8";

// PHP INI CONFIGURATIONS
define("UPLOAD_MAX_FILESIZE",       ini_get('upload_max_filesize'));    // The maximum size of an uploaded file - 2M, 200M, ...
define("MAX_FILE_UPLOADS",          ini_get('max_file_uploads'));       // The maximum number of files allowed to be uploaded simultaneously - 20, 30, ...
define("POST_MAX_SIZE",             ini_get('post_max_size'));          // Max size of post data allowed - 8M, 200M, ...
define("MEMORY_LIMIT",              ini_get('memory_limit'));           // Maximum amount of memory in bytes that a script is allowed to allocate - 128M, 256M, ...
define("MAX_EXECUTION_TIME",        ini_get('max_execution_time'));     // Maximum time in seconds a script is allowed to run before it is terminated by the parser - 30, 60, 300, ...
define("MAX_INPUT_TIME",            ini_get('max_input_time'));         // Maximum time in seconds a script is allowed to parse input data, like POST and GET - 30, 60, 300, ... -1 means that max_execution_time is used instead
define("ALLOW_URL_FOPEN",           ini_get('allow_url_fopen'));        // If the server can access remote files through url - true or false
define("CURL_INIT",                 function_exists('curl_init'));      // If the curl function is installed or not - true or false
