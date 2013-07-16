<?php defined('SYSPATH') or die('No direct script access.');

class S1_Controller_Rest_Template extends Controller
{
  const FORMAT_XML = "XML";
  const FORMAT_CSV = "CSV";
  const FORMAT_JSON = "JSON";
  const FORMAT_DEBUG = "DEBUG";


  public $template =  's1/rest/api';
  public $auto_render = TRUE;
  
  protected $user = NULL;


  /*
   * FALSE: don't check role
   * 'role': ensure user has access to 'role'
   * array('role_a', 'role_b'): ensure User has access to both 'role_a' and 'role_b'
   * array('GET' => 'role_a', 'POST' => 'role_b'): ensure User has role_a when 
   *         HTTP method is GET and role_b when HTTP method is POST
   * array('GET' => array('role_a', 'role_b'), 'PUT' => array('role_b', 'role_c'))
   */
  public $auth_required = FALSE;   /* Access control for the whole controller */
  public $secure_actions = FALSE;  /* Access control for individual actions */

   
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
  
  protected function user_has_access($access_required, Model_User $user)
  {
    if( $access_required === FALSE )
      {
	return TRUE;
      }

    if( is_array($access_required) === FALSE )
      {
	$role = ORM::factory('Role', array('name' => $access_required));
	if( $role->loaded() === FALSE )
	  {
	    return FALSE;
	  }
	return $user->has('roles', $role);
      }

    if( $this->is_assoc($access_required) === FALSE )
      {
	$roles = ORM::factory('Role')
	  ->where('name', 'IN', $access_required)
	  ->find_all()
	  ->as_array(NULL, 'id');

	if( count($roles) !== count($access_required) )
	  {
	    return FALSE;
	  }
	
	return $user->has('roles', $roles);
      }
    else
      {
	if( array_key_exists($this->request->method(), $access_required) === TRUE )
	  {
	    $access_required = $access_required[$this->request->method()];
	    return $this->user_has_access($access_required, $user);
	  }
	else
	  {
	    return FALSE;
	  }
      }
  }

  protected function check_access($user = NULL)
  {
    if( $user === NULL )
      {
	if( $this->user === NULL )
	  {
	    if( Auth::instance()->logged_in() === TRUE )
	      {
		$this->user = $user = Auth::instance()->get_user();
	      }
	    else
	      {
		$this->user = $user = ORM::factory('User');
	      }
	  }
	else
	  {
	    $user = $this->user;
	  }
      }

    $action_name = $this->request->action();
    if( $this->user_has_access($this->auth_required, $user) === TRUE && 
	(is_array($this->secure_actions) === FALSE || array_key_exists($action_name, $this->secure_actions) === FALSE || $this->user_has_access( $this->secure_actions[$action_name], $user) === TRUE)
	)
      {
	return TRUE;
      }
    return FALSE;
  }
 
  public function before()
  {
    parent::before();
   
    
    if ($this->auto_render)
      {
	// Initialize empty values
	$this->template = View::factory($this->template);
	$this->template->content = '';
      }

    if( $this->check_access() === FALSE )
      {
	$this->throw_error(401, 'User does not have required roles');
      }
  }
  
  public function after()
  {
    if ($this->auto_render)
      {
	$this->response->body($this->template->render());
      }
    parent::after();
  }

  protected function is_assoc(array $arr)
  {
    return (bool)count(array_filter(array_keys($arr), 'is_string'));
  }
}