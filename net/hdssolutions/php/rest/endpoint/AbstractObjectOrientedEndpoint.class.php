<?php
    namespace net\hdssolutions\php\rest\endpoint;

    use net\hdssolutions\php\rest\AbstractObjectOrientedRestAPI;
    use \Exception;

    abstract class AbstractObjectOrientedEndpoint {
        // parent instance
        private $parent;

        // local instance for static calls
        private static $instance;

        final function __construct(AbstractObjectOrientedRestAPI $parent) {
            // save instance
            self::$instance = $this;
            // save parent
            $this->parent = $parent;
        }

        public static final function __callStatic(string $endpoint, array $func_args) {
            // parse verb
            $verb = $func_args[0] ?? null;
            // parse args
            $args = $func_args[1] ?? [];
            // parse data
            $data = $func_args[2] ?? (object)[];
            // parse local
            $local = $func_args[3] ?? false;
            // execute endpoint on parent
            return self::$instance->parent->$endpoint($verb, $args, $data, $local);
        }

        public abstract function get(string $verb = null, array $args = [], object $data = null, bool $local = false);

        public function post(string $verb = null, array $args = [], object $data = null, bool $local = false) {
            // method disabled by default
            throw new Exception('Method not allowed', 400);
        }

        public function put(string $verb = null, array $args = [], object $data = null, bool $local = false) {
            // method disabled by default
            throw new Exception('Method not allowed', 400);
        }

        public function delete(string $verb = null, array $args = [], object $data = null, bool $local = false) {
            // method disabled by default
            throw new Exception('Method not allowed', 400);
        }

        protected final function parent() {
            // return parent object
            return $this->parent;
        }

        protected final function output($data, $local = false) {
            // redirect to parent output() method
            return $this->parent->output($data, $local);
        }

        protected abstract function makeObject(object $dbdata, object $data, bool $local);

        protected final function sLayer() {
            // return parent object
            return $this->parent->sLayer();
        }
    }