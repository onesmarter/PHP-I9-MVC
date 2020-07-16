<?php

namespace SFW\Controller;
use SFW\Connection;
use SFW\Request;


class ExampleController {
    public function example(Request $request,Connection $connection,$tpl) {
        return $tpl->draw('sample', $return_string = true);
    }
    public function apiExample(Request $request,Connection $connection,$tpl) {
        return "dddddddddd";
    }
}