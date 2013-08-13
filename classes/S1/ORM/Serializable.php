<?php defined('SYSPATH') or die('No direct script access.');

class S1_ORM_Serializable extends ORM implements S1_REST_ISerializable
{
  /* 
   * Why extend ORM instead of S1_ORM?
   * Many parts of Kohana_ORM check if the item passed in
   * is an instanceof ORM (eg add or count_relations).
   */

  protected $_serializationArray = NULL;

  /*
   * Name of API endpoint for this object type
   */
  protected $_public_object_name = NULL;

  public function public_object_name()
  {
    if( is_null( $this->_public_object_name ) === FALSE )
      {
	return $this->_public_object_name;
      }
    else
      {
	$object_name = explode("_", $this->object_name());
	return end($object_name);
      }
  }

  public function getSerializationId()
  {
    return $this->pk();
  }
  
  public function getSerializationArray()
  {
    if( $this->_serializationArray !== NULL )
      {
	return $this->_serializationArray;
      }
    else
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
	$object_name = Inflector::plural($this->public_object_name());
	return URL::site(Route::get( Kohana::$config->load('kohana-s1-rest.api.route') )->uri(array('controller' => $object_name, 'id' => $this->getSerializationId())));
      default:
	return parent::__get($column);
      }
  }
}