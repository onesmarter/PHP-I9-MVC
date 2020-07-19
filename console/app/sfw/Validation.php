<?php
namespace SFW;
class Validation {
    public static function isEmailValid($email) : bool {
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } 
        return false;
    }
}