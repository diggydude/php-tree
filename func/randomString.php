<?php

  function randomString($len)
  {
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $str   = "";
    while (strlen($str) < $len) {
      $idx  = mt_rand(0, strlen($chars) - 1);
      $str .= $chars{$idx};
    }
    return $str;
  } // randomString

?>