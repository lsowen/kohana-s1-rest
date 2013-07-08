<?php defined('SYSPATH') or die('No direct script access.');

class S1_ORM_Serializable extends ORM
{
  /* 
   * Why extend ORM instead of S1_ORM?
   * Many parts of Kohana_ORM check if the item passed in
   * is an instanceof ORM (eg add or count_relations).
   */

  public $_serializationArray = NULL;

  public function getSerializationId()
  {
    return $this->pk();
  }
  
  public function getSerializationArray()
  {
    if( $this->_serializationArray === NULL )
      {
	$table_columns = array_keys($this->_table_columns);
	foreach($table_columns as $key => $value)
	  {
	    if( substr($value, -1 * strlen($this->_foreign_key_suffix)) === $this->_foreign_key_suffix )
	      {
		unset($table_columns[$key]);
	      }
	  }

	return array_flip($table_columns) + array('location' => '') + $this->belongs_to();
      }
    else
      {
	return $this->_serializationArray;
      }
  }


  public static function serialize_datetime($value)
  {
    if ( $value instanceof DateTime )
      {
	return $value->format('Y-m-d\\TH:i:s');
      }

    if( !is_int($value) )
      {
	$value = (int)$value;
      }

    return gmdate('Y-m-d\\TH:i:s+00:00', $value);
  }

  public static function serialize_timezone($value)
  {
    if ( $value instanceof DateTimeZone )
      {
	return $value->getName();
      }

    return $value;
  }

  public function __get($column)
  {
    switch($column)
      {
      case 'location':
	$object_name = explode("_", $this->object_plural());
	return URL::site(Route::get( Kohana::$config->load('oauth.api.route') )->uri(array('controller' => end($object_name), 'format' => '', 'id' => $this->getSerializationId())));
      default:
	return parent::__get($column);
      }
  }
}