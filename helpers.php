<?php

if (! function_exists('template_include') ):
	public function template_include($template, $data) {
		return D3turnes\Helpers\Template($template, $data);
	}
endif;