<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;
use Framework\Session;

class UserController
{
  protected $db;

  public function __construct()
  {
    $config = require basePath('config/db.php');
    $this->db = new Database($config);
  }

  /**
   * Show the login page
   * 
   * @return void
   */
  public function login()
  {
    loadView('users/login');
  }

  /**
   * Authenticate a user with email and password
   * 
   * @return void
   */
  public function authenticate()
  {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $error = [];

    // validation
    if (!Validation::email($email)) {
      $errors['email'] = 'Please enter a valid email';
    }

    if (!Validation::string($password, 6)) {
      $errors['password'] = 'Password must be at least 6 characters';
    }

    // Check for errors and display to user
    if (!empty($errors)) {
      loadView('users/login', [
        'errors' => $errors
      ]);
      exit;
    }

    // Check if email exists
    $params = [
      'email' => $email
    ];

    $user = $this->db->query('SELECT * FROM users WHERE email = :email', $params)->fetch();

    if (!$user) {
      $errors['email'] = 'Invalid email or password';
      loadView('users/login', [
        'errors' => $errors
      ]);
      exit;
    }

    // Check if password is correct
    if (!password_verify($password, $user->password)) {
      $errors['email'] = 'Invalid email or password';
      loadView('users/login', [
        'errors' => $errors
      ]);
      exit;
    }

    // Set the user session
    Session::set('user', [
      'id' => $user->id,
      'name' => $user->name,
      'email' => $user->email,
      'city' => $user->city,
      'state' => $user->state
    ]);

    redirect('/');
  }


  /**
   * Logout the user and kill session
   * 
   * @return void
   */
  public function logout()
  {
    // Clear session data
    Session::clearAll();

    // Clear session cookie
    $params = session_get_cookie_params();
    setcookie('PHPSESSID', '', time() - 86400, $params['path'], $params['domain']);

    redirect('/');
  }


  /**
   * Show the register page
   * 
   * @return void
   */
  public function create()
  {
    loadView('users/create');
  }


  /**
   * Store user in database
   * 
   * @return void
   */
  public function store()
  {
    // Extract data from $_POST superglobal
    $name = $_POST['name'];
    $email = $_POST['email'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $password = $_POST['password'];
    $passwordConfirmation = $_POST['password_confirmation'];

    // Validation
    $errors = [];

    if (!Validation::email($email)) {
      $errors['email'] = 'Please enter a valid email address';
    }

    if (!Validation::string($name, 2, 30)) {
      $errors['name'] = 'Name must be at least 2 characters and not more than 30';
    }

    if (!Validation::string($password, 6, 30)) {
      $errors['password'] = 'Password must be at least 6 characters and not more than 50';
    }

    if (!Validation::match($password, $passwordConfirmation)) {
      $errors['passwordConfirm'] = 'Passwords must match';
    }

    if (!empty($errors)) {
      loadView('/users/create', [
        'errors' => $errors,
        'user' => [
          'name' => $name,
          'email' => $email,
          'city' => $city,
          'state' => $state
        ]
      ]);
      exit;
    }

    // Check if email already exists
    $params = [
      'email' => $email
    ];

    $user = $this->db->query('SELECT * FROM users WHERE email = :email', $params)->fetch();

    if ($user) {
      $errors['email'] = 'That email already exists';
      loadView('users/create', [
        'errors' => $errors
      ]);
      exit;
    }

    // Create user account
    $params = [
      'name' => $name,
      'email' => $email,
      'city' => $city,
      'state' => $state,
      'password' => password_hash($password, PASSWORD_DEFAULT)
    ];

    $this->db->query('INSERT INTO users (name, email, city, state, password) VALUES (:name, :email, :city, :state, :password)', $params);

    // Get new user ID
    $userID = $this->db->conn->lastInsertId();

    Session::set('user', [
      'id' => $userID,
      'name' => $name,
      'email' => $email,
      'city' => $city,
      'state' => $state
    ]);

    redirect('/');
  }
}
