<?php
namespace auth;

/**
 * @return string
 */
function get_clinet_inet_address()
{
    return $_SERVER['REMOTE_ADDR'];
}

