<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'login' => [
        'title' => 'Log in to your account',
        'description' => 'Enter your email and password below to log in.',
        'forgot' => 'Forgot your password?',
        'remember_me' => 'Remember me',
    ],
    'verify_email' => [
        'description' => 'Please verify your email address by clicking on the link we just emailed to you.',
        'link_sent' => 'A new verification link has been sent to the email address you provided during registration.',
        'resend' => 'Resend verification email',
    ],
    'reset_password' => [
        'title' => 'Reset password',
        'description' => 'Please enter your new password below.',
        'submit' => 'Reset password',
    ],
    'confirm_password' => [
        'title' => 'Confirm password',
        'description' => 'This is a secure area of the application. Please confirm your password before continuing.',
    ],
    'forgot_password' => [
        'title' => 'Forgot password',
        'description' => 'Enter your email address to receive a password reset link.',
        'confirmation' => 'A reset link will be sent if the account exists.',
        'return_to' => 'Or, return to',
        'submit' => 'Send reset link',
    ],
];
