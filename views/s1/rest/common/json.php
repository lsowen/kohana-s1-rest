<?php

function format_item($DataItem)
{
  $result = array();
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
      
      if( $value instanceof S1_REST_ISerializable )
	{
	  $value = $value->getSerializationId();
	  $label = strtolower($label) . '_id';
	}
      else if( $value instanceof ORM )
	{
	  $value = $value->pk();
	  $label = strtolower($label) . '_id';
	}
      
      $result[$label] = $value;
    }

  return $result;
}

$results = array();
if( $Data instanceof iterator || is_array($Data))
  {
    $data_results = array();
    foreach($Data as $DataItem)
      {
	$data_results[] = format_item($DataItem);
      }
    $results[$DataName] = $data_results;

  }
else
  {
    $data_result = format_item($Data);
    $results[$DataName] = $data_result;
  }

if( isset($Pagination) )
  {
    $results['meta'] = array();
    $results['meta']['pagination'] = array(
					   'total'  => $Pagination['total'],
					   'limit'  => $Pagination['limit'],
					   'offset' => $Pagination['offset'],
					   );
  }
echo json_encode($results);