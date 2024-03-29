<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private '.service_locator..EwH3.j' shared service.

return $this->privates['.service_locator..EwH3.j'] = new \Symfony\Component\DependencyInjection\Argument\ServiceLocator($this->getService, [
    'passwordEncoder' => ['services', 'security.password_encoder', 'getSecurity_PasswordEncoderService.php', true],
    'serializer' => ['services', 'serializer', 'getSerializerService', false],
    'validator' => ['services', 'validator', 'getValidatorService', false],
], [
    'passwordEncoder' => '?',
    'serializer' => '?',
    'validator' => '?',
]);
