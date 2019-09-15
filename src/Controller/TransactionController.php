<?php

namespace App\Controller;

use App\Entity\Tarifs;
use App\Entity\User;
use App\Entity\Commission;
use App\Entity\Transaction;
use App\Entity\ComptBancaire;
use App\Entity\Retrait;
use App\Form\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;


/**
 * @Route("/api")
 */
class TransactionController extends AbstractController
{
    /**
     * @Route("/tra", name="transaction_index", methods={"GET"})
     */
    public function index(TransactionRepository $transactionRepository): Response
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('transaction/index.html.twig', [
            'transactions' => $transactionRepository->findAll(),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("contratprestataire.pdf", [
            "Attachment" => false
        ]);
        // Send some text response
        return $this->render('admin_part/index.html.twig', [
            'controller_name' => 'AdminPartController',
        ]);
        //return new Response("Le fichier PDF a été bien générer !");

    }

    /**
     * @Route("/trans", name="transaction_new", methods={"GET","POST"})
     */
    public function new(Request  $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $commi = new Commission();
        $transaction = new Transaction();
        $retrait=new Retrait();
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);
        $Values = $request->request->all();
        $form->submit($Values);

         //recupere le numero du compte bancaire
         $part = $this->getUser()->getCompteBancaire();
         //on recupere le montant qui est dans ce compte
         $repo = $this->getDoctrine()->getRepository(ComptBancaire::class);
         $numcompt = $repo->findOneBy(['numCompt' => $part]);
         $val = $numcompt->getSolde();
         //on recupere lensemble des tarifs
         $repo = $this->getDoctrine()->getRepository(Tarifs::class);
         $tar = $repo->findAll();
         //recuper  l'utilisateur qui fait le transaction
            $user = $this->getUser();

            $type = $Values['type'];
            
          
      
            if ($type == '1') {
                $montant = $Values['montant'];
                if ($montant < $val) {
                    foreach ($tar as $value) {
                        $min = $value->getBorneInferieur();
                        $max = $value->getBorneSuperieur();
                        if (($min <= $montant) && ($montant >= $max)) {
                            $com = $value->getValeur();
                        }
                    }
                $etat = (($com * 30) / 100);
                $envoye = (($com * 10) / 100);
                $comretrait = (($com * 20) / 100);
                $system = (($com * 40) / 100);
    
             $code = date('d') . date('m') . date('y') . date('s') . date('m');
            $transaction->setDateTrans(new \DateTime());
             $transaction->setUser($user);   
            //on compare le montant qu'on veut recuperer et le montant qui existe dans le compte
         

                $transaction->setMontant($montant);
                $transaction->setCodeTrans($code);
                $numcompt->setSolde($numcompt->getSolde() - $montant);
                $numcompt->setSolde($numcompt->getSolde() + $envoye);
                $transaction->setEnvoi('Envoye');

                $commi->setPartenaire($system);
                $commi->setEtat($etat);
                $commi->setEnvoi($envoye);
                $commi->setRetrait($comretrait);
                $commi->setDate(new \DateTime());
                $entityManager->persist($commi);

                $entityManager->persist($transaction);

            }
                
         else {

            $data = [
                'STATUS' =>400,
                'MESSAGE' => 'votre sole ne vous permet pas deffectuer cette transaction',
            ];
            return new JsonResponse($data, 400);
        }

    }
            if ($type == '2') {
                $coderetrait = $Values['code'];
                $cniB = $Values['cniB'];
                $repo = $this->getDoctrine()->getRepository(Transaction::class);
                $trans = $repo->findOneBy(['codeTrans'=> $coderetrait]);
                $coderetrait=$trans->getCodeTrans();
                $montantreti=$trans->getMontant();

                if($trans->getRetrait()=='Retrait'){
                    $data = [
                        'STATU' => 400,
                        'MESSAG' => 'Desoler ce code est deja utiliser ',
                    ];
                    return new JsonResponse($data, 400);
                }
                else{
                    foreach ($tar as $value) {
                        $min = $value->getBorneInferieur();
                        $max = $value->getBorneSuperieur();
                        if (($min <= $montantreti) && ($montantreti >= $max)) {
                            $com = $value->getValeur();
                        }
                    }
                $comretrait = (($com * 20) / 100);
    
                    $retrait->setTransaction($trans);
                    $retrait->setCode($coderetrait);     
                    $retrait->setDateRetrait(new \DateTime());
                    $retrait->setUser($user);
                    $entityManager->persist($retrait);
                     //incrementant du solde du partenaire du montant du depot
                $numcompt->setSolde($numcompt->getSolde() + $montantreti);
                $numcompt->setSolde($numcompt->getSolde() + $comretrait);
                $trans->setRetrait('Retrait');
                $trans->setCniB($cniB);
                }

               $errors = $validator->validate($user);
        if (count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }
    } 
        $entityManager->flush();



        $data = [
            'STATUS' => 201,
            'MESSAGE' => 'La transaction a ete bien effectuer',
        ];
        return new JsonResponse($data, 201); 
}

    /**
     * @Route("/listtrans", name="transaction_show", methods={"GET"})
     */
    public function show(Request  $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $user=$this->getUser()->getId();
        $repo = $this->getDoctrine()->getRepository(Transaction::class);
        $trans = $repo->findAll();
        // foreach ($trans as  $value) {
        // dump($value)        }
        $re = $this->getDoctrine()->getRepository(Retrait::class);
        $ret = $re->findAll();
       // dump($ret); die();
        $totaltras=[];
        if (!empty($trans)) {
            foreach ($trans as  $value) {
                     if($value->getUser()->getId()==$user){
                    $totaltras[]=$value;
                     } 

                        
            }
            $dat = $serializer->serialize($totaltras ,'json',[
                'groups'=>['trans']
            ]); 
        }
        if (!empty($ret)) {
            foreach ($ret as  $value) {
                     if($value->getUser()->getId()==$user){
                    $totaltras[]=$value;
                     } 

                        
            }
            $dat = $serializer->serialize($totaltras ,'json',[
                'groups'=>['trans']
            ]); 
        }
  
    return new Response($dat, 200, [
        'Content-Type' => 'application/json'
    ]);

     
    }

    /**
     * @Route("/{id}/edit", name="transaction_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Transaction $transaction): Response
    {
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="transaction_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Transaction $transaction): Response
    {
        if ($this->isCsrfTokenValid('delete' . $transaction->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($transaction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('transaction_index');
    }
}
