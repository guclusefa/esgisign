<?php

namespace App\Controller\Security;

use App\Constants\RouteConstants;
use App\Constants\ToastConstants;
use App\Constants\UserConstants;
use App\Entity\User;
use App\Form\Security\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use App\Utils\SecurityUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct
    (
        EmailVerifier $emailVerifier,
        private readonly SecurityUtils $securityUtils
    )
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: RouteConstants::SECURITY_REGISTER)]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // set role
            $user->setRoles([UserConstants::ROLE_USER]);
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->securityUtils->sendEmailConfirmation($user);

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: RouteConstants::SECURITY_VERIFY_EMAIL)]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute(RouteConstants::SECURITY_REGISTER);
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute(RouteConstants::SECURITY_REGISTER);
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute(RouteConstants::SECURITY_REGISTER);
        }

        $this->addFlash(ToastConstants::SUCCESS, 'Votre email a bien été vérifié.');

        return $this->redirectToRoute(RouteConstants::FRONTOFFICE_HOME);
    }
}
