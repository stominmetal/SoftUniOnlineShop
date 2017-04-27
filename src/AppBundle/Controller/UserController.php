<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BoughtProducts;
use AppBundle\Entity\User;
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
    function listUsers() {
        $users = $this->getDoctrine()
            ->getRepository("AppBundle:User")
            ->findAll();

        return $this->render('products/admin_users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/edit-user", name="edit_user")
     */
    function editUser() {

    }

    /**
     * @Route("/ban-user", name="ban_user")
     */
    function banUser() {

    }
}
