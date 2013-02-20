<?php
/**
 * Short description of the class
 *
 * @package TAO
 * @author lionel.lecaque@tudor.lu
 *
 */
class tao_install_utils_SqlsrvDbCreator extends tao_install_utils_DbCreator
{

    protected function getDsn($host)
    {
        return $this->driver . ':Server=' . $host . $this->getExtraDSN();
    }



    public function chooseSQLParsers()
    {
        $this->setSQLParser(new tao_install_utils_SimpleSQLParser());
        //TODO $this->setProcSQLParser();
    }

    /**
     * Check if the database exists already
     * @param string $name
     */
    public function dbExists($dbName)
    {
        $this->pdo->exec('use master;');
        $result = $this->pdo->query('SELECT name FROM "sysdatabases"');
        $databases = array();
        while($db = $result->fetchColumn(0)){
            $databases[] = $db;
        }
        if (in_array($dbName, $databases)){
            return true;
        }
        return false;
    }

    /**
     * Clean database by droping all tables
     * @param string $name
     */
    public function cleanDb()
    {
        $tables = array();
        $result = $this->pdo->query('SELECT TABLE_NAME FROM information_schema.tables');

        while ($t = $result->fetchColumn(0)){
            $tables[] = $t;
        }

        foreach ($tables as  $t){
            $this->pdo->exec("DROP TABLE \"${t}\"");
        }
    }

    public function createDatabase($name)
    {
        $this->pdo->exec('CREATE DATABASE "' . $name . '"');
        $this->setDatabase($name);
    }

    protected function afterConnect()
    {
        $this->pdo->exec("SET NAMES 'UTF8'");
    }

    protected function getExtraConfiguration()
    {
        return array();
    }

    protected function getExtraDSN()
    {
        return ';';
    }

    protected function getDiscoveryDSN()
    {
        $driver = str_replace('pdo_', '', $this->driver);
        $dsn  = $driver . ':Server=' . $this->host;
        return $dsn;
    }

    protected function getDatabaseDSN()
    {
        $driver = str_replace('pdo_', '', $this->driver);
        $dbName = $this->dbName;
        $dsn  = $driver . ':Server=' . $this->host . ';Database=' . $dbName;
        return $dsn;
    }

}
