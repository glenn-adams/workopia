<?php

namespace App\Controllers;

use Framework\Database;

class HomeController
{
  protected $db;

  public function __construct()
  {
    $config = require basePath('config/db.php');
    $this->db = new Database($config);
  }

  /**
   * Show the home view with limited listings
   *
   * @return void
   */
  public function index()
  {
    // Get listings
    $listings = $this->db->query('SELECT * FROM listings ORDER BY created_at DESC LIMIT 6')->fetchAll();

    // Load home view
    loadView('home', [
      'listings' => $listings
    ]);
  }
}
