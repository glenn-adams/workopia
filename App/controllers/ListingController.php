<?php

namespace App\Controllers;

use Framework\Authorization;
use Framework\Database;
use Framework\Session;
use Framework\Validation;

class listingController
{
  protected $db;

  public function __construct()
  {
    $config = require basePath('config/db.php');
    $this->db = new Database($config);
  }

  /**
   * Show the lastest listings
   *
   * @return void
   */
  public function index()
  {
    // Get listings
    $listings = $this->db->query('SELECT * FROM listings ORDER BY created_at DESC')->fetchAll();

    // Load Listings/index view
    loadView('listings/index', [
      'listings' => $listings
    ]);
  }

  /**
   * Show the create listing view
   *
   * @return void
   */
  public function create()
  {
    // Load create view
    loadView('listings/create');
  }

  /**
   * Show the listing detailed view
   *
   * @param array $params
   * @return void
   */
  public function show($params)
  {
    $id = $params['id'] ?? '';

    $params = [
      'id' => $id
    ];

    // Using params for the query string is safer & prevents SQL injection
    $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

    if (!$listing) {
      ErrorController::notFound('Listing not found');
      return;
    }

    loadView(
      'listings/show',
      [
        'listing' => $listing
      ]
    );
  }

  /**
   * Store data in database
   * 
   * @return void
   */
  public function store()
  {
    $allowedFields = ['title', 'description', 'salary', 'tags', 'company', 'address', 'city', 'state', 'phone', 'email', 'requirements', 'benefits'];

    // Filter out in fields not in the $allowedFields
    $newListingData = array_intersect_key($_POST, array_flip($allowedFields));

    // Establish user id
    $newListingData['user_id'] = Session::get('user')['id'];

    // Sanitize field data for any troublesome control characters
    $newListingData = array_map('sanitize', $newListingData);

    // Make sure certain fields are filled out
    $requiredFields = ['title', 'description', 'salary', 'email', 'city', 'state'];

    // Set up error messaging if requireFields are empty
    $errors = [];

    foreach ($requiredFields as $field) {
      if (empty($newListingData[$field]) || !Validation::string($newListingData[$field])) {
        $errors[$field] = ucfirst($field) . ' is required';
      };
    }

    // If there are errors, then reload view with errors
    if (!empty($errors)) {
      loadView('listings/create', ['errors' => $errors, 'listing' => $newListingData]);
    } else {
      // Submit data to db
      echo '<h4>Success!</h4>';

      // Prepare fields and values for database
      foreach ($newListingData as $field => $value) {
        $fields[] = $field;
      }
      // Prepare fields
      $fields = implode(', ', $fields);
      // Prepare the placeholders (form of :fieldname) for matching values
      $values = [];

      foreach ($newListingData as $field => $value) {
        // Convert empty strings to nulls
        if ($value === '') {
          $newListingData[$field] = null;
        }
        // Create the placeholders
        $values[] = ':' . $field;
      }

      $values = implode(', ', $values);

      // Prepare query
      $query = "INSERT INTO listings ({$fields}) VALUES ({$values})";
      $this->db->query($query, $newListingData);

      Session::setFlashMessage('success_message', 'Listing Created Successfully');

      // Redirect page
      redirect('/listings');
    }
  }

  /**
   * Delete a listing
   * 
   * @param array $params
   * @return void
   */
  public function destroy($params)
  {
    $id = $params['id'];

    $params = [
      'id' => $id
    ];

    // Check if listing exists via a query, if not issue error and return
    $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

    if (!$listing) {
      ErrorController::notFound('Listing Not Found');
      return;
    }

    // Verify if user is authorized to delete listing
    if (!Authorization::isOwner($listing->user_id)) {
      Session::setFlashMessage('error_message', 'You are not authorized to delete this listing');
      return redirect('/listings/' . $listing->id);
    }

    // Delete the listing and redirect
    $this->db->query('DELETE FROM listings WHERE id = :id', $params);

    // Set flash message
    Session::setFlashMessage('success_message', 'Listing deleted successfuly');
    redirect('/listings');
  }

  /**
   * Show the listing edit form
   *
   * @param array $params
   * @return void
   */
  public function edit($params)
  {
    $id = $params['id'] ?? '';

    $params = [
      'id' => $id
    ];

    // Using params for the query string is safer & prevents SQL injection
    $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();


    if (!$listing) {
      ErrorController::notFound('Listing not found');
      return;
    }

    // Verify if user is authorized to edit listing
    if (!Authorization::isOwner($listing->user_id)) {
      Session::setFlashMessage('error_message', 'You are not authorized to edit this listing');
      return redirect('/listings/' . $listing->id);
    }

    loadView(
      'listings/edit',
      [
        'listing' => $listing
      ]
    );
  }

  /**
   * Update a listing
   * 
   * @param array $params
   * @return void
   */
  public function update($params)
  {
    $id = $params['id'] ?? '';

    $params = [
      'id' => $id
    ];

    // Using params for the query string is safer & prevents SQL injection
    $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

    // Check if listing exists
    if (!$listing) {
      ErrorController::notFound('Listing not found');
      return;
    }

    // Verify if user is authorized to update listing
    if (!Authorization::isOwner($listing->user_id)) {
      Session::setFlashMessage('error_message', 'You are not authorized to update this listing');
      return redirect('/listings/' . $listing->id);
    }

    $allowedFields = ['title', 'description', 'salary', 'tags', 'company', 'address', 'city', 'state', 'phone', 'email', 'requirements', 'benefits'];

    $updateValues = [];

    $updateValues = array_intersect_key($_POST, array_flip($allowedFields));

    $updateValues = array_map('sanitize', $updateValues);

    $requiredFields = ['title', 'description', 'salary', 'email', 'city', 'state'];

    $errors = [];

    foreach ($requiredFields as $field) {
      if (empty($updateValues[$field]) || !Validation::string($updateValues[$field])) {
        $errors[$field] = ucfirst($field) . ' is required';
      }
    }

    if (!empty($errors)) {
      loadView('listings/edit', [
        'listing' => $listing,
        'errors' => $errors
      ]);
      exit;
    } else {
      // Submit to database
      $updateValues['id'] = $id;

      foreach (array_keys($updateValues) as $field) {
        $updateFields[] = "{$field} = :{$field}";
      }

      $updateFields = implode(', ', $updateFields);

      $updateQuery = "UPDATE listings SET $updateFields WHERE id = :id";

      $this->db->query($updateQuery, $updateValues);

      Session::setFlashMessage('success_message', 'Listing Updated');

      redirect('/listings/' . $id);
    }
  }

  /**
   * Search listings by <keywords>
   * 
   * @return void
   */
  public function search()
  {
    // Extract search variables
    $keywords = sanitize(isset($_GET['keywords']) ? trim($_GET['keywords']) : '');
    $location = sanitize(isset($_GET['location']) ? trim($_GET['location']) : '');

    // Construct search query
    $query = "SELECT * FROM listings WHERE (title LIKE :keywords OR description LIKE :keywords OR tags LIKE :keywords or company LIKE :keywords) AND (city LIKE :location OR state LIKE :location)";
    $params = [
      'keywords' => "%{$keywords}%",
      'location' => "%{$location}%"
    ];

    $listings = $this->db->query($query, $params)->fetchAll();

    // Display search results
    loadView('listings/index', [
      'listings' => $listings,
      'keywords' => $keywords,
      'location' => $location
    ]);
  }
}
