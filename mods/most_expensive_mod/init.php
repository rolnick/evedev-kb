<?php
event::register('home_assembling', 'most_expensive::add');

class most_expensive{
  function add($home){
    $home->addBehind("contracts", "most_expensive::generate");
  }
  
  function generate(){
    include_once('mods/most_expensive_mod/most_expensive.php');
    return $html;
  }
}



?>