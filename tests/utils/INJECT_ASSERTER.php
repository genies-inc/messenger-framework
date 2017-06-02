<?php

namespace {

  $testerMock;

}

namespace MessengerFramework {

  function tester() {

    global $testerMock;

    if (!isset($testerMock) || !\is_callable($testerMock)) {
      return;
    }

    return $testerMock(func_get_args());

  }

}
