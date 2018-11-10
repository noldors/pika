<?php

declare(strict_types=1);

namespace Support\Response;

class EmptyJsonResponse extends JsonResponse
{
    public function __construct()
    {
        parent::__construct(null, 200, null);
    }
}
