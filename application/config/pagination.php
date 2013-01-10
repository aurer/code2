<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$config['first_link'] = false;
$config['last_link'] = false;

$config['use_page_numbers'] = TRUE;

// Wrapper for the pagination
$config['full_tag_open'] = '<ul class="pagination reset">';
$config['full_tag_close'] = "</ul>";

// The 'Prev' 'ink
$config['prev_tag_open']	= "\n<li class='prev'>";
$config['prev_tag_close']	= "</li>\n";
$config['prev_link'] = '&lt;';

// The 'Next' link
$config['next_tag_open']	= "<li class='next'>";
$config['next_tag_close']	= "</li>\n";
$config['next_link'] = '&gt;';

// Numbered items
$config['num_tag_open']		= '<li>';
$config['num_tag_close']	= "</li>\n";

// The current item
$config['cur_tag_open']		= "<li class='current'><strong>";
$config['cur_tag_close']	= "</strong></li>\n";