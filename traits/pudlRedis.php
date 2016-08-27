<?php


trait pudlRedis {


	public function cache($seconds=0, $key=false) {
		$this->cache	= $seconds;
		$this->cachekey	= $key;
		return $this;
	}



	public function uncache($key=false) {
		$this->cache	= -1;
		$this->cachekey	= $key;
		return $this;
	}



	public function purge($key) {
		if (!$this->redis  ||  empty($key)) return false;
		try {
			return $this->redis->delete("pudl:$key");
		} catch (RedisException $e) {}
		return false;
	}



	public function redis($server=false) {
		if ($server === false) return $this->redis;

		if (is_object($server)  &&  is_a($server, 'Redis')) {
			$this->redis = $server;

		} else if (class_exists('Redis')) {
			try {
				$this->redis = new pudlRedisHack;
				if ($this->redis->connect($server, -1, 0.25)) {
					$this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
				} else {
					$this->redis = false;
				}
			} catch (RedisException $e) {
				$this->redis = false;
			}
		}

		if (!is_object($this->redis)) $this->redis = new pudlVoid;
		return $this->redis;
	}



	public function stats() {
		return $this->stats;
	}



	private function missed($query) {
		$this->stats['queries']++;
		$this->stats['misses']++;
		$this->stats['missed'][] = $query;
		return $this->process($query);
	}



	public function salt() {
		$auth = $this->auth();
		return $auth['salt'];
	}



	protected		$cache		= false;
	protected		$cachekey	= false;
	protected		$redis		= false;

	protected		$stats		= [
		'total'					=> 0,
		'queries'				=> 0,
		'hits'					=> 0,
		'misses'				=> 0,
		'missed'				=> [],
	];

}




//HHVM HACK BECAUSE THEY HAVE YET TO FIX THEIR CODE
if (class_exists('Redis')) {
	class pudlRedisHack extends Redis {
		protected function doConnect($host, $port, $timeout, $persistent_id,
									 $retry_interval, $persistent=false) {

			$level	= error_reporting(0);

			$return	= parent::doConnect($host, $port, $timeout, $persistent_id,
										$retry_interval, $persistent);

			error_reporting($level);

			return	$return;
		}
	}
}