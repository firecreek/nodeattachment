<?php
/**
 * Plugin activation
 *
 * Description
 *
 * @package  Croogo
 * @author Juraj Jancuska <jjancuska@gmail.com>
 */
class NodeattachmentActivation {

        /**
         * Schema directory
         *
         * @var string
         */
        private $SchemaDir;

        /**
         * DB connection
         *
         * @var object
         */
        private $db;

        /**
         * Constructor
         *
         * @return vodi
         */
         public function  __construct() {

                 $this->SchemaDir = APP.'plugins'.DS.'nodeattachment'.DS.'config'.DS.'schemas';
                 $this->db =& ConnectionManager::getDataSource('default');

        }

        /**
         * Before onActivation
         *
         * @param object $controller
         * @return boolean
         */
        public function beforeActivation(&$controller) {

                App::Import('CakeSchema');
                $CakeSchema = new CakeSchema();

                // list schema files from config/schema dir
                if (!$cake_schema_files = $this->_listSchemas($this->SchemaDir))
                        return false;

                // create table for each schema
                foreach ($cake_schema_files as $schema_file) {
                        $schema_name = substr($schema_file, 0, -4);
                        $schema_class_name = Inflector::camelize($schema_name).'Schema';
                        $table_name = $schema_name;

                        if (!in_array($table_name, $this->db->_sources)) {
                                 include_once($this->SchemaDir.DS.$schema_file);
                                 $ActiveSchema = new $schema_class_name;
                                 if(!$this->db->execute($this->db->createSchema($ActiveSchema, $table_name))) {
                                         return false;
                                 }
                        }

                }

                return true;

        }

        /**
         * Activation of plugin,
         * called only if beforeActivation return true
         *
         * @param object $controller
         * @return void
         */
        public function onActivation(&$controller) {

                $controller->Setting->write('Nodeattachment.maxFileSize', '3', array(
                    'editable' => 1, 'description' => __('Max. size of uploaded file (MB)', true))
                );
                $controller->Setting->write('Nodeattachment.allowedFileTypes', 'jpg,gif,png', array(
                    'editable' => 1, 'description' => __('Coma separated list of allowes extensions (empty = all files)', true))
                );

        }

        /**
         * Before onDeactivation
         *
         * @param object $controller
         * @return boolean
         */
        public function beforeDeactivation(&$controller) {

                // list schema files from config/schema dir
                if (!$cake_schema_files = $this->_listSchemas($this->SchemaDir))
                        return false;

                // delete tables for each schema
                foreach ($cake_schema_files as $schema_file) {
                        $schema_name = substr($schema_file, 0, -4);
                        $table_name = $schema_name;
                        /*if(!$this->db->execute('DROP TABLE '.$table_name)) {
                                return false;
                        }*/
                }
                return true;

        }

        /**
         * Deactivation of plugin,
         * called only if beforeActivation return true
         *
         * @param object $controller
         * @return void
         */
        public function onDeactivation(&$controller) {

                $controller->Setting->deleteKey('Nodeattachment');

        }

        /**
         * List schemas
         *
         * @return array
         */
        private function _listSchemas($dir = false) {

                if (!$dir) return false;

                $cake_schema_files = array();
                if ($h = opendir($dir)) {
                        while (false !== ($file = readdir($h))) {
                                if (($file != ".") && ($file != "..")) {
                                        $cake_schema_files[] = $file;
                                }
                        }
                } else {
                        return false;
                }

                return $cake_schema_files;

        }
}
?>