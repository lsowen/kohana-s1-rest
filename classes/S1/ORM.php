<?php defined('SYSPATH') or die('No direct script access.');

class S1_ORM extends Kohana_ORM
{

  public function validate_exists($field)
  {
    if( array_key_exists($field, $this->_belongs_to) )
      {
	if( isset( $this->_related[$field] ) && $this->_related[$field]->loaded() )
	  {
	    return;
	  }
	
	if( ORM::factory($this->_belongs_to[$field]['model'], $this->_object[$this->_belongs_to[$field]['foreign_key']])->loaded() )
	  {
	    return;
	  }
      }

    $this->validation()->error($field, ':field does not exist');
  }
}