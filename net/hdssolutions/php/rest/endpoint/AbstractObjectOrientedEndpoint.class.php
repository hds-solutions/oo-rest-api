<?php
    namespace net\hdssolutions\php\rest\endpoint;

    use net\hdssolutions\php\rest\AbstractObjectOrientedRestAPI;
    use \Exception;

    abstract class AbstractObjectOrientedEndpoint {
        protected $parent;

        final function __construct(AbstractObjectOrientedRestAPI $parent) {
            $this->parent = $parent;
        }

        public abstract function get(string $verb = null, array $args = [], object $data = null, bool $local = false);

        public function post(string $verb = null, array $args = [], object $data = null) {
            // method disabled by default
            throw new Exception('Method not allowed', 400);
        }

        public function put(string $verb = null, array $args = [], object $data = null) {
            // method disabled by default
            throw new Exception('Method not allowed', 400);
        }

        public function delete(string $verb = null, array $args = [], object $data = null) {
            // method disabled by default
            throw new Exception('Method not allowed', 400);
        }

        protected final function output($data, $local = false) {
            //
            return $this->parent->output($data, $local);
        }
    }