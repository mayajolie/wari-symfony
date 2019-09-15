<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Depot;
use App\Form\UserType;
use App\Form\DepotType;
use App\Form\ComptBanType;
use App\Entity\Partenaires;
use App\Form\PartenaireType;
use App\Entity\ComptBancaire;
use App\Repository\UserRepository;
use App\Repository\PartenairesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use FOS\RestBundle\Controller\Annotations as Rest; // alias pour toutes les annotations
// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;


/**
 * @Route("/api")
 */
class AdminSystemController extends FOSRestController
{
  
     /**
     * @Route("/admin", name="super", methods={"POST","GET"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function admin(Request $request, SerializerInterface $serializer,UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager,ValidatorInterface $validator)
    {

     //=======================Ajout Partenaire==============================//  
         //generer un numero de compte bancaire
         $code="";
         $jour = date('d');
         $mois = date('m');
         $annee = date('Y');
         $heure = date('H');
         $minutes = date('i');
         $seconde = date('s');
         $code = ($annee.$mois.$jour.$heure.$minutes.$seconde);
         
     $partenaire =new Partenaires();  
    
     $form = $this->createForm(PartenaireType::class, $partenaire);
     $form->handleRequest($request);
     $Values =$request->request->all();
     $form->submit($Values);
     $partenaire->setEtat('actif');
     $entityManager->persist($partenaire);

        //======================= creer admin du partenaire====================//
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $Values =$request->request->all();
        $form->submit($Values);
        $Files=$request->files->all()['imageName'];
        $user->setImageFile($Files);

        $user->setPassword($passwordEncoder->encodePassword($user,$form->get('plainPassword')->getData()));
        $user->setRoles(["ROLE_ADMIN"]);
        $user->setEtat(0);
        $user->setCompteBancaire($code);
        $user->setPartenaire($partenaire);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
             

            //============================creer un compte bancaire==================//      

            $compb = new ComptBancaire();
           
            $compb->setNumCompt($code);
            $compb->setSolde("0");
            $compb->setPartenaire($partenaire);

            $errors = $validator->validate($user);
            if(count($errors)) {
                $errors = $serializer->serialize($errors, 'json');
                return new Response($errors, 500, [
                    'Content-Type' => 'application/json'
                ]);
            } 
            $entityManager->persist($compb); 
            $entityManager->flush();
                $data =[
                    'STATUS' => 201,
                    'MESSAGE' => 'Le partenaire a été créé son compte bancaire et son administrateur',
                ];
                return new JsonResponse($data, 201);
    }

     /**
      * @Route("/listPart/{id}", name="listePart", methods={"GET"})
      */
      public function listeuser(UserRepository $userRepo, SerializerInterface $serializer,EntityManagerInterface $entityManager){
        
        $listUser= $entityManager->getRepository(Partenaires::class);
        $users = $listUser->findAll();
        // Serialize your object in Json
        $jsonObject = $serializer->serialize($users, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return new Response($jsonObject, 200, ['Content-Type' => 'application/json']);

        }
    /**
     * @Route("/comptB", name="compt", methods={"POST"})
     */
    public function ajoutComptB(Request $request,EntityManagerInterface $entityManager)
    {
        $compb = new ComptBancaire();
     $form = $this->createForm(ComptBanType::class, $compb);
     $form->handleRequest($request);
     $Values =$request->request->all();
     $form->submit($Values);
           // generer un numero de compte bancaire
            $code="";
            $jour = date('d');
            $mois = date('m');
            $annee = date('Y');
            $heure = date('H');
            $minutes = date('i');
            $seconde = date('s');
            $code = ($annee . $mois . $jour . $heure . $minutes . $seconde);
            $compb->setNumCompt($code);
            $compb->setSolde("0");
            $ninea=$Values['ninea'];
            $repo = $this->getDoctrine()->getRepository(Partenaires::class);
            $partenaire = $repo->findOneBy(['ninea'=>$ninea]);
            $compb->setPartenaire($partenaire);

            
            $entityManager->persist($compb); 
            $entityManager->flush();
        $data = [
            'STATUT' => 201,
            'MESSAGE' => 'Le compte bancaire  a été bien créer ',
        ];

        return new JsonResponse($data, 201);
    

    }

    /**
     * @Route("/depot", name="depot", methods={"POST","GET"})
     * @IsGranted("ROLE_CAISIER")
     */
    public function Depotav(Request $request, EntityManagerInterface $entityManager,SerializerInterface $serializer)
    {
     $depot = new Depot();    
     $form = $this->createForm(DepotType::class, $depot);
     $form->handleRequest($request);
     $Values =$request->request->all();
     $form->submit($Values);
     $depot->setDateDepot(new \DateTime());
     $depot->setCassier($this->getUser());
     $montant=$Values['montant'];
     $depot->setMontant($montant);
     $numeroCompt=$Values['Numero'];
     $repo = $this->getDoctrine()->getRepository(ComptBancaire::class);
     $numcompt = $repo->findOneBy(['numCompt'=>$numeroCompt]);
     $Values['solde']=$numcompt->getSolde();
     $nom=$numcompt->getPartenaire();

     $depot->setNumeroCompt($numcompt);
    //incrementant du solde du partenaire du montant du depot
    $numcompt->setSolde($numcompt->getSolde() + $montant);
    if($montant<"75000") {
        $data = [
            'status' => 500,
            'message' =>"Le solde minimum autorisée est 75000",
        ];

        return new JsonResponse($data, 500);
    } else{
    
    //enregistrement au niveau du compte bancaire
    $entityManager->persist($numcompt);


    //enregistrement au niveau du depot
    $entityManager->persist($depot);
    $entityManager->flush();
    }
    $dat = $serializer->serialize($numcompt, 'json',[
        'groups'=>['depotpart']
    ]);
    return new Response($dat, 200, [
        'Content-Type' => 'application/json'
    ]);
    }
    /**
     * @Route("/finddepot", name="depotfor", methods={"POST"})
     * @IsGranted("ROLE_CAISIER")
     */
    public function infoPart(Request $request,PartenairesRepository $partRepository, SerializerInterface $serializer,EntityManagerInterface $entityManager )
    {
        $values = json_decode($request->getContent(),true);
        $liste= $this->getDoctrine()->getRepository(ComptBancaire::class)->findOneBy(['numCompt'=>$values['numeroCompt']]);
        if (!$values) {
            $values=$request->request->all();
        }
        if (!$liste) 
        {
            throw new HttpException (403,'Numero de compte incorrect');
        }
        $data = $serializer->serialize($liste, 'json',[
            'groups'=>['depotpart']
        ]);
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
       
    
    }
    /**
    * @Route("/bloqueDebloqueUser/{id}", name="bloqueDebloqueUser", methods={"POST","GET"})
    */
    public function bloqueDebloqueUser(Request $request,EntityManagerInterface $mng,$id)
    {
        $user=$this->getDoctrine()->getRepository(User::class)->findOneBy(['id'=>$id]);
        if($user->getEtat()=='Actif') {
            $user->setEtat('Bloque');
        }
        else {
            $user->setEtat('Actif');
        }
        $mng = $this->getDoctrine()->getManager();
        $mng->persist($user);
        $mng->flush();
        $data = [
            'status' => 200,
            'message' => 'L\'etat a bien été mis à jour'
        ];
        return new JsonResponse($data);
    }
    /**
    * @Route("/bloqueDebloquePart/{id}", name="bloqueDebloquePart", methods={"POST","GET"})
    */
    public function bloqueDebloquePart(Request $request,EntityManagerInterface $mng,$id)
    {
        $part=$this->getDoctrine()->getRepository(Partenaires::class)->findOneBy(['id'=>$id]);
        $users=$part->getUsers();
        foreach ($users as $key => $value) {
            $this->bloqueDebloqueUser($request,$mng,$value->getId());
        }
        $bloque='Bloque';
        if($part->getEtat()=='Actif'){
            $part->setEtat($bloque);
        }
        else {
            $part->setEtat('Actif');
        }
        $mng= $this->getDoctrine()->getManager();
        $mng->persist($part);
        $mng->flush();
        $data = [
            'status' => 200,
            'message' => ' L\'etat a bien été mis à jour'
        ];
        return new JsonResponse($data);
    }
}
