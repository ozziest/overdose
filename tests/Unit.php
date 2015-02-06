<?php

use Ozziest\Overdose\Overdose;

class UnitTest extends PHPUnit_Framework_TestCase {

    /**
     * Setting up 
     *
     * @return null
     */
    public function setUp()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->cache = Mockery::mock('Desarrolla2\Cache\Cache'); 
    }

    /**
     * Tear down 
     *
     * @return null
     */
    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * Secure test
     *
     * @test
     */
    public function secure()
    {
        $this->cache->shouldReceive('get')->with('api_oversode_recreation_127.0.0.1')->andReturn(time() - 10);
        $this->cache->shouldReceive('get')->with('api_oversode_request_127.0.0.1');
        $this->cache->shouldReceive('get')->with('api_oversode_overdose_127.0.0.1');
        $this->cache->shouldReceive('set')->atLeast()->times(2);
        $overdose = new Overdose($this->cache);        
        $this->assertTrue($overdose->isSecure());
    }

    /**
     * Recreation test
     *
     * @expectedException Ozziest\Overdose\OverdoseException
     * @test
     */
    public function recreation()
    {
        $this->cache->shouldReceive('get')->with('api_oversode_recreation_127.0.0.1')->andReturn(time() + 10);
        $overdose = new Overdose($this->cache);        
        $overdose->isSecure();
    }

    /**
     * Secure test
     *
     * @expectedException Ozziest\Overdose\OverdoseException
     * @expectedExceptionMessage You perform a lot of requests. -10 sec.
     * @test
     */
    public function overdose()
    {
        $this->cache->shouldReceive('get')->with('api_oversode_recreation_127.0.0.1')->andReturn(time() - 10);
        $this->cache->shouldReceive('get')->with('api_oversode_request_127.0.0.1')->andReturn(time());
        $this->cache->shouldReceive('get')->with('api_oversode_overdose_127.0.0.1')->andReturn(24);
        $this->cache->shouldReceive('get')->with('api_oversode_critical_127.0.0.1');
        $this->cache->shouldReceive('set');
        $overdose = new Overdose($this->cache);        
        $this->assertTrue($overdose->isSecure());
    }    

}