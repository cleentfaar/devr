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

    /**
     * @var array $data
     */
    private $data;

    /**
     * @var \PDO $connection
     */
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

    /**
     * @param array $data
     * @return bool
     */
    public function save(array $data)
    {
        $this->data = $data;
        foreach ($data as $key => $value) {
            $query = "SELECT `key` FROM `configuration` WHERE `key` = :key";
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute(array('key' => $key));
            $rows = $stmt->fetchAll();
            $params = array('key' => $key, 'value' => $value);
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

    /**
     * @return mixed
     */
    private function getData()
    {
        if (!isset($this->data)) {
            $connection = $this->getConnection();
            $query = "SELECT key,value FROM `configuration`";
            $stmt = $connection->query($query);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $this->data[$row['key']] = $row['value'];
            }
        }
        return $this->data;
    }

    /**
     * @return \PDO
     * @throws \Exception
     */
    private function getConnection()
    {
        if (!isset($this->connection)) {
            $dbDir = DEVR_ROOT_DIR . "/app/db";
            $filesystem = new Filesystem();
            if (!is_dir($dbDir)) {
                $filesystem->mkdir($dbDir);
            }
            $filesystem->chmod($dbDir, 0777);
            if (defined("DEVR_TEST_MODE")) {
                //$pathToDb = $dbDir . '/devr.test.'.uniqid().'.sq3';
                $pathToDb = ':memory:';
            } else {
                $pathToDb = $dbDir . '/devr.sq3';
            }
            if ($filesystem->exists($pathToDb)) {
                $filesystem->chmod($pathToDb, 0777);
            }
            $connection = new \PDO(
                'sqlite:' . $pathToDb,
                null,
                null,
                array(
                    \PDO::ATTR_PERSISTENT => true,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                )
            );
            if (!$this->prepareTables($connection)) {
                throw new \Exception("Failed to prepare tables");
            }
            $this->connection = $connection;
        }
        return $this->connection;
    }

    /**
     * @param \PDO $connection
     * @return bool
     */
    private function prepareTables(\PDO $connection)
    {
        $stmt = $connection->query("SELECT `name` FROM `sqlite_master` WHERE type='table' AND name='configuration';");
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (!empty($result)) {
            return true;
        }
        return $this->prepareConfigurationTable($connection);
    }

    /**
     * @param \PDO $connection
     * @return bool
     */
    private function prepareConfigurationTable(\PDO $connection)
    {
        $query = "
            CREATE TABLE `configuration` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `key` TEXT NOT NULL,
                `value` CHAR(512)
            );
        ";
        $tableCreated = $connection->exec($query);
        if ($tableCreated === 0) {
            $stmt = $connection->prepare("INSERT INTO `configuration` (`key`,`value`) VALUES ('application.name',:value)");
            $stmt->execute(array('value' => 'DEVR'));
            $stmt = $connection->prepare("INSERT INTO `configuration` (`key`,`value`) VALUES ('application.version',:value)");
            $stmt->execute(array('value' => '1.0a'));
            $stmt = $connection->prepare("INSERT INTO `configuration` (`key`,`value`) VALUES ('environment.hierarchy',:value)");
            $stmt->execute(array('value' => 'clients -> client -> project'));
            $stmt = $connection->prepare("INSERT INTO `configuration` (`key`,`value`) VALUES ('composer.download_url',:value)");
            $stmt->execute(array('value' => 'http://getcomposer.org/composer.phar'));
            if (defined("DEVR_TEST_MODE")) {
                $this->insertTestingConfiguration($connection);
            }
            return true;
        }
        return false;
    }

    /**
     * @param \PDO $connection
     * @return bool
     */
    private function insertTestingConfiguration(\PDO $connection)
    {
        $skeletonDir = DEVR_CACHE_DIR . '/tests/project_skeleton';
        $clientsDir = DEVR_CACHE_DIR . '/tests/clients';

        $filesystem = new Filesystem();
        $filesystem->mkdir($skeletonDir);
        $filesystem->mkdir($clientsDir);

        $stmt = $connection->prepare("INSERT INTO `configuration` (`key`,`value`) VALUES ('projects.skeleton_dir', :value)");
        $stmt->execute(array('value' => $skeletonDir));
        $stmt = $connection->prepare("INSERT INTO `configuration` (`key`,`value`) VALUES ('projects.clients_dir',:value)");
        $stmt->execute(array('value' => $clientsDir));
        return true;
    }
}
