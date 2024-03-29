<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Partenaires;
use App\Entity\CompBancaire;
use App\Entity\ComptBancaire;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api")
 */
class UserController extends AbstractController
{

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    //===================================================>Login<==============================£===============================================================================================//
    /**
     * @Route("/login", name="loginch", methods={"POST"})
     * @param Request $request
     * @param JWTEncoderInterface $JWTEncoder
     * @return JsonResponse
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
     */
    public function login(Request $request, JWTEncoderInterface $JWTEncoder)
    {
        $values = json_decode($request->getContent());
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['username' => $values->username]);

        if (!$user) {
            $da = [
                'stat' => 400,
                'mesg' => 'Nom d\'Utilisateur incorrect'
            ];
            return   new JsonResponse($da, 400);
        }
        $isValid = $this->passwordEncoder->isPasswordValid($user, $values->password);
        if (!$isValid) {
            $da = [
                'stat' => 400,
                'mesg' => 'Mot de passe incorrect'
            ];
            return   new JsonResponse($da, 400);
        }
        //===============================================================================

        $profil = $user->getRoles();

        if (!empty($user->getEtat()) && $profil != ['ROLE_CAISIER']) {
            $partenstat = $user->getPartenaire()->getEtat();


            if ($partenstat == 'Bloque') {
                $data = [
                    'stat' => 400,
                    'messge' => 'Accés refusé! votre prestataire a été bloqué.'
                ];
                return new JsonResponse($data, 400);
            } elseif ($partenstat == 'Actif' &&  $user->getEtat() == 'Bloque') {
                $data = [
                    'stat' => 400,
                    'mesge' => 'Votre accés est bloqué,veillez vous adressez à votre administrateur!'
                ];
                return new JsonResponse($data, 400);
            } elseif ($user->getEtat() == 'Bloque') {
                $dat = [
                    'stat' => 400,
                    'mesge' => 'Votre accés est bloqué,veillez vous adressez à votre administrateur!'
                ];
                return new JsonResponse($dat, 400);
            } else {
                $token = $JWTEncoder->encode([
                    'username' => $user->getUsername(),
                    'roles' => $user->getRoles(),
                    'exp' => time() + 3600 // 1 hour expiration
                ]);
                return new JsonResponse(['token' => $token]);
            }
        } elseif ($profil == ['ROLE_CAISIER']) {
            $statuser = $user->getEtat();
            if ($statuser == 'Bloque') {
                $data = [
                    'stat' => 400,
                    'messge' => 'Accés refusé! votre avez  été bloqué.'
                ];
                return new JsonResponse($data, 400);
            } else {
                $token = $JWTEncoder->encode([
                    'username' => $user->getUsername(),
                    'roles' => $user->getRoles(),
                    'exp' => time() + 3600 // 1 hour expiration
                ]);
                return new JsonResponse(['token' => $token]);
            }
        } else {
            $token = $JWTEncoder->encode([
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'exp' => time() + 3600 // 1 hour expiration
            ]);
            return new JsonResponse(['token' => $token]);
        }
    }
    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     * @IsGranted({"ROLE_ADMIN","ROLE_SUPER_ADMIN"},message="vous netes pas autoriser a ajouter des utilisateur")
     */
    public function addUser(Request $request, UserPasswordEncoderInterface $passwordEncoder, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $Values = $request->request->all();
        $form->submit($Values);

        $Files = $request->files->all()['imageName'];
        $profil = $Values['profil'];
        switch ($profil) {
            case 1:
                $user->setRoles(['ROLE_ADMIN']);
                $parte = $this->getUser()->getPartenaire();
                $user->setPartenaire($parte);
                break;
            case 2:
                $user->setRoles(['ROLE_CAISIER']);
                break;
            case 3:

                $user->setRoles(['ROLE_USER']);
                $parte = $this->getUser()->getPartenaire();
                $user->setPartenaire($parte);

                break;
            default:
                $data = [
                    'statuts' => 400,
                    'message' => 'Ce profil n\'existe pas,veillez réctifier votre profil!'
                ];
                return new JsonResponse($data, 400);
        }

        $user->setPassword($passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData()));
        $user->setEtat('Actif');


        $user->setImageFile($Files);
        $entityManager = $this->getDoctrine()->getManager();
        $errors = $validator->validate($user);
        if (count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Typle' => 'applicatlion/json'
            ]);
        }
        $entityManager->persist($user);
        $entityManager->flush();
        $data = [
            'statut' => 201,
            'massage' => 'L"utilisateur été bien ajouté'
        ];
        return new JsonResponse($data, 201);
    }
    //========================Modifier un utilisateur========================£===============================================================================================//
    /**
     * @Route("/utilisateur/{id}", name="utilisaUpdate", methods={"PUT","GET","POST"})
     * @IsGranted({"ROLE_SUPER_ADMIN","ROLE_ADMIN"},message="Acces Refusé!Veillez vous connecter en tant que super administrateur.")
     */
    public function updat(Request $request, SerializerInterface $serializer, User $user, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $utilisaUpdate = $entityManager->getRepository(User::class)->find($user->getId());
        $data = json_decode($request->getContent());
        foreach ($data as $key => $values) {
            if ($key && !empty($values)) {
                $compteBancaire = ucfirst($key);
                $setter = 'set' . $compteBancaire;
                $utilisaUpdate->$setter($values);
            }
        }
        $errors = $validator->validate($utilisaUpdate);
        if (count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'ContentType' => 'applications/json'
            ]);
        }
        $entityManager->flush();
        $data = [
            'statut' => 200,
            'message' => 'Le Compte  a été bien allouer'
        ];
        return new JsonResponse($data);
    }
    /**
     * @Route("/liscomp", name="jhj", methods={"PUT","GET"})
     */
    public function Compte(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $adpart = $this->getUser();
        $part = $adpart->getPartenaire();
        $repo = $this->getDoctrine()->getRepository(ComptBancaire::class);
        $trans = $repo->findBy(['partenaire'=>$part]);

        $dat = $serializer->serialize($trans, 'json', [
            'groups' => ['compt']

        ]

        );

        return new Response($dat, 201, [
            'Content-Type' => 'application/json',
        ]);    
    }

   
    /**
     * @Route("/listeUser/{id}", name="listeUserr", methods={"GET"})
     */
    public function listeuser(UserRepository $userRepo, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {

        $listUser = $entityManager->getRepository(User::class);
        $users = $listUser->findAll();
        // Serialize your object in Json
        $jsonObject = $serializer->serialize($users, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        // For instance, return a Response with encoded Json
        return new Response($jsonObject, 200, ['Content-Type' => 'application/json']);
    }
    /**
     * @Route("/user", name="userliste", methods={"GET"})
     */
    public function show(Request  $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $usr = $this->getUser()->getRoles();
        $part = $this->getUser()->getPartenaire();
        $user = [];
        $repo = $this->getDoctrine()->getRepository(User::class);
        $trans = $repo->findAll();
        if ($usr == ['ROLE_ADMIN']) {
            foreach ($trans as $value) {

                if ($value->getRoles() == ['ROLE_USER'] && $value->getPartenaire()==$part) {
                    $user[] = $value;
                }
            }
        } elseif ($usr == ['ROLE_SUPER_ADMIN']) {

            foreach ($trans as $value) {

                if ($value->getRoles() == ['ROLE_CAISIER']) {
                    $user[] = $value;
                }
            }
        }

        $dat = $serializer->serialize($user, 'json', [
            'groups' => ['users']

        ]);


        return new Response($dat, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
