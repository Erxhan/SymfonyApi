<?php

namespace Dev\ApiBundle\Controller;

use Dev\ApiBundle\Entity\AuthToken;
use Dev\ApiBundle\Entity\Image;
use Dev\ApiBundle\Entity\Login;
use Dev\ApiBundle\Entity\User;
use Dev\ApiBundle\Form\ImageType;
use Dev\ApiBundle\Form\LoginType;
use Dev\ApiBundle\Form\RegistrationType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @Rest\Get(path="/users")
     * @Rest\View()
     * @return User[]
     */
    public function getUsersAction()
    {
        $userManager = $this->getDoctrine()->getRepository('DevApiBundle:User');
        $users = $userManager->findAllWithAvatarAndEnabled();
        return $users;
    }

    /**
     * @return User[]
     * @Rest\Get(path="/profile/friends")
     * @Rest\View()
     */
    public function getFriendsForUserAction()
    {
        $userManager = $this->getDoctrine()->getRepository('DevApiBundle:User');
        /** @var  $userLogged */
        $userLogged = $this->getUser();
        $UserWithFriends = $userManager->findFriendsByUser($userLogged->getId());
        return $UserWithFriends->getFriends();
    }

    /**
     * @Rest\Get(path="/profile")
     * @Rest\View()
     * @return User | View
     */
    public function profileAction()
    {
        /** @var User $userLogged */

        $repo = $this->getDoctrine()->getManager()->getRepository('DevApiBundle:User');
        $userLogged = $this->getUser();
        $userProfile = $repo->findOneWithAvatar($userLogged->getId());
        if (!$userLogged) {
            return $this->invalidCredentials();
        } else {
            return $userProfile;
        }
    }

    /**
     * @Rest\Post(path="/login")
     * @Rest\View(statusCode=Response::HTTP_ACCEPTED)
     * @param Request $request
     * @return AuthToken | View | Form
     */
    public function loginAction(Request $request)
    {
        $login = new Login();

        $form = $this->createForm(LoginType::class, $login);
        $form->submit($request->request->all());

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DevApiBundle:User')->findOneBy(['username' => $login->getUsername()]);

        if ($user === null) {
            return $this->invalidCredentials();
        }

        $encoder = $this->get('security.password_encoder');
        $isPasswordValid = $encoder->isPasswordValid($user, $login->getPassword());

        if (!$isPasswordValid) {
            return $this->invalidCredentials();
        }

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $form;
        }

        $token = $em->getRepository('DevApiBundle:AuthToken')->findOneBy(['user' => $user]);

        if ($token === null) {
            $token = new AuthToken();
            $token->setUser($user);
            $generator = $this->get('fos_user.util.token_generator');
            $token->setAuthTokenValue($generator->generateToken());
        }
        $token->setAuthTokenCreatedAt(new \DateTime());

        if ($token->getAuthTokenID() === null) {
            $em->persist($token);
        }
        $user->setIsOnline(true);
        $em->flush();
        return $token;
    }

    /**
     * @return View
     */
    private function invalidCredentials()
    {
        return View::create(array('message' => 'Bad credentials'), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\Delete(path="/disconnect")
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     */
    public function logoutAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var AuthToken $authToken */
        $authToken = $em->getRepository('DevApiBundle:AuthToken')->findOneBy(['user' => $this->getUser()]);

        if ($authToken === null) {
            return;
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($user->getId() === $authToken->getUser()->getId()) {
            $user->setIsOnline(false);
            $em->remove($authToken);
            $em->flush();
        }
    }

    /**
     * @Rest\Post(path="/register")
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @param Request $request
     * @return User | Form
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $user->setEnabled(true);

        $form = $this->createForm(RegistrationType::class, $user);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user);
            return $user;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\Post(path="/profile/picture")
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @param Request $request
     * @return Image | Form | View
     */
    public function imageUploadAction(Request $request)
    {
        $image = new Image();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if ($user === null) {
            return View::create('User does not exist', Response::HTTP_NOT_FOUND);
        }

        $data = $request->request->all();
        $data['file'] = $request->files->get('file');

        $form = $this->createForm(ImageType::class, $image);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getAvatar() !== null) {
                $em->remove($user->getAvatar());
                $em->flush();
            }
            $user->setAvatar($image);
            $em->persist($image);
            $em->flush();
            return $image;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\Put(path="/profile")
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @param Request $request
     * @return View
     */
    public function updateUserAction(Request $request)
    {
        return $this->updateUser($request, false);
    }

    /**
     * @Rest\Patch(path="/profile")
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @param Request $request
     * @return View
     */
    public function patchUserAction(Request $request)
    {
        return $this->updateUser($request, true);
    }

    /**
     * @Rest\Delete(path="/profile")
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     */
    public function deleteUserAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setEnabled(false);
        //$userManager = $this->get('fos_user.user_manager');
        //$userManager->deleteUser($user);
    }

    private function updateUser(Request $request, $clearMissing)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $this->getUser();

        if ($user === null) {
            return View::create('User does not exist', Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(RegistrationType::class, $user);
        $form->setData($user);
        $form->submit($request->request->all(), $clearMissing);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($user);
            return View::create('Utilisateur mis à jour');
        } else {
            return $form;
        }
    }

}
