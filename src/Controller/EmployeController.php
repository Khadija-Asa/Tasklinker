<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EmployeRepository;
use App\Form\EmployeType;
use App\Form\RegisterType;
use App\Entity\Employe;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class EmployeController extends AbstractController
{
    public function __construct(
        private EmployeRepository $employeRepository,
        private EntityManagerInterface $entityManager,
    )
    {

    }

    // welcome route
    #[Route('/bienvenue', name: 'app_bienvenue')]
    public function bienvenue(): Response
    {
        return $this->render('register/bienvenue.html.twig');
    }

    // register route
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $employe = new Employe();
        $employe
            ->setStatut('CDI')
            ->setDateArrivee(new \DateTime());

        $form = $this->createForm(RegisterType::class, $employe);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $employe->setPassword($hasher->hashPassword($employe, $employe->getPassword()));

            $this->entityManager->persist($employe);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_projets');
        }
        
        return $this->render('register/register.html.twig', [
            'form' => $form,
        ]);
    }

    // login route
    #[Route('/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $email = $authenticationUtils->getLastUsername();

        return $this->render('register/login.html.twig', [
            'email' => $email,
            'error' => $error,
        ]);
    }

    // logout route
    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }


    // employe's list route
    #[Route('/employes', name: 'app_employes')]
    public function employes(): Response
    {
        $employes = $this->employeRepository->findAll();
        
        return $this->render('employe/liste.html.twig', [
            'employes' => $employes,
        ]);
    }

    #[Route('/employes/{id}', name: 'app_employe')]
    public function employe($id): Response
    {
        $employe = $this->employeRepository->find($id);

        if(!$employe) {
            return $this->redirectToRoute('app_employes');
        }
        
        return $this->render('employe/employe.html.twig', [
            'employe' => $employe,
        ]);
    }

    // delete
    #[Route('/employes/{id}/supprimer', name: 'app_employe_delete')]
    public function supprimerEmploye($id): Response
    {
        $employe = $this->employeRepository->find($id);

        if(!$employe) {
            return $this->redirectToRoute('app_employes');
        }

        $this->entityManager->remove($employe);
        $this->entityManager->flush();
        
        return $this->redirectToRoute('app_employes');
    }

    // edit
    #[Route('/employes/{id}/editer', name: 'app_employe_edit')]
    public function editerEmploye($id, Request $request): Response
    {
        $employe = $this->employeRepository->find($id);

        if(!$employe) {
            return $this->redirectToRoute('app_employes');
        }

        $form = $this->createForm(EmployeType::class, $employe);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('app_employes');
        }

        return $this->render('employe/employe.html.twig', [
            'employe' => $employe,
            'form' => $form->createView(),
        ]);
    }


}