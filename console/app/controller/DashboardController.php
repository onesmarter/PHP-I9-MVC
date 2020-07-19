<?php

namespace SFW\Controller;
use SFW\Connection;
use SFW\Request;
use RainTPL;


class DashboardController {
    public function dashboard(Request $request,Connection $connection,RainTPL $tpl) {
        return $tpl->draw('after-login/dashboard', $return_string = true);
    }
}