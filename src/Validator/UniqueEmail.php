<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class UniqueEmail extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public string $message = 'The email "{{ value }}" is already registered';
}
