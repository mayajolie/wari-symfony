<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;



 class UserFixtures extends Fixture
{
    private $encoder;

public function __construct(UserPasswordEncoderInterface $encoder)
{
    $this->encoder = $encoder;
}

    public function load(ObjectManager $manager)
    {

        $user = new User();
        $user->setUsername('mayajolie');
        $password = $this->encoder->encodePassword($user, 'mayajolie');
        $user->setPassword($password);
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setNom('ngom');
        $user->setPrenom('mariama');
        $user->setAdresse('parcelle');
        $user->setTelephone(772918604);
        $user->setEmail('maya@gmail.com');
        $user->setEtat('');
        $user->setImageName('1.png');
        $user->setUpdatedAt(new \DateTime());
        
        
        $manager->persist($user);
        $manager->flush();

        $caisier = new User();
        $caisier->setUsername('bsokhna');
        $password = $this->encoder->encodePassword($user, 'bsokhna');
        $caisier->setPassword($password);
        $caisier->setRoles(['ROLE_CAISSIER']);
        $caisier->setNom('ngom');
        $caisier->setPrenom('sokhna');
        $caisier->setAdresse('parcelle');
        $caisier->setTelephone(771479013);
        $caisier->setEmail('bsona@gmail.com');
        $caisier->setEtat('');
        $caisier->setImageName('1.png');
        $caisier->setUpdatedAt(new \DateTime());
        
        $manager->persist($caisier);
        $manager->flush();
    }
}
