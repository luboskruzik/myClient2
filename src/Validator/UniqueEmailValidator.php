<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Service\MyClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Annotation
 */
class UniqueEmailValidator extends ConstraintValidator
{
    private MyClient $client;
    
    public function __construct(MyClient $client) 
    {
        $this->client = $client;
    }

    public function validate($value, Constraint $constraint)
    {
        if ($value === null || filter_var($value, FILTER_VALIDATE_EMAIL) === false) return;
        
        try {
            $content = $this->client->getUserByEmail($value);
            if (json_decode($content)->user) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $value)
                    ->addViolation();
            }
        } catch (ExceptionInterface $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
