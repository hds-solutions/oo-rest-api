<?php
    namespace net\hdssolutions\api;

    use Exception;

    final class APIUtils {
        public static $KEY_SIZE = 10;
        public static final function makeUID() {
            $uid = '';
            $fields = func_get_args();
            $fields = array_reverse($fields);
            foreach ($fields as $field)
                // append the parameters with : between
                $uid = strlen($uid) == 0 ?
                        substr(md5($field         ), 0, self::$KEY_SIZE):
                        substr(md5($field.':'.$uid), 0, self::$KEY_SIZE);
            // return MD5 ID
            return $uid;
        }

        public static final function makeFK() {
            $fk = '';
            $fields = func_get_args();
            $fields = array_reverse($fields);
            foreach ($fields as $field)
                // append the parameters with : between
                $fk = strlen($fk) == 0 ?
                        'SUBSTR(MD5('.       $field             .'),1,'.self::$KEY_SIZE.')':
                        'SUBSTR(MD5(CONCAT('.$field.',":",'.$fk.')),1,'.self::$KEY_SIZE.')';
            // return MD5 ID
            return $fk;
        }

        public static final function dField($field) {
            // retornamos el string con el pass
            return 'CONVERT(IFNULL(AES_DECRYPT('.$field.', UNHEX("'.$this->auth.'")), '.$field.') USING UTF8) AS '.substr($field, strpos($field, '.') ? strpos($field, '.') + 1 : 0, strlen($field));
        }

        public static final function eField($field) {
            // retornamos el string con el pass
            return 'AES_ENCRYPT('.$field.', UNHEX("'.$this->auth.'"))';
        }

        public static final function requestedVersion() {
            // validate request
            if (!isset($_GET['_api_']) || (
                !isset($_GET['_api_']['request']) ||
                !isset($_GET['_api_']['version']) ||
                !isset($_GET['_api_']['endpoint'])))
                //
                throw new Exception('Bad configuration', 500);
            // return null if not version is defined
            if (strlen($_GET['_api_']['version']) == 0) return null;
            // return requested version
            return (float)ltrim($_GET['_api_']['version'] ?? 'v1.0', 'v');
        }
    }