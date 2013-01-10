<?php

function uri_part($part){
	$parts = explode('/', uri_string());
	array_unshift($parts, site_url());
	
	if(isset($parts[$part]))
		return $parts[$part];
	else
		return false;
}

function uri_count(){
	return count( explode('/', uri_string() ) );
}