<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Config\Loader;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class DatabaseLoader
 * @package Cleentfaar\Devr\Config\Loader
 */
class DatabaseLoader
{

    private $data;

    private $connection;

    /**
     * @param $key
     * @return null
     */
    public function get($key)
    {
        $data = $this->getData();
        return isset($data[$key]) ? $data[$key] : null;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->getData();
    }

    public function save(array $data)
    {
        $this->data = $data;
        foreach ($data as $key => $value) {
            $query = "SELECT `key` FROM `configuration` WHERE `key` = :key";
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute(array('key'=>$key));
            $rows = $stmt->fetchAll();
            $params = array('key'=>$key,'value'=>$value);
            if (!empty($rows)) {
                $query = "UPDATE `configuration` SET `value` = :value WHERE `key` = :key";
            } else {
                $query = "INSERT INTO `configuration` (key, value) VALUES (:key,:value)";
            }
            $stmt = $this->getConnection()->prepare($query);
            $success = $stmt->execute($params);
            if (!$success) {
                return false;
            }
        }
        return true;
    }

    private function getData()
    {
        if (!isset($this->data)) {
            $connection = $this->getConnection();
            $query = "SELECT key,value FROM `configuration`";
            $stmt = $connection->query($query);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $i => $row) {
                $this->data[$row['key']] = $row['value'];
            }
        }
        return $this->data;
    }

    private function getConnection()
    {
        if (!isset($this->connection)) {
            $dbDir = DEVR_ROOT_DIR . "/app/db";
            $filesystem = new Filesystem();
            if (!is_dir($dbDir)) {
                $filesystem->mkdir($dbDir);
            }
            $filesystem->chmod($dbDir,0777);
            if (defined("DEVR_TEST_MODE")) {
                $pathToDb = $dbDir . '/devr.test.'.uniqid().'.sq3';
            } else {
                $pathToDb = $dbDir . '/devr.sq3';
            }
            if ($filesystem->exists($pathToDb)) {
                $filesystem->chmod($pathToDb,0777);
            }
            $connection = new \PDO(
                'sqlite:' . $pathToDb,
                null,
                null,
                array(
                    \PDO::ATTR_PERSISTENT => true,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                )
            );
            if (!$this->prepareTables($connection)) {
                throw new \Exception("Failed to prepare configuration table");
            }
            $this->connection = $connection;
        }
        return $this->connection;
    }

    private function prepareTables(\PDO $connection)
    {
        $stmt = $connection->query("SELECT `name` FROM `sqlite_master` WHERE type='table' AND name='configuration';");
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (!empty($result)) {
            return true;
        }
        $query = "
            CREATE TABLE `configuration` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `key` TEXT NOT NULL,
                `value` CHAR(512)
            );
        ";
        $tableCreated = $connection->exec($query);
        if ($tableCreated === 0) {
            $connection->exec("INSERT INTO `configuration` (`key`,`value`) VALUES ('application.name','DEVR')");
            $connection->exec("INSERT INTO `configuration` (`key`,`value`) VALUES ('application.version','1.0a')");
            $connection->exec("INSERT INTO `configuration` (`key`,`value`) VALUES ('environment.hierarchy','clients -> client -> project')");
            $connection->exec("INSERT INTO `configuration` (`key`,`value`) VALUES ('composer.download_url','http://getcomposer.org/composer.phar')");
            return true;
        }
        return false;
    }

}