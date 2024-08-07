<?php
/**
 * Lesti_Fpc (http:gordonlesti.com/lestifpc)
 *
 * PHP version 5
 *
 * @link      https://github.com/GordonLesti/Lesti_Fpc
 * @package   Lesti_Fpc
 * @author    Gordon Lesti <info@gordonlesti.com>
 * @copyright Copyright (c) 2013-2016 Gordon Lesti (http://gordonlesti.com)
 * @license   http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

/**
 * Class Lesti_Fpc_Test_Model_Observer_Clean
 */
class Lesti_Fpc_Test_Model_Observer_Clean extends Lesti_Fpc_Test_TestCase
{
    protected Lesti_Fpc_Model_Observer_Clean $_cleanObserver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_cleanObserver = Mage::getSingleton('fpc/observer_clean');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mage::unregister('_singleton/fpc/observer_clean');
    }

    /**
     * @test
     */
    public function testCoreCleanCache()
    {
        $data = new \Lesti_Fpc_Model_Fpc_CacheItem('fpc_old_data', time(), 'text/html');
        $this->_fpc->save($data, 'fpc_old_id', array('fpc'), 1);
        sleep(2);
        $this->_cleanObserver->coreCleanCache();
        $this->assertFalse($this->_fpc->remove('fpc_old_id'));
    }

    /**
     * @test
     */
    public function testAdminhtmlCacheFlushAll()
    {
        $data = new \Lesti_Fpc_Model_Fpc_CacheItem('fpc_old_data', time(), 'text/html');
        $this->_fpc->save($data, 'fpc_id');
        Mage::dispatchEvent('adminhtml_cache_flush_all');
        $this->assertFalse($this->_fpc->remove('fpc_id'));
    }

    public function testAdminhtmlCacheFlushSystem()
    {
        $data = new \Lesti_Fpc_Model_Fpc_CacheItem('fpc_old_data', time(), 'text/html');
        $this->_fpc->save($data, 'fpc_id');
        Mage::dispatchEvent('adminhtml_cache_flush_system');
        $this->assertFalse($this->_fpc->remove('fpc_id'));
    }

    public function testAdminhtmlCacheRefreshType()
    {
        $data = new \Lesti_Fpc_Model_Fpc_CacheItem('test_data', time(), 'text/html');

        $this->_fpc->save($data, 'test_id');
        Mage::dispatchEvent('adminhtml_cache_refresh_type', ['type' => 'core']);
        $this->assertEquals($data, $this->_fpc->load('test_id'));

        $this->_fpc->clean();
        $this->_fpc->save($data, 'test_id');
        Mage::dispatchEvent('adminhtml_cache_refresh_type', ['type' => 'core']);
        Mage::dispatchEvent('adminhtml_cache_refresh_type', ['type' => Lesti_Fpc_Model_Observer_Clean::CACHE_TYPE]);
        $this->assertFalse($this->_fpc->load('test_id'));

        $this->_fpc->clean();
        $this->_fpc->save($data, 'test_id');
        Mage::app()->getCacheInstance()->banUse('fpc');
        Mage::dispatchEvent('adminhtml_cache_refresh_type', ['type' => 'core']);
        Mage::dispatchEvent('adminhtml_cache_refresh_type', ['type' => Lesti_Fpc_Model_Observer_Clean::CACHE_TYPE]);
        $this->assertEquals($data, $this->_fpc->load('test_id'));
    }
}
