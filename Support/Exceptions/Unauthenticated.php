<?php

declare(strict_types=1);

namespace Support\Exceptions;

class Unauthenticated extends ResponsableException
{
    public function __construct()
    {
        parent::__construct('Missing access_token!', 401);
    }
}
