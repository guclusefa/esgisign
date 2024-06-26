<?php

namespace App\Controller\Frontoffice;

use App\Constants\RouteConstants;
use App\Constants\ToastConstants;
use App\Entity\User;
use App\Form\User\ProfileEmailFormType;
use App\Form\User\ProfileFilterFormType;
use App\Form\User\ProfileFormType;
use App\Form\User\ProfilePasswordFormType;
use App\Repository\UserRepository;
use App\Utils\SecurityUtils;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/users')]
class UserController extends AbstractController
{
    public function __construct
    (
        private readonly UserRepository $profileRepository,
        private readonly EntityManagerInterface $em,
        private readonly SecurityUtils $securityUtils,
        private readonly PaginatorInterface $paginator
    )
    {
    }

    private function checkUser(?User $profile): void
    {
        if (!$profile instanceof User) {
            throw $this->createNotFoundException('L\'utilisateur n\'existe pas');
        }
    }

    private function checkUserUpdatingRights(User $profile): void
    {
        if (!$this->isGranted('ROLE_ADMIN') && $profile !== $this->getUser()) {
            throw $this->createNotFoundException('Vous n\'avez pas les droits pour modifier cet utilisateur');
        }
    }

    #[Route('', name: RouteConstants::FRONTOFFICE_USERS, methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1) < 1 ? 1 : $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10) < 1 ? 10 : $request->query->getInt('limit', 10);

        // Handle the form submission
        $filters = $this->createForm(ProfileFilterFormType::class, [
            'search' => $request->query->get('search'),
            'order' => $request->query->get('order') ?? 'createdAt',
            'direction' => $request->query->get('direction') ?? 'DESC'
        ]);
        $filters->handleRequest($request);

        $requestFilters = [
            'search' => $filters->get('search')->getData(),
            'roles' => $filters->get('roles')->getData(),
            'isVerified' => $filters->get('isVerified')->getData(),
            'isBanned' => $filters->get('isBanned')->getData(),
            'order' => $filters->get('order')->getData() ?? 'createdAt',
            'direction' => $filters->get('direction')->getData() ?? 'DESC'
        ];
        $items = $this->profileRepository->findByFilters($requestFilters);

        $profiles= $this->paginator->paginate(
            $items,
            $page,
            $limit
        );

        return $this->render('views/frontoffice/user/index.html.twig', [
            'profiles' => $profiles,
            'filters' => $filters->createView()
        ]);
    }

    #[Route('/{id}', name: RouteConstants::FRONTOFFICE_USERS_SHOW, methods: ['GET'])]
    public function show(?User $profile): Response
    {
        $this->checkUser($profile);
        return $this->render('views/frontoffice/user/show.html.twig', [
            'profile' => $profile
        ]);
    }

    #[Route('/{id}/edit', name: RouteConstants::FRONTOFFICE_USERS_EDIT, methods: ['GET', 'POST'])]
    public function edit(Request $request, ?User $profile): Response|RedirectResponse
    {
        $this->checkUser($profile);
        $this->checkUserUpdatingRights($profile);

        $form = $this->createForm(ProfileFormType::class, $profile);
        if (!$this->isGranted('ROLE_ADMIN')) {
            $form->remove('roles');
        }
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->flush();

                // REASON FOR THIS : to prevent serialization of the file
                $profile->setImageFile(null);

                $this->addFlash(ToastConstants::SUCCESS, 'L\'utilisateur a bien été modifiée');

                return $this->redirectToRoute(RouteConstants::FRONTOFFICE_USERS);
            }
            $this->addFlash(ToastConstants::ERROR, 'L\'utilisateur n\'a pas pu être modifiée');
        }

        return $this->render('views/frontoffice/user/edit.html.twig', [
            'form' => $form,
            'profile' => $profile
        ]);
    }

    #[Route('/{id}/edit-email', name: RouteConstants::FRONTOFFICE_USERS_EDIT_EMAIL, methods: ['GET', 'POST'])]
    public function editEmail(Request $request, ?User $profile): Response|RedirectResponse
    {
        $this->checkUser($profile);
        $this->checkUserUpdatingRights($profile);

        $form = $this->createForm(ProfileEmailFormType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // check if old password is valid
                if (!$profile->isPasswordValid($form->get('oldPassword')->getData())) {
                    $this->addFlash(ToastConstants::ERROR, 'L\'ancien mot de passe est incorrect');
                    return $this->redirectToRoute(RouteConstants::FRONTOFFICE_USERS_EDIT_EMAIL, ['id' => $profile->getId()]);
                }
                // set verified to false
                $profile->setIsVerified(false);

                // send email confirmation
                $this->securityUtils->sendEmailConfirmation($profile);

                $this->em->flush();
                $this->addFlash(ToastConstants::SUCCESS, 'L\'email a bien été modifiée');

                return $this->redirectToRoute(RouteConstants::FRONTOFFICE_USERS);
            }
        }

        return $this->render('views/frontoffice/user/edit_email.html.twig', [
            'form' => $form,
            'profile' => $profile
        ]);
    }

    #[Route('/{id}/edit-password', name: RouteConstants::FRONTOFFICE_USERS_EDIT_PASSWORD, methods: ['GET', 'POST'])]
    public function editPassword(Request $request, ?User $profile, UserPasswordHasherInterface $userPasswordHasher): Response|RedirectResponse
    {
        $this->checkUser($profile);
        $this->checkUserUpdatingRights($profile);

        $form = $this->createForm(ProfilePasswordFormType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // check if old password is valid
                if (!$profile->isPasswordValid($form->get('oldPassword')->getData())) {
                    $this->addFlash(ToastConstants::ERROR, 'L\'ancien mot de passe est incorrect');
                    return $this->redirectToRoute(RouteConstants::FRONTOFFICE_USERS_EDIT_PASSWORD, ['id' => $profile->getId()]);
                }
                // encode the plain password
                $profile->setPassword(
                    $userPasswordHasher->hashPassword(
                        $profile,
                        $form->get('plainPassword')->getData()
                    )
                );

                $this->em->flush();
                $this->addFlash(ToastConstants::SUCCESS, 'Le mot de passe a bien été modifiée');

                return $this->redirectToRoute(RouteConstants::FRONTOFFICE_USERS);
            }
        }

        return $this->render('views/frontoffice/user/edit_password.html.twig', [
            'form' => $form,
            'profile' => $profile
        ]);
    }

    #[Route('/{id}/verify-email', name: RouteConstants::FRONTOFFICE_USERS_VERIFY_EMAIL, methods: ['GET'])]
    public function verifyEmail(Request $request, ?User $profile): Response|RedirectResponse
    {
        $this->checkUser($profile);
        $this->checkUserUpdatingRights($profile);

        $this->securityUtils->sendEmailConfirmation($profile);

        $this->addFlash(ToastConstants::SUCCESS, 'Un email de vérification a été envoyé');

        return $this->redirectToRoute(RouteConstants::FRONTOFFICE_USERS_SHOW, ['id' => $profile->getId()]);
    }

    #[Route('/{id}', name: RouteConstants::FRONTOFFICE_USERS_DELETE, methods: ['POST'])]
    public function delete(Request $request, ?User $profile): Response|RedirectResponse
    {
        $this->checkUser($profile);
        $this->checkUserUpdatingRights($profile);

        if ($this->isCsrfTokenValid('delete' . $profile->getId(), $request->request->get('_token'))) {
            $this->em->remove($profile);
            $this->em->flush();
            $this->addFlash(ToastConstants::SUCCESS, 'L\'utilisateur a bien été supprimée');
        } else {
            $this->addFlash(ToastConstants::ERROR, 'L\'utilisateur n\'a pas pu être supprimée');
        }

        return $this->redirectToRoute(RouteConstants::FRONTOFFICE_USERS);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/ban', name: RouteConstants::FRONTOFFICE_USERS_BAN, methods: ['GET', 'POST'])]
    public function ban(Request $request, ?User $profile): Response|RedirectResponse
    {
        $this->checkUser($profile);

        if ($this->isCsrfTokenValid('ban' . $profile->getId(), $request->request->get('_token'))) {
            $banned = $profile->isBanned();
            $profile->setIsBanned(!$banned);
            $this->em->flush();
            $message = $banned ? 'L\'utilisateur a bien été débanni' : 'L\'utilisateur a bien été banni';
            $this->addFlash(ToastConstants::SUCCESS, $message);
        } else {
            $this->addFlash(ToastConstants::ERROR, 'L\'utilisateur n\'a pas pu être banni');
        }

        return $this->redirectToRoute(RouteConstants::FRONTOFFICE_USERS);
    }
}
