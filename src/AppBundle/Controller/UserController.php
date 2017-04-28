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
    function listUsers()
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
    function editUser(Request $request, User $user)
    {
        $editForm = $this->createForm(UserType::class, $user);
        $editForm->handleRequest($request);

//        $em = $this->getDoctrine()->getManager();
//
//        if ($editForm->isSubmitted() && $editForm->isValid()) {
//            $mimeType = $editForm['imageName']->getData()->getMimeType();
//
//            if ($mimeType == 'image/jpeg' || $mimeType == 'image/jpg') {
//                $extension = explode("/", $mimeType)[1];
//                $newImgName = time() . "-" . rand(1, 999999) . "." . $extension;
//
//                $editForm['imageName']->getData()->move('images/categories', $newImgName);
//
//                $category->setImageName($newImgName);
//            }
//
//            if ($category->getDiscount() < 0) {
//                $this->get('session')->getFlashBag()->add('error', 'Discount should be 0 or bigger!');
//
//                return $this->render('products/edit_category.html.twig', array(
//                    'category' => $category,
//                    'edit_form' => $editForm->createView()
//                ));
//            }
//
//            $em->flush();
//
//            return $this->redirectToRoute('admin_categories');
//        }

        return $this->render('user/edit_user.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView()
        ));
    }

    /**
     * @Route("/ban-user/{id}", name="ban_user")
     */
    function banUser(int $id)
    {
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($id);

        $user->setBan(true);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/unban-user/{id}", name="unban_user")
     */
    function unbanUser(int $id)
    {
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($id);

        $user->setBan(false);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('admin_users');
    }
}
