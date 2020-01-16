<?php

function pf_get_bigger_bp($breakpoints){
  foreach($breakpoints as $bp){
    if($bp){
      return $bp;
    }
  }
}
function pf_get_smaller_bp($breakpoints){
  foreach( array_reverse($breakpoints) as $bp){
    if($bp){
      return $bp;
    }
  }
}


function pf_sanitize_output($buffer){
	$search = array(
		'/\>[^\S ]+/s',     // strip whitespaces after tags, except space
		'/[^\S ]+\</s',     // strip whitespaces before tags, except space
		'/(\s)+/s',         // shorten multiple whitespace sequences
		'/<!--(.|\s)*?-->/' // Remove HTML comments
	);
	$replace = array(
		'>',
		'<',
		'\\1',
		''
	);
	$buffer = preg_replace($search, $replace, $buffer);
	return $buffer;
}
