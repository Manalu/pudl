<?php


require_once('pudlMySqli.php');



class pudlGalera extends pudlMySqli {
	use pudlMySqlHelper;


	public function __construct($data, $autoconnect=true) {
		if (!is_array($data['server'])) {
			throw new pudlException(
				'Not a valid server pool, $data[server] must be ARRAY data type'
			);
		}

		//SET INITIAL VALUES
		$this->pool = $this->onlineServers($data['server']);

		//RANDOMIZE SERVER POOL ORDER
		//IF REMOTE_ADDR AVAILABLE, USE IT TO HASH ROUTE TO SAME NODE EACH TIME
		if (!empty($_SERVER['REMOTE_ADDR'])) {
			srand( crc32($_SERVER['REMOTE_ADDR']) );
			shuffle($this->pool);
			srand();
		} else {
			shuffle($this->pool);
		}

		//CONNECT TO THE SERVER CLUSTER
		parent::__construct($data, $autoconnect);
	}



	public static function instance($data, $autoconnect=true) {
		return new pudlGalera($data, $autoconnect);
	}



	public function connect() {
		$auth = $this->auth();

		foreach ($this->pool as $server) {
			$this->mysqli = mysqli_init();

			//SET CONNECTION TIMEOUT TO 1 SECOND IF WE'RE IN A CLSUTER, ELSE 10 SECONDS
			$this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, count($this->pool)>1 ? 1 : 10);

			//ATTEMPT TO CREATE A PERSISTANT CONNECTION
			$ok = @$this->mysqli->real_connect(
				"p:$server",
				$auth['username'],
				$auth['password'],
				$auth['database']
			);

			//ATTEMPT TO CREATE A NON-PERSISTANT CONNECTION
			if (empty($ok)) {
				$ok = @$this->mysqli->real_connect(
					$server,
					$auth['username'],
					$auth['password'],
					$auth['database']
				);
			}

			//ATTEMPT TO SET UTF-8 CHARACTER SET
			//WE'RE GOOD, RETURN A GOOD RESULT!
			if ($ok  &&  @$this->mysqli->set_charset('utf8')) {
				$this->connected = $server;
				return true;
			}

			//OKAY, MAYBE WE'RE NOT
			$this->offlineServer($server);
		}

		//CANNOT CONNECT - ERROR OUT
		$error  = "<br />\n";
		$error .= 'Unable to connect to galera cluster "';
		$error .= implode(', ', $this->pool);
		$error .= '" with the username: "' . $auth['username'];
		$error .= "\"<br />\nError " . $this->connectErrno() . ': ' . $this->connectError();
		if (self::$die) die($error);
		return false;
	}



	public function reconnect() {
		if (empty($this->pool)) return false;

		array_shift($this->pool);

		if (empty($this->pool)) {
			if (self::$die) die('No more servers available in server pool');
			return false;
		}

		return $this->connect();
	}



	protected function process($query) {
		//PROPERLY HANDLE RE-ENTRY TO THIS FUNCTION
		$wait = $this->wait;
		$this->wait = false;

		if ($wait) {
			@$this->mysqli->query(
				'SET @wsrep_sync_wait_orig = @@wsrep_sync_wait'
			);
			if ($this->errno()) return new pudlMySqliResult(false, $this);

			@$this->mysqli->query(
				'SET SESSION wsrep_sync_wait = GREATEST(@wsrep_sync_wait_orig,'.$wait.')'
			);
			if ($this->errno()) return new pudlMySqliResult(false, $this);
		}


		$result = @$this->mysqli->query($query);

		switch ($this->errno()) {
			case 0: break; //NO ERRORS!

			//AN ERROR OCCURRED WITH THIS NODE, SO LET'S CONNECT TO A DIFFERENT NODE IN THE CLUSTER
			case 1047: // "WSREP HAS NOT YET PREPARED NODE FOR APPLICATION USE"
			case 1053: // "SERVER SHUTDOWN IN PROGRESS"
			case 2006: // "MYSQL SERVER HAS GONE AWAY"
			case 2062: // "READ TIMEOUT IS REACHED"
				if (!$this->reconnect()) return;
				if ($this->inTransaction()) {
					$result = $this->retryTransaction();
				} else {
					$result = $this->process($query);
				}
			break;

			//A DEADLOCKING CONDITION OCCURRED, SIMPLE, LET'S RETRY!
			case 1205: // "LOCK WAIT TIMEOUT EXCEEDED; TRY RESTARTING TRANSACTION"
			case 1213: // "DEADLOCK FOUND WHEN TRYING TO GET LOCK; TRY RESTARTING TRANSACTION"
				if ($this->inTransaction()) {
					usleep(50000);
					$result = $this->retryTransaction();

				//IT IS POSSIBLE TO DEADLOCK WITH A SINGLE QUERY
				//THIS CONDITION IS SIMPLE: JUST RETRY THE QUERY!
				} else {
					usleep(25000);
					$result = @$this->mysqli->query($query);

					//IF WE DEADLOCK AGAIN, TRY ONCE MORE BUT WAIT LONGER
					if ($this->errno() == 1205  ||  $this->errno() == 1213) {
						usleep(50000);
						$result = @$this->mysqli->query($query);
					}
				}
			break;
		}

		if ($wait  &&  !$this->errno()) {
			@$this->mysqli->query(
				'SET SESSION wsrep_sync_wait = @wsrep_sync_wait_orig'
			);
		}

		return new pudlMySqliResult($result, $this);
	}



	public function wait($wait=true) {
		$this->wait = ($wait === true) ? 7 : (int)$wait;
		return $this;
	}



	public function sync() {
		$auth	= $this->auth();
		$die	= self::$die;
		self::$die = false;
		foreach ($this->pool as $server) {
			if ($server == $this->connected) continue;
			$sync = pudlGalera::instance(['server'=>[$server]]+$auth);
			$sync->wait()->query('SELECT * FROM information_schema.GLOBAL_VARIABLES LIMIT 1');
		}
		self::$die = $die;
		return $this;
	}


	public function onlineServer($server) {
		$key	= ftok(__FILE__, 't');
		$shm	= shm_attach($key);
		$list	= shm_has_var($shm, 1) ? shm_get_var($shm, 1) : [];
		foreach ($list as $key => $value) {
			if ($value === $server) unset($list[$key]);
		}
		@shm_remove_var($shm, 1);
		@shm_put_var($shm, 1, $list);
		shm_detach($shm);
	}



	public function onlineServers($servers) {
		return $servers;
		if (count($servers) < 2) return $servers;

		$key	= ftok(__FILE__, 't');
		$shm	= shm_attach($key);

		if (!shm_has_var($shm, 1)) {
			shm_detach($shm);
			return $servers;
		}

		$list = shm_get_var($shm, 1);
		foreach ($servers as $index => &$item) {
			if (in_array($item, $list)) unset($servers[$index]);
		} unset($item);

		shm_detach($shm);
		return $servers;
	}



	public function offlineServer($server) {
		$key	= ftok(__FILE__, 't');
		$shm	= shm_attach($key);
		$list	= shm_has_var($shm, 1) ? shm_get_var($shm, 1) : [];
		if (!in_array($server, $list)) $list[] = $server;
		@shm_remove_var($shm, 1);
		@shm_put_var($shm, 1, $list);
		shm_detach($shm);
	}



	public function offlineServers() {
		$key	= ftok(__FILE__, 't');
		$shm	= shm_attach($key);
		$list	= shm_has_var($shm, 1) ? shm_get_var($shm, 1) : [];
		shm_detach($shm);
		return $list;
	}


	public function server()	{ return $this->connected; }
	public function pool()		{ return $this->pool; }


	private $pool		= [];
	private $wait		= false;
	private $connected	= false;
}
