<?php

namespace Apex\Container\Attributes;

use \Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Inject
{

    public function __construct(
        public string $var_name
    ) 
    { 

    }

}


