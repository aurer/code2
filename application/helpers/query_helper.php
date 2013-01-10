<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('querydata'))
{
    function querydata($query_object)
	{
        $data = array();
		if( method_exists($query_object, 'result_array')){
			foreach($query_object->result_array() as $row){
				$data[] = $row;
			}
		}
        return $data;
	}
	
	function slug($string){
		$string = str_to_lower($string);
		$string = str_replace(' ', '-', $string);
		return $string;
	}
}