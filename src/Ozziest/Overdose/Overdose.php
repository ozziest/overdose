<?php namespace Ozziest\Overdose;

use Desarrolla2\Cache\Cache;

class Overdose {

    /**
     * Key 
     *
     * @var object
     */
    private $key;

    /**
     * Allowed second 
     *
     * @var int
     */
    private $acceptable = 1;

    /** 
     * Safe second
     *
     * @var int
     */
    private $safe = 10;

    /**
     * Allowed overdose count 
     *
     * @var int
     */
    private $max = 25;

    /**
     * Recreation time 
     *
     * @var int
     */
    private $recreation = 60;

    /**
     * Overdose constructer
     *
     * This method is setting keys of values for request numbers.
     *
     * @param  Cache        $cache
     * @return null
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
        $ip = $_SERVER['REMOTE_ADDR'];
        // Setting keys 
        $this->key = (object) [
                'request'    => "api_oversode_request_{$ip}",
                'overdose'   => "api_oversode_overdose_{$ip}",
                'recreation' => "api_oversode_recreation_{$ip}",
                'critical'   => "api_oversode_critical_{$ip}"
            ];
    }

    /**
     * Set configurations
     *
     * @param  array        $configs
     * @return null
     */
    public function set(Array $configs)
    {
        $alloweds = ['acceptable', 'safe', 'max', 'recreation'];
        foreach ($configs as $config => $value) {
            if (in_array($config, $alloweds) && is_int($value)) {
                $this->{$config} = $value;
            }
        }
        return $this;
    }

    /**
     * Security control for overdose request 
     *
     * @return null
     */
    public function secure()
    {
        $this->recreation();
        list($request, $overdose) = $this->getDatas();
        $before = time() - $request;
        if ($this->increase($before, $overdose)) {
            $this->decrease($before, $overdose);
        }
        $this->cache->set($this->key->request, time());
        $this->overdose($overdose);
    }

    /**
     * Getting remain time 
     *
     * @return integer
     */
    public function getRemainTime()
    {
        $recreation = $this->cache->get($this->key->recreation);
        return $recreation - time();
    }

    /**
     * Recreation
     *
     * @return true;
     */
    private function recreation()
    {
        $recreation = $this->cache->get($this->key->recreation);
        if ($recreation > time()) {
            $remain = $this->getRemainTime();
            throw new OverdoseException("You perform a lot of requests. $remain sec.");
        }
    }

    /**
     * Checking overdose 
     *
     * @param  integer      $count
     * @return null
     */
    private function overdose($count)
    {
        if ($count >= $this->max) {
            // Kaç kez overdose olunmuş öğreniliyor.
            $critical = $this->getOrSet($this->key->critical, 0);
            // Overdose sayısı arttırılıyor.
            $critical++;
            $allowedTime = time() + (($critical * $critical) * $this->recreation);
            $this->cache->set($this->key->recreation, $allowedTime);
            $this->cache->set($this->key->overdose, 0);
            $this->cache->set($this->key->critical, $critical);
            $remain = $this->getRemainTime();            
            throw new OverdoseException("You perform a lot of requests. $remain sec. ");
        }
    }

    /**
     * Overdose is decreasing
     *
     * @param  integer      $before 
     * @param  pointer      $overdose 
     */
    private function decrease($before, &$overdose)
    {
        if ($before > $this->safe) {
            $overdose--;
            $this->cache->set($this->key->overdose, $overdose);
        }
    }

    /**
     * Overdose is increasing
     *
     * @param  integer      $before 
     * @param  pointer      $overdose 
     */
    private function increase($before, &$overdose)
    {
        if ($before < $this->acceptable) {
            $overdose++;
            $this->cache->set($this->key->overdose, $overdose);
            return false;
        }
        return true;
    }

    /**
     * Getting datas 
     *
     * @return array
     */
    private function getDatas()
    {
        return [
                $this->getOrSet($this->key->request, time()),
                $this->getOrSet($this->key->overdose, 0)
            ];
    }

    /**
     * Getting or setting session data
     *
     * @param  string       $key 
     * @param  string       $default
     * @return string
     */
    private function getOrSet($key, $default)
    {
        $value = $this->cache->get($key);
        if ($value === false) {
            $value = $default;
            $this->cache->set($key, $value);
        }
        return $value;
    }

}