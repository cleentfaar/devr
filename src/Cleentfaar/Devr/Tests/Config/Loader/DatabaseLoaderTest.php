<?php
/**
 * This file is part of the DEVR CLI-script
 *
 * @author Cas Leentfaar
 * @license http://github.com/cleentfaar/devr
 */
namespace Cleentfaar\Devr\Tests\Config\Loader;

/**
 * Class DatabaseLoader
 * @package Cleentfaar\Devr\Config\Loader
 */
class DatabaseLoaderTest
{

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

    /**
     * @param array $data
     */
    public function testSave(array $data)
    {
        $mockDatabaseLoader = $this->mockObject('\Cleentfaar\Devr\Config\Loader\DatabaseLoader');
        $this->assertEquals('bool', gettype($mockDatabaseLoader->save(array('somekey'=>'somevalue'))));
        $this->assertEquals('somevalue', $mockDatabaseLoader->get('somekey'));
    }
}
