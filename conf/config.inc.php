<?php
    // default error handler
    function _default_error_handler($severity, $message, $file, $line, array $context = null) {
        // reset output
        ob_clean();
        // force JSON outout
        header('Content-Type: application/json', true);
        echo json_encode([
            'success'   => false,
            'code'      => $severity,
            'error'     => str_replace(SERVICE_PATH, '', "$message in $file:$line"),
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
            _default_error_handler($error->type, $error->message, $error->file, $error->line);
    });