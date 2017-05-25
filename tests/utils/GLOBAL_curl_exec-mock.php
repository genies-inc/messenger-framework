<?php

namespace {
  $curl_exec_rtv;
}

namespace Framework {

  function curl_exec() {

    global $curl_exec_rtv;

    if (!isset($curl_exec_rtv) || is_null($curl_exec_rtv)) {
      return call_user_func_array('\curl_exec', func_get_args());
    }

    return $curl_exec_rtv;

  }

}
