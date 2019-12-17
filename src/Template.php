<?php

namespace D3turnes\Helpers;

class Template {
	
	public static $path = 'templates/';
	
	public static function render($template, $data = []) {
		
		$data?extract($data):false;
		
		$path = rtrim(self::$path, '/');
		 
		if ( !is_dir($path) || !is_readable($path) )
			throw new \InvalidArgumentException(sprintf("The directory %s not exists", $path));
		
		if ( !file_exists(sprintf("%s/%s.php", $path, $template)) )
			throw new \Exception(sprintf("File %s.php not found in %s/", $template, $path));
		 
		ob_start();
        include ( $path . DIRECTORY_SEPARATOR . $template . '.php' );
		$content = ob_get_contents();
        ob_end_clean();
        echo $content;
	}
	
}