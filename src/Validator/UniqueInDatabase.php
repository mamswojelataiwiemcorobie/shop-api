<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueInDatabase extends Constraint
{
    public string $message = 'The string "{{ string }}" is not unique.';
    // If the constraint has configuration options, define them as public properties
    public string $mode = 'strict';
}