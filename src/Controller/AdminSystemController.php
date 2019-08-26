<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Depot;
use App\Form\UserType;
use App\Form\ComptBanType;
use App\Entity\Partenaires;
use App\Form\PartenaireType;
use App\Entity\ComptBancaire;
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
        $user->setEtat('actif');
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
     * @Route("/partenaires", name="find_partenaires",methods={"GET"})
      */
    public function liste()
    {
        $repo = $this->getDoctrine()->getRepository(Partenaires::class);
        $partenaire = $repo->findAll();
        var_dump($partenaire);
        return $this->handleView($this->view($partenaire));
    }
   

  

    /**
     * @Route("/ajout/{id}", name="bloqr", methods={"PUT"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function bloquerPartenaie(Request $request, SerializerInterface $serializer, Partenaires $partenaire, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $bloqueP = $entityManager->getRepository(Partenaires::class)->find($partenaire->getId());
    
        $data = json_decode($request->getContent());
        foreach ($data as $key => $value) {
            if ($key && !empty($value)) {
                $name = ucfirst($key);
                $setter = 'set'.$name;
                $bloqueP->$setter($value);
            }
        }
        $errors = $validator->validate($bloqueP);
        if (count($errors)) {
            $errors = $serializer->serialize($errors, 'json');

            return new Response($errors, 500, [
                'Content-Type' => 'application/json',
            ]);
        }
        $entityManager->flush();
        $data = [
            'statu' => 200,
            'messag' => 'L \'etat du partenaire a bien été mis à jour',
        ];

        return new JsonResponse($data);
    }
    /**
     * @Route("/comptB", name="compt", methods={"POST"})
     */
    public function ajoutComptB(Request $request,EntityManagerInterface $entityManager)
    {
        $values = json_decode($request->getContent());
        $compb = new ComptBancaire();
            //generer un numero de compte bancaire
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
            $repo = $this->getDoctrine()->getRepository(Partenaires::class);
            $partenaire = $repo->find($values->partenaire);
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
     * @Route("/depot", name="depot", methods={"POST"})
     * @IsGranted("ROLE_CAISSIER")
     */
    public function Depot(Request $request, EntityManagerInterface $entityManager)
    {
        $values = json_decode($request->getContent());
        if (isset($values->montant)) {
            $depot = new Depot();
            $depot->setMontant($values->montant);
            $depot->setDateDepot(new \DateTime());
            //recuperation de l'id du caissier
            $repo = $this->getDoctrine()->getRepository(User::class);
            $caissier = $repo->find($values->caissier); 
            $depot->setCassier($caissier);
             //recuperation du numero de compte
             $repo = $this->getDoctrine()->getRepository(ComptBancaire::class);
             $numcompt = $repo->findOneBy(['numCompt'=>$values->numeroCompt]);
             $depot->setNumeroCompt($numcompt);
            //incrementant du solde du partenaire du montant du depot
            $numcompt->setSolde($numcompt->getSolde() + $values->montant);

            if($values->montant<"75000") {
                $data = [
                    'status' => 500,
                    'message' =>"Le solde minimum autorisée est 75000",
                ];
        
                return new JsonResponse($data, 500);
            }
            else{
            //enregistrement au niveau du compte bancaire
            $entityManager->persist($numcompt);


            //enregistrement au niveau du depot
            $entityManager->persist($depot);
            $entityManager->flush();

            $data = [
                'status_1' => 201,
                'message' => 'Le depot  a été enregistré',
            ];

            return new JsonResponse($data, 201);
        }
        }
       

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_CREATED));
    }
}
