<?php defined('SYSPATH') or die('No direct script access.');

class S1_Controller_Rest_Standard extends S1_Controller_Rest
{
  public $ItemType = NULL;
  public $ItemName = NULL;

  public function index_actions()
  {
    return array(
		 Request::GET => array(
				       'handle_get_list' => array('filter' => array('id' => NULL)),
				       'handle_get_item' => array(),
				       ),
		 Request::PUT => array(
				       'handle_put_item' => array(),
				       ),
		 Request::POST => array(
					'handle_post_item' => array('filter' => array('id' => NULL)),
					),
		 );
		 
  }

  /*
   * Function associated with GET requests for a single item
   */

  protected function handle_get_item($args = array())
  {
    $args = $args + array('key' => 'id');
    $Item = ORM::factory($this->ItemType, array($args['key'] => $this->request->param('id')));


    if( $Item->loaded() )
      {
	$content = NULL;
	$format = $this->request_format();
	switch($format)
	  {
	  default:
	  case self::FORMAT_XML:
	    $this->response->headers('Content-Type', 'application/xml; charset=utf-8');
	    $content = $this->template->content = View::factory('s1/rest/common/xml');
	    break;
	  case self::FORMAT_JSON:
	    $this->response->headers('Content-Type', 'application/json; charset=utf-8');
	    $content = $this->template->content = View::factory('s1/rest/common/json');
	    break;
	  }
	
	$content->Data = $Item;
	$ItemName = isset($this->ItemName) ? $this->ItemName : $this->ItemType;
	$content->DataName = strtolower($ItemName);
      }
    else
      {
	$this->throw_error(404, 'id not found');
      }
  }

  /*
   * Functions associated with GET requests for multiple items
   */

  protected function handle_get_list_search(ORM $sql)
  {
    return $sql;
  }

  protected function handle_get_list_sort(ORM $sql)
  {
    if($this->request->query('sort') !== NULL)
      {
	$sorts = explode(",", $this->request->query('sort'));
	$sort_options = $sql->getSerializationArray();
	foreach($sorts as $sort)
	  {
	    $sort_dir = 'ASC';
	    switch(substr($sort,0,1))
	      {
	      case '-':
		$sort_dir = 'DESC';
		$sort = substr($sort,1);
		break;
	      case '+':
		$sort_dir = 'ASC';
		$sort = substr($sort,1);
		break;
	      }
	    
	    if(array_key_exists($sort, $sort_options))
	      {
		$sql->order_by($sort, $sort_dir);
	      }
	    
	  }
      }
    return $sql;
  }

  protected function handle_get_list($args = array())
  {
    $sql = ORM::factory($this->ItemType);

    $content = NULL;
    $format = $this->request_format();
    switch($format)
      {
      default:
      case self::FORMAT_XML:
	$this->response->headers('Content-Type', 'application/xml; charset=utf-8');
	$content = $this->template->content = View::factory('s1/rest/common/xml');
	break;
      case self::FORMAT_JSON:
	$this->response->headers('Content-Type', 'application/json; charset=utf-8');
	$content = $this->template->content = View::factory('s1/rest/common/json');
	break;
      }

    $sql = $this->handle_get_list_search($sql);
    $content->Pagination = $this->paginate_results($sql);

    $sql = $this->handle_get_list_search($sql);
    $sql = $this->handle_get_list_sort($sql);
    $content->Data = $sql->find_all();

    $ItemName = isset($this->ItemName) ? $this->ItemName : $this->ItemType;
    $content->DataName = Inflector::plural(strtolower($ItemName));

    //echo View::factory('profiler/stats');
    //exit;
  }


  /* 
   * Functions associated with PUT requests to update a single item
   */

  protected function handle_put_item_update($Item, $args = array())
  {
    return $Item;
  }


  protected function handle_put_item($args = array())
  {
    $args = $args + array('key' => 'id');
    $Item = ORM::factory($this->ItemType, array($args['key'] => $this->request->param('id')));
    
    
    if( $Item->loaded() )
      {
	$Item = $this->handle_put_item_update($Item, $args);
	try 
	  {
	    $Item = $Item->update();
	  }	
	catch(ORM_Validation_Exception $e)
	  {
	    $errors = $e->errors('s1/rest');
	    $this->throw_error(400, $errors);
	  }

	$content = NULL;
	$format = $this->request_format();
	switch($format)
	  {
	  default:
	  case self::FORMAT_XML:
	    $this->response->headers('Content-Type', 'application/xml; charset=utf-8');
	    $content = $this->template->content = View::factory('s1/rest/common/xml');
	    break;
	  case self::FORMAT_JSON:
	    $this->response->headers('Content-Type', 'application/json; charset=utf-8');
	    $content = $this->template->content = View::factory('s1/rest/common/json');
	    break;
	  }
	
	$content->Data = $Item;
	$ItemName = isset($this->ItemName) ? $this->ItemName : $this->ItemType;
	$content->DataName = strtolower($ItemName);
      }
    else
      {
	$this->throw_error(404, 'id not found');
      }
  }


  /* 
   * Functions associated with POST requests to update a single item
   */

  protected function handle_post_item_create($Item, $args = array())
  {
    return $Item;
  }


  protected function handle_post_item($args = array())
  {
    $Item = ORM::factory($this->ItemType);
    
    $Item = $this->handle_post_item_create($Item, $args);

    try 
      {
	$Item = $Item->create();
      }	
    catch(ORM_Validation_Exception $e)
      {
	$errors = $e->errors('s1/rest');
	$this->throw_error(400, $errors);
      }

    $content = NULL;
    $format = $this->request_format();
    switch($format)
      {
      default:
      case self::FORMAT_XML:
	$this->response->headers('Content-Type', 'application/xml; charset=utf-8');
	$content = $this->template->content = View::factory('s1/rest/common/xml');
	break;
      case self::FORMAT_JSON:
	$this->response->headers('Content-Type', 'application/json; charset=utf-8');
	$content = $this->template->content = View::factory('s1/rest/common/json');
	break;
      }
    
    $this->response->status(201);
    $content->Data = $Item;
    $ItemName = isset($this->ItemName) ? $this->ItemName : $this->ItemType;
    $content->DataName = strtolower($ItemName);
  }

  protected function paginate_results(ORM $sql)
  {
    $Pagination = array(
			'total'  => (int) $sql->count_all(),
			'offset' => ($this->request->query('offset') === NULL) ? 0 : (int)$this->request->query('offset'),
			'limit'  => ($this->request->query('limit') === NULL) ? 10 : (int)$this->request->query('limit'),
			);

    $sql->limit($Pagination['limit'])->offset($Pagination['offset']);
    return $Pagination;
  }


  public function action_index()
  {
    $action_options_array = $this->index_actions();
    if( array_key_exists($this->request->method(), $action_options_array) )
      {
	$action_options = $action_options_array[$this->request->method()];
	foreach($action_options as $method => $params)
	  {
	    $method_args = isset($params['args']) ? $params['args'] : array();

	    if( isset($params['filter']) )
	      {
		foreach($params['filter'] as $key => $value)
		  {
		    if( $this->request->param($key) !== $value )
		      {
			continue 2; //This method doesn't pass filter, consider next one
		      }
		  }
	      }
	    return $this->{$method}($method_args);
	  }
      }
    else
      {
	$this->throw_error(400, 'Request not understood');
      }
  }
}