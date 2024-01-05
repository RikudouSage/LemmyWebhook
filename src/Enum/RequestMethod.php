<?php

namespace App\Enum;

use Symfony\Component\HttpFoundation\Request;

enum RequestMethod: string
{
    case Get = Request::METHOD_GET;
    case Post = Request::METHOD_POST;
    case Delete = Request::METHOD_DELETE;
    case Patch = Request::METHOD_PATCH;
    case Put = Request::METHOD_PUT;
}
