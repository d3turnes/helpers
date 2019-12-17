<?php

namespace D3turnes\Helpers;

class Config {
	

	/**
     * The root config directory.
     * @var string
     */
	private $path = 'config';

	/**
	 * The file '.php' to load without extension.
	 * @var string
	 */
	private $file = 'app';

	/**
	 * The path complete 'conf/app.php'
	 */
	private $fileName;

	/**
	 * The Array of options.
	 * @var array
	 */
	private $items;
	
	
	/**
	 * Constructor
	 * @params array $options
	 */
	public function __construct(array $options = []) {
		
		$properties = ['path', 'file'];
		foreach ($properties as $name) {
			if ( isset($options[$name]) ) {
				$this->{$name} = $options[$name];
			}
		}

		$fileName = str_replace('\\', '/', sprintf('%s/%s.php', $this->path, $this->file));
		if (!file_exists($fileName)) throw new \Exception(__(sprintf('File [%s] not found', $fileName)));

		$this->fileName = $fileName;
		$this->items = require $fileName;
	}

	/**
	 * Print values
	 */
	public function print() {
		return sprintf("path: %s, file: %s, fileName: %s", $this->path, $this->file, $this->fileName);
	}

	/**
	 * Checks if the given key or index exists in the array
	 * @param array $array
	 * @param string $key
	 */
	protected function exists($array, $key) {
		return array_key_exists($key, $array);
    }
	
	/**
	 * Retrieve the value of a given key
	 * @param string $key, where key = 'file.key1[.key2]'
	 * @param string $default
	 */
	public function get($key, $default = null) {

		$key = $this->first( $key );

		if (is_null($key)) return $this->items;
  
		if (isset($this->items[$key])) return $this->items[$key];
		
		if (strpos($key, '.') === false) {
            return $default;
        }
		
		$items = $this->items;		
		
		foreach (explode('.', $key) as $segment) {
			if ( ! is_array($items) || ! array_key_exists($segment, $items)) {
			  return value($default);
			}
		  
			$items = &$items[$segment];
		}
		
		return $items;	
	}
	
	/**
	 * Set the configuration value of a given key at runtime
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value = null) {
		
		if ( is_array($key) ) {

			foreach ($key as $k => $v) {
				$this->set($k, $v);
			}
			return;
		}

		$items = &$this->items;
		//$keys = $this->first($key);
        foreach (explode('.', $key) as $key) {
            if (!isset($items[$key]) || !is_array($items[$key])) {
                $items[$key] = [];
            }
            $items = &$items[$key];
        }
		
        $items = $value;
		
	}

	/**
	 * Delete items of the same configuration file
	 * @param array $keys
	 */
	public function delete($keys) {

		if (!is_array($keys)) return;

		foreach ( $keys as $key ) {

			if ( $this->exists($this->items, $key)) {
				unset($this->items[$key]);
                continue;
			}
			
			$items = &$this->items;
			$segments = explode('.', $key);
			$lastSegment = array_pop($segments);
			foreach ($segments as $segment) {
                if (!isset($items[$segment]) || !is_array($items[$segment])) {
					continue 2;
				}
                $items = &$items[$segment];
            }
            unset($items[$lastSegment]);

		}

	}

	/**
	 * Remove items from the different configuration file and save the change
	 * @param array $keys
	 */
	public function purge($keys) {
		if (!is_array($keys) || empty($keys)) return;

		foreach ( $keys as $key ) {
			$key = $this->first($key);

			if ($this->exists($this->items, $key)) {
                unset($this->items[$key]);
                continue;
			}

			$items = &$this->items;
			$segments = explode('.', $key);
			$lastSegment = array_pop($segments);
			foreach ($segments as $segment) {
                if (!isset($items[$segment]) || !is_array($items[$segment])) {
					continue 2;
				}
                $items = &$items[$segment];
            }
            unset($items[$lastSegment]);
			
			// apply changes
			$this->save();
		}		
	}

	/**
	 * Return all items
	 * @return array
	 */
	public function getItems() {
		return $this->items;
	}
	
	/**
	 * Store $items to disk
	 * <?php return [
	 * 		'key1' => [
	 * 			'item-1' => 'value-1'
	 * 		],
	 * 		'key2' => 'value-2'
	 * ];
	 */
	public function save() {
		
		$data = $this->var_export_pretty($this->items, true);
		file_put_contents($this->fileName, '<?php return ' . $data . ';', LOCK_EX);
		
	}
	
	/**
	 *  Private functions
	 */
	private function first($key) {
		
		$parts = explode('.', $key);
		
		if ( count($parts) > 1 ) {

			$fileName = str_replace('\\', '/', sprintf('%s/%s.php', $this->path, $parts[0]));
			if (!file_exists($fileName)) throw new \Exception(__(sprintf('File [%s] not found', $fileName)));

			$this->file = $parts[0];
			$this->fileName = $fileName;
			$this->items = require $fileName;

			return ltrim($key, "${parts[0]}.");
		}
		
		return $key;
	}
	
	/**
	 * Convert array() to [] pretty
	 */
	private function var_export_pretty($expression, $return = false) {
		$export = var_export($expression, TRUE);
		$patterns = [
			"/array \(/" => '[',
			"/^([ ]*)\)(,?)$/m" => '$1]$2',
			"/=>[ ]?\n[ ]+\[/" => '=> [',
			"/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
		];
		$export = preg_replace(array_keys($patterns), array_values($patterns), $export);
		if ((bool)$return) return $export; else echo $export;
	}
	
}