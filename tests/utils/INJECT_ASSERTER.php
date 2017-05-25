<?php

namespace {

  $testerMock;

}

namespace Framework {

  function tester() {

    global $testerMock;

    if (!isset($testerMock) || !\is_callable($testerMock)) {
      return;
    }

    return $testerMock(func_get_args());

  }

}
