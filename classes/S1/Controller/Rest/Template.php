<?php defined('SYSPATH') or die('No direct script access.');

class S1_Controller_Rest_Template extends Controller
{
  public $template =  's1/rest/api';
  public $auto_render = TRUE;


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


  protected function check_access()
  {
    $user = NULL;
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
   
    if( $this->check_access() === FALSE )
      {
	$this->respond_error('User does not have required roles');
      }
    
    if ($this->auto_render)
      {
	// Initialize empty values
	$this->template = View::factory($this->template);
	$this->template->content = '';
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
}