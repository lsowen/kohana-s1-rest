<?php defined('SYSPATH') or die('No direct script access.');

class S1_Controller_Rest extends S1_Controller_Rest_Template
{
  const FORMAT_XML = "XML";
  const FORMAT_CSV = "CSV";
  const FORMAT_JSON = "JSON";
  const FORMAT_DEBUG = "DEBUG";
  
  
  public function request_format()
  {
    $format = $this->request->param('format');
    switch($format)
      {
      case 'xml':
	return self::FORMAT_XML;
      case 'csv':
	return self::FORMAT_CSV;
      case 'json':
	return self::FORMAT_JSON;
      case 'debug':
	return self::FORMAT_DEBUG;
      }
    
    if( $this->request->is_ajax() === TRUE )
      {
	return self::FORMAT_JSON;
      }
    
    return self::FORMAT_XML;
  }
  
  public function throw_error($code, $messages)
  {
    if( is_array($messages) === FALSE )
      {
	$messages = array($messages);
      }

    /* Stop gap to turn messages into array instead of hash */
    $messages = array_values($messages);

    $content = NULL;
    $format = $this->request_format();
    switch($format)
      {
      default:
      case self::FORMAT_XML:
	$this->response->headers('Content-Type', 'application/xml; charset=utf-8');
	$content = $this->template->content = View::factory('s1/rest/common/error/xml');
	break;
      case self::FORMAT_JSON:
	$this->response->headers('Content-Type', 'application/json; charset=utf-8');
	$content = $this->template->content = View::factory('s1/rest/common/error/json');
	break;
      }

    $content->code = $code;
    $content->messages = $messages;
    
    $this->response->status($code);
    $this->after();

    echo $this->response->send_headers(TRUE)->body();
    exit(0);
  }

  public function after()
  {
    if ($this->auto_render)
      {
	if( Kohana::$environment == Kohana::DEVELOPMENT && isset($this->template->content->errors) )
	  {
	    $this->template->errors = $this->template->content->errors;
	  }
      }
    parent::after();
  }
}