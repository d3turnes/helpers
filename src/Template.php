<?php

namespace D3turnes\Helpers;

class Template {
	
	public static $path = '/templates';
	
	public static function render($template, $data = []) {
		
		$data?extract($data):false;
		 
		if ( !is_dir(self::$path) || !is_readable(self::$path) )
			throw new \InvalidArgumentException(sprintf("The directory %s not exists", self::$path));
		
		if ( !file_exists(sprintf("%s/%s.php", self::$path, $template)) )
			throw new \Exception(sprintf("File %s.php not found in %s/", $template, self::$path));
		 
		ob_start();
        //include( PLUGIN_PATH . DIRECTORY_SEPARATOR . 'resources/templates' . DIRECTORY_SEPARATOR . $template . '.php');
        include ( self::$path . DIRECTORY_SEPARATOR . $template . '.php' );
		$content = ob_get_contents();
        ob_end_clean();
        echo $content;
		
	}
	
}