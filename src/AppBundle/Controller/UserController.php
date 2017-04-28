<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BoughtProducts;
use AppBundle\Entity\User;
use AppBundle\Form\EditUserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Role;
use AppBundle\Form\UserType;
use AppBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{

    const ROLE_DEFAULT = 'ROLE_USER';

    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (strlen($user->getFullName()) < 5) {
                $this->get('session')->getFlashBag()->add('error', 'Full name must be at least 5 charachters!');

                return $this->render(
                    'user/register.html.twig',
                    [
                        'form' => $form->createView()
                    ]
                );
            }

            if (strlen($user->getPassword()) < 5) {
                $this->get('session')->getFlashBag()->add('error', 'Password must be at least 5 charachters!');

                return $this->render(
                    'user/register.html.twig',
                    [
                        'form' => $form->createView()
                    ]
                );
            }

            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $roleRepo = $this->getDoctrine()->getRepository(Role::class);
            $role = $roleRepo->findOneBy(['name' => self::ROLE_DEFAULT]);

            $user->addRole($role);
            $user->setMoney(10000);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Register was successful!');

            return $this->redirectToRoute('security_login');
        }

        return $this->render(
            'user/register.html.twig',
            array('form' => $form->createView())
        );
    }


    /**
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/profile", name="user_profile")
     */
    public function profileAction()
    {
        $userId = $this->getUser()->getId();

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($userId);

        if ($user->isBan()) {
            $this->get('session')->getFlashBag()->add('error', 'This functionality is not allowed for banned users!');

            return $this->redirectToRoute('blog_index');
        }

        $boughtProducts = $this->getDoctrine()
            ->getRepository("AppBundle:BoughtProducts")
            ->findBy(['uid' => $userId]);

        $products = [];

        /**
         * @var $product BoughtProducts
         */
        foreach ($boughtProducts as $product) {
            $productId = $product->getPid();
            $quantity = $product->getQuantity();

            $productProperties = $this->getDoctrine()
                ->getRepository("AppBundle:Products")
                ->findOneBy(['id' => $productId]);

            $products[] = ['name' => $productProperties->getName(), 'imageName' => $productProperties->getImageName(), 'price' => $productProperties->getPrice(), 'quantity' => $quantity];
        }

        return $this->render("user/profile.html.twig", [
            'boughtProducts' => $products,
            'user' => $user
        ]);
    }

    /**
     * @Route("/users", name="admin_users")
     */
    public function listUsers()
    {
        $users = $this->getDoctrine()
            ->getRepository("AppBundle:User")
            ->findAll();

        return $this->render('user/admin_users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/edit-user/{id}", name="edit_user")
     */
    public function editUser(Request $request, User $user)
    {
        $editForm = $this->createForm(EditUserType::class, $user);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            if ($user->getMoney() < 0) {
                $this->get('session')->getFlashBag()->add('error', 'Money should be 0 or bigger!');

                return $this->render('user/edit_user.html.twig', array(
                    'user' => $user,
                    'edit_form' => $editForm->createView()
                ));
            }

            $user->setFullName($user->getFullName());
            $user->setMoney($user->getMoney());
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'User properties were edited successfully!');

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('user/edit_user.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView()
        ));
    }

    /**
     * @Route("/ban-user/{id}", name="ban_user")
     */
    public function banUser(int $id)
    {
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($id);

        $user->setBan(true);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add('success', 'User was banned successfully!');

        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/unban-user/{id}", name="unban_user")
     */
    public function unbanUser(int $id)
    {
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($id);

        $user->setBan(false);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add('success', 'User was unbanned successfully!');

        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/delete-user/{id}", name="delete_user")
     */
    public function deleteUser(int $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($id);

        $boughtProducts = $this->getDoctrine()
            ->getRepository('AppBundle:BoughtProducts')
            ->findBy(['uid' => $id]);

        foreach ($boughtProducts as $boughtProduct) {
            $em->remove($boughtProduct);
        }

        $em->remove($user);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'User and his product(s) were deleted successfully!');

        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/user-possessions/{id}", name="list_user_possessions")
     */
    public function listUserPossessions(int $id)
    {
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($id);

        $boughtProducts = $this->getDoctrine()
            ->getRepository('AppBundle:BoughtProducts')
            ->findBy(['uid' => $id]);

        $products = [];

        /**
         * @var $product BoughtProducts
         */
        foreach ($boughtProducts as $product) {
            $productId = $product->getPid();
            $quantity = $product->getQuantity();

            $productProperties = $this->getDoctrine()
                ->getRepository("AppBundle:Products")
                ->findOneBy(['id' => $productId]);

            $products[] = ['id' => $productId, 'name' => $productProperties->getName(), 'imageName' => $productProperties->getImageName(), 'price' => $productProperties->getPrice(), 'quantity' => $quantity];
        }

        return $this->render('user/list_user_possessions.html.twig', [
            'boughtProducts' => $products,
            'userFullName' => $user->getFullName()
        ]);
    }
}
