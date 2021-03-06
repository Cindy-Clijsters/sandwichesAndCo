<?php
$root = dirname(__FILE__, 2);

require_once($root . '/vendor/autoload.php');

use App\Business\{AdministratorService, FlashService, TwigService, ValidationService};

// Check if the admnistrator is logged in correctly
$administratorSvc = new AdministratorService();
$administrator    = $administratorSvc->getLoggedInAdministrator();

if ($administrator === null) {
    $administratorSvc->logOut();
}

// Check if the form is posted
$errors          = new stdClass();
$errors->isValid = true;

if ($_POST) {
    
    $oldPassword     = filter_input(INPUT_POST, 'old-password');
    $password        = filter_input(INPUT_POST, 'password');
    $confirmPassword = filter_input(INPUT_POST, 'confirm-password');
    
    // Validate the fields
    $validationSvc = new ValidationService();

    $oldPasswordErrors = $validationSvc->validateTextField($oldPassword, 50);
    
    if ($oldPasswordErrors !== '') {
        $errors->oldPassword = $oldPasswordErrors;
        $errors->isValid     = false;
    }
    
    $passwordErrors = $validationSvc->validatePasswordField($password, 50, 8);
    
    if ($passwordErrors !== '') {
        $errors->password = $passwordErrors;
        $errors->isValid  = false;
    }
    
    $confirmPasswordErrors = $validationSvc->validateConfirmPasswordField(
        $confirmPassword,
        $password,
        50
    );

    if ($confirmPasswordErrors !== '') {
        $errors->confirmPassword = $confirmPasswordErrors;
        $errors->isValid  = false;
    }
    
    if ($errors->isValid === true) {

        if (password_verify($oldPassword, $administrator->getPassword())) {
            
            // Hash the password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Update the password
            $administrator->setPassword($passwordHash);
            
            // Update the administrator
            $administratorSvc->update($administrator);
            
            // Set the success message
            $flashSvc = new FlashService();
            $flashSvc->setFlashMessage(
                "profile",
                "Je wachtwoord is met succes gewijzigd.",
                "success"
            );
            
            
            // Redirect to the overview
            header("location:profile.php");
            exit(0);
        }
        
    }
    
}

// Show the view
$twigSvc = new TwigService();

echo $twigSvc->generateView(
    $root . '/admin/presentation',
    'passwordEdit.php',
    [
        "menuItem"       => "profile",
        "companyName"    => $_SESSION['companyName'],
        "administrator"  => $administrator,
        "errors"         => $errors
    ]
);