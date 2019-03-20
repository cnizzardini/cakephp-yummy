<?php

namespace Yummy\Exception\YummySearch;

use Cake\Core\Exception\Exception;

class QueryException extends Exception
{
    // You can set a default exception code as well.
    protected $_defaultCode = 500;
}