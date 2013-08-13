<?php defined('SYSPATH') or die('No direct script access.');

interface S1_REST_ISerializable
{

  public function getSerializationId();
  
  public function getSerializationArray();

}