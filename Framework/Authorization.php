<?php

namespace Framework;

use Framework\Session;

class Authorization
{
  /**
   * Check of current logged in user owns a resource
   * 
   * @param int $resourseId
   * @return bool
   */
  public static function isOwner($resourseId)
  {
    $sessionUser = Session::get('user');

    if ($sessionUser !== null && isset($sessionUser['id'])) {
      $sessionUserId = (int) $sessionUser['id'];
      return $sessionUserId === $resourseId;
    }

    return false;
  }
}
