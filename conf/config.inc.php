<?php
    // redirect parser
    foreach ($_SERVER as $key => $value)
        if (substr($key, 0, 9) == 'REDIRECT_') {
            $_SERVER[substr($key, 9, strlen($key))] = $value;
            unset($_SERVER[$key]);
        }

    // develop flag
    define('DEVELOP', isset($_SERVER['DEVELOP']) && $_SERVER['DEVELOP'] === 'true'); unset($_SERVER['DEVELOP']);

    // Configuracion PHP
    error_reporting(E_ALL);
    ini_set('display_errors', DEVELOP);

    // PHP Configuration
    date_default_timezone_set('America/Asuncion');
    setlocale(LC_ALL, 'es-PY');

    // ruta base
    define('SERVICE_PATH', __DIR__.'/..');

    // directorio para los include_ require_ en /
    set_include_path(SERVICE_PATH);

    // default error handler
    function _default_error_handler($severity, $message, $file, $line, array $context = null) {
        // force JSON outout
        header('Content-Type: application/json', true);
        echo json_encode([
            'success'   => false,
            'code'      => $severity,
            'error'     => "$message in $file:$line",
            'result'    => null
        ]);
        // end execution
        exit;
    };

    // default error capture
    set_error_handler(function($severity, $message, $file, $line, array $context) {
        // execute error handler
        _default_error_handler($severity, $message, $file, $line, $context);
    });

    // default shutdown capture
    register_shutdown_function(function() {
        // get last error
        $error = (object)error_get_last();
        // check if is a valid error
        if (error_get_last() !== null && in_array($error->type, [ E_ERROR, E_PARSE, E_COMPILE_ERROR ]))
            // redirect to error handler
            _default_error_handler($error->type, $error->message, $error->file, $error->file);
    });