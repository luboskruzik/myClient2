<?php

namespace App\Tests\Form;

use App\Form\RegisterUserType;
use App\Entity\RegisterUser;
use Symfony\Component\Form\Test\TypeTestCase;

class RegisterUserTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'email' => 'john@doe.com'
        ];
        $model = new RegisterUser();
        $form = $this->factory->create(RegisterUserType::class, $model);
        
        $expected = new RegisterUser();
        $expected->setEmail('john@doe.com');
        
        
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);

    }
    
    public function testCustomFormView()
    {
        $formData = new RegisterUser();
        $formData->setEmail('john@doe.com');
        $view = $this->factory->create(RegisterUserType::class, $formData)
            ->createView();
        $model = $view->vars['value'];
        $this->assertEquals('john@doe.com', $model->getEmail());
    }
}