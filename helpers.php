<?php

// Helper functions and code that can be
// used around the site

/**
 * Get the base path
 * 
 * @param string $path
 * @return string
 */

function basePath($path = '')
{
  return __DIR__ . '/' . $path;
}

/**
 * Load a view
 * 
 * @param string $name
 * @return void
 * 
 */
function loadView($name, $data = [])
{
  $viewPath = basePath("App/views/{$name}.view.php");

  // inspect($name);
  // inspectAndDie($viewPath);

  // Check if file exist, otherwise error message
  if (file_exists($viewPath)) {
    // extract() allows any passed in data available to the view page
    extract($data);
    require $viewPath;
  } else {
    echo "View {$name} not found!";
  }
}

/**
 * Load a partial
 * 
 * @param string $name
 * @return void
 * 
 */
function loadPartial($name, $data = [])
{
  $partialsPath = basePath("App/views/partials/{$name}.php");
  // Check if file exist, otherwise error message
  if (file_exists($partialsPath)) {
    extract($data);
    require $partialsPath;
  } else {
    echo "Partial {$name} not found!";
  }
}

/**
 * Inspect a value(s), and print to screen
 * useful for debugging
 * 
 * @param mixed $value
 * @return void
 */
function inspect($value)
{
  echo '<pre>';
  var_dump($value);
  echo '</pre>';
}

/**
 * Inspect a value(s), and print to screen
 * then die (stops script)
 * Can only use once...
 * 
 * @param mixed $value
 * @return void
 */
function inspectAndDie($value)
{
  echo '<pre>';
  die(var_dump($value));
  echo '</pre>';
}

/**
 * Format Salary
 * 
 * @param string $salary
 * @return string Formatted Salary
 */
function formatSalary($salary)
{
  return '$' . number_format(floatval($salary));
}

/**
 * Sanitize data
 * 
 * @param string $dirty
 * @return string
 */
function sanitize($dirty)
{
  return filter_var($dirty, FILTER_SANITIZE_SPECIAL_CHARS);
}

/**
 * Redirect to a given uel
 * 
 * @param string $url
 * @return void
 */
function redirect($url)
{
  header("Location: {$url}");
  exit;
}
