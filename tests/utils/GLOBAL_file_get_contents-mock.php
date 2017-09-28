<?php

namespace {

    $file_get_contents_rtv;
}

namespace Genies\MessengerFramework {

    function file_get_contents()
    {

        global $file_get_contents_rtv;

        if (!isset($file_get_contents_rtv) || $file_get_contents_rtv === null) {
            return call_user_func_array('\file_get_contents', func_get_args());
        }

        return $file_get_contents_rtv;
    }

}
