<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Tests\Config\Loader;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class DatabaseLoader
 * @package Cleentfaar\Devr\Config\Loader
 */
class DatabaseLoaderTest
{

    private $data;

    private $connection;

    /**
     * @param $key
     * @return null
     */
    public function testGet()
    {
        $key = 'application.name';
        $expectedValue = 'DEVR';
        $mockDatabaseLoader = $this->mockObject('\Cleentfaar\Devr\Config\Loader\DatabaseLoader');
        $this->assertRegExp('/'.$expectedValue.'/', $mockDatabaseLoader->get($key));
    }

    /**
     * @return array
     */
    public function testGetAll()
    {
        $mockDatabaseLoader = $this->mockObject('\Cleentfaar\Devr\Config\Loader\DatabaseLoader');
        $this->assertEquals('array', gettype($mockDatabaseLoader->getAll()));
    }

    public function testSave(array $data)
    {
        $mockDatabaseLoader = $this->mockObject('\Cleentfaar\Devr\Config\Loader\DatabaseLoader');
        $this->assertEquals('bool', gettype($mockDatabaseLoader->save(array('somekey'=>'somevalue'))));
        $this->assertEquals('somevalue', $mockDatabaseLoader->get('somekey'));

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
            $pathToDb = DEVR_ROOT_DIR . "/app/db/devr.sq3";
            if (!is_dir(dirname($pathToDb))) {
                $filesystem = new Filesystem();
                $filesystem->mkdir(dirname($pathToDb));
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
            $inserted = $connection->exec("INSERT INTO `configuration` (`key`,`value`) VALUES ('application.name','DEVR')");
            var_dump($inserted);
            $inserted = $connection->exec("INSERT INTO `configuration` (`key`,`value`) VALUES ('application.version','1.0a')");
            var_dump($inserted);
            $inserted = $connection->exec("INSERT INTO `configuration` (`key`,`value`) VALUES ('environment.hierarchy','clients -> client -> project')");
            var_dump($inserted);
            return true;
        }
        return false;
    }

}