<?php

namespace Crm\UsersModule\Forms;

use Crm\UsersModule\Auth\UserManager;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;
use Nette\Localization\ITranslator;
use Tomaj\Form\Renderer\BootstrapRenderer;

class RequestPasswordFormFactory
{
    private $userManager;

    private $translator;

    /* callback function */
    public $onSuccess;

    public function __construct(UserManager $userManager, ITranslator $translator)
    {
        $this->userManager = $userManager;
        $this->translator = $translator;
    }

    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;

        $form->setRenderer(new BootstrapRenderer());
        $form->addProtection();
        $form->setTranslator($this->translator);

        $form->addText('email', 'users.frontend.request_password.email.label')
            ->setType('email')
            ->setAttribute('autofocus')
            ->setRequired('users.frontend.request_password.email.required')
            ->setAttribute('placeholder', 'users.frontend.request_password.email.placeholder')
            ->addRule(function (TextInput $input) {
                $userRow = $this->userManager->loadUserByEmail($input->getValue());
                if ($userRow) {
                    return (bool)$userRow->active;
                }
                return true;
            }, 'users.frontend.request_password.inactive_user');

        $form->addSubmit('send', 'users.frontend.request_password.submit');

        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    public function formSucceeded($form, $values)
    {
        $result = $this->userManager->requestResetPassword($values->email);

        if (!$result) {
            $form['email']->addError($this->translator->translate('users.frontend.request_password.invalid_email'));
        } else {
            $this->onSuccess->__invoke($values->email);
        }
    }
}
