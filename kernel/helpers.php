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
