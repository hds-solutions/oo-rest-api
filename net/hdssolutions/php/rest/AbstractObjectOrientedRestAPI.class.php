<?php
    namespace net\hdssolutions\php\rest;

    use \Exception;

    abstract class AbstractObjectOrientedRestAPI {
        private static $instance;

        public static final function init() {
            // get singleton instance
            $api = self::getInstance();
            //
            $api->execute();
        }

        private function __construct() {}

        private function execute() {
            //
            try {
                // parse request
                $raw = $this->parseRequest();
                // parse data based on method
                $data = $this->parseData($raw->method);
                // validate method
                if (!method_exists($this, $raw->endpoint)) throw new Exception('No endpoint: '.$raw->endpoint, 404);
                // get endpoint
                $endpoint = $this->{$raw->endpoint}();
                // execute method
                $endpoint->{strtolower($raw->method)}($raw->verb, $raw->args, $data);
            } catch (Exception $e) {
                //
                $this->output([
                    'code'  => $e->getCode(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        final function output($data, $local = false) {
            // return local data
            if ($local === true) return (object)$data;
            // force JSON outout
            header('Content-Type: application/json', true);
            echo json_encode(array_merge([
                'success'       => true,
                'code'          => 200,
                'error'         => null,
                'performance'   => 0,
                'result'        => null
            ], $data));
        }

        private function parseRequest() {
            // validate request
            if (!isset($_GET['_api_']) || (
                !isset($_GET['_api_']['request']) ||
                !isset($_GET['_api_']['version']) ||
                !isset($_GET['_api_']['endpoint'])))
                //
                throw new Exception('Bad configuration', 500);
            // parse request URI
            $raw = (object)$_GET['_api_']; unset($_GET['_api_']);
            // clean params
            $raw->request = rtrim($raw->request, '/');
            $raw->endpoint = rtrim($raw->endpoint, '/');
            // format request params (call without version)
            if ($raw->version == null && $raw->endpoint == null) {
                // default version to v1.0
                $raw->version = 'v1.0';
                // copy request value to endpoint
                $raw->endpoint = $raw->request;
            }
            // almacenamos la version
            $raw->version = (float)ltrim($raw->version, 'v');
            // separamos el request en partes
            $raw->args = explode('/', $raw->endpoint);
            // obtenemos el primer parametro como el endpoint
            $raw->endpoint = array_shift($raw->args);
            // almacenamos el verbo
            $raw->verb = array_key_exists(0, $raw->args) ? array_shift($raw->args) : null;
            // obtenemos el metodo
            $raw->method = strtoupper($_SERVER['REQUEST_METHOD']);
            // verificamos si el metodo es POST
            if ($raw->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
                // verificamos si es PUT|DELETE
                if (in_array(strtoupper($_SERVER['HTTP_X_HTTP_METHOD']), [ 'PUT', 'DELETE' ]))
                    $raw->method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD']);
                else
                    // retornamos con un error
                    throw new Exception('Unexpected Header', 400);
            }
            //
            return $raw;
        }

        private function parseData($method) {
            //
            $data = [];
            switch ($method) {
                case 'GET':
                    // append GET data
                    $data = $this->cleanInputs($_GET);
                case 'POST':
                case 'PUT':
                case 'DELETE':
                    // append POST data
                    $data = array_merge($data, $this->cleanInputs($_POST));
                    //
                    $input_file = file_get_contents('php://input');
                    // check for JSON POST data
                    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
                        // parse JSON
                        $input_file = json_decode($input_file);
                        // check for double escaped JSON
                        if (gettype($input_file) === 'string') $input_file = json_decode($input_file);
                    }
                    break;

                default: throw new Exception('Invalid Method', 405);
            }
            //
            return (object)$data;
        }

        private function cleanInputs($data) {
            // eliminamos los espacios en blanco de cada parametro recibido
            $clean_input = [];
            if (is_array($data))
                foreach ($data as $k => $v)
                    $clean_input[$k] = $this->cleanInputs($v);
            else
                $clean_input = trim($data);
            // retornamos los parametros limpios
            return $clean_input;
        }

        public static final function error_handler($severity, $message, $file, $line, array $context) {
            // reset output
            ob_clean();
            // force JSON outout
            header('Content-Type: application/json', true);
            echo json_encode([
                'success'   => false,
                'code'      => $severity,
                'error'     => "$message in $file:$line",
                'result'    => null
            ]);
            // stop execution
            exit;
        }

        private static function getInstance() {
            // force singleton
            if (self::$instance === null) {
                $clazz = get_called_class();
                self::$instance = new $clazz;
            }
            // return API instance
            return self::$instance;
        }
    }