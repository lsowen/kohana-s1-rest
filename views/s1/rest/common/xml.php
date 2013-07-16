<?php

function format_item($xParent, $DataItem)
{
  foreach($DataItem->getSerializationArray() as $field => $specs)
    {	
      $label = isset($specs['label']) ? $specs['label'] : $field;
      
      $value = $DataItem->{$field};
      
      if( isset( $specs['formatter'] ) )
	{
	  $formatter = $specs['formatter'];
	  if( strpos($formatter, '::') === FALSE )
	    {
	      $fun = new ReflectionFunction($formatter);
	      $value = $fun->invokeArgs($value);
	    }
	  else
	    {
	      list($class, $method) = explode('::', $formatter, 2);
	      $method =  new ReflectionMethod($class, $method);
	      $value = $method->invokeArgs(NULL, array($value));
	    }
	}

      if( $value instanceof Custom_ORM_Serializable )
	{
	  $value = $value->getSerializationId();
	  $label = strtolower($label) . '_id';
	}
      else if( $value instanceof ORM )
	{
	  $value = $value->pk();
	  $label = strtolower($label) . '_id';
	}
      
      /* Work around the casting of bools to strings:
       * http://www.php.net/manual/en/language.types.string.php#language.types.string.casting
       */
      if( $value === TRUE )
	{
	  $xParent->{$label} = 'true';
	}
      else if( $value === FALSE )
	{
	  $xParent->{$label} = 'false';
	}
      else
	{
	  $xParent->{$label} = $value;
	}
    }
}

$x = new SimpleXMLElement('<Response/>');

$xContainer = $x->addChild($DataName);

if( $Data instanceof iterator || is_array($Data) === TRUE )
  {
    $item_name = Inflector::singular($DataName);
    foreach($Data as $DataItem)
      {
	format_item($xContainer->addChild($item_name), $DataItem);
      }
  }
else
  {
    format_item($xContainer, $Data);
  }

if( isset($Pagination) )
  {
    $xMeta = $x->addChild('meta');
    $xContainer = $xMeta->addChild('pagination');
    $xContainer->total = $Pagination['total'];
    $xContainer->limit = $Pagination['limit'];
    $xContainer->offset = $Pagination['offset'];
  }

echo $x->asXML();