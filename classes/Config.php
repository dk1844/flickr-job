<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Config
 *
 * @author Daniel
 */
class Config {
    ///schema as "mysql://flickr:flickr@localhost/flickr"
    const DB_CONNECTION_STRING_TEMPLATE = "%s://%s:%s@%s/%s";
    ///location of config.ini
    private $ini_path;
    ///these are config data
    private $config_data = array();

    /**
     * creates Config and load it
     * @param string $path the file
     */
    public function __construct($path = "./config.ini") {
        $this->ini_path  = $path;
        $this->load();
    }

    /**
     * Load the given config file in constuctor. If fails, everybody dies.
     */
    public function load() {
        $data = @parse_ini_file($this->ini_path); // @ cuz we're replacing the error message with our own
        if ($data === false) die('Error: Cannot load config from path:' . $this->ini_path);

        $this->setConfigData($data);
        
    }

    /**
     * gives you the parameter
     * @param string $name
     * @return string|array content or false on error
     */
    public function p($name) {
        $data = ($this->getConfigData());
        $par = $data[$name];

        if(isset($par)) return $par;
        return false;
    }

    /**
     * creates db connection string
     * @return string like "mysql://flickr:flickr@localhost/flickr"
     */
    public function generateDbConnectionString() {
        $template = self::DB_CONNECTION_STRING_TEMPLATE;
        $res = sprintf($template,
                $this->p("db_type"),
                $this->p("db_username"),
                $this->p("db_password"),
                $this->p("db_host"),
                $this->p("db_database_name")
                );
       return $res;
    }

            ///-------getters and setters-------------

     public function setIniPath($path) {
        $this->ini_path = $path;
    }

    public function getIniPath() {
        return $this->ini_path;
    }

    public function setConfigData($data) {
        $this->config_data = $data;
    }

    public function getConfigData() {
        return $this->config_data;
    }
}
?>
