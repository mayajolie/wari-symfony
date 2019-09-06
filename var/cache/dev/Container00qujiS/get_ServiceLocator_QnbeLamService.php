<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private '.service_locator.qnbeLam' shared service.

return $this->privates['.service_locator.qnbeLam'] = new \Symfony\Component\DependencyInjection\Argument\ServiceLocator($this->getService, [
    'entityManager' => ['services', 'doctrine.orm.default_entity_manager', 'getDoctrine_Orm_DefaultEntityManagerService', false],
    'partRepository' => ['privates', 'App\\Repository\\PartenairesRepository', 'getPartenairesRepositoryService.php', true],
    'serializer' => ['services', 'serializer', 'getSerializerService', false],
], [
    'entityManager' => '?',
    'partRepository' => 'App\\Repository\\PartenairesRepository',
    'serializer' => '?',
]);
