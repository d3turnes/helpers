<?php

if (!function_exists('template_include')) {
	function template_include($template, $data = array()) {
		return D3turnes\Helpers\Template::render($template, $data);
	}
}