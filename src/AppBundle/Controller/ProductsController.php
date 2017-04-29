<?php
/**
 * Created by PhpStorm.
 * User: stoyanminchev
 * Date: 4/9/17
 * Time: 2:41 PM
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Products;
use AppBundle\Entity\Categories;
use AppBundle\Entity\User;
use AppBundle\Form\CategoryType;
use AppBundle\Form\EditCategoryType;
use AppBundle\Form\ProductEditType;
use AppBundle\Form\ProductType;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductsController extends Controller
{
    /**
     * @Route("/", name="blog_index")
     */
    public function listCategories()
    {
        $categories = $this->getDoctrine()
            ->getRepository("AppBundle:Categories")
            ->findAll();

        /**
         * @var $category \AppBundle\Entity\Categories
         */
        foreach ($categories as $category) {
            $products = $this->getDoctrine()
                ->getRepository("AppBundle:Products")
                ->findBy(['catId' => $category->getId()]);

            $category->setProductsNumber(count($products));
        }

        return $this->render("products/categories.html.twig", [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/categories/{catId}", name="products_list")
     */
    public function listProducts(int $catId)
    {
        $userId = $this->getUser()->getId();

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($userId);

        $products = $this->getDoctrine()
            ->getRepository("AppBundle:Products")
            ->findBy(['catId' => $catId]);

        $count = count($products);
        $len = 0;

        $debug = [];

        $result = [];
        $tmp = [];

        for ($i = 0; $i < $count; $i++) {
            if ($len == 4) {
                $result[] = $tmp;
                $tmp = [];
                $tmp[] = $products[$i];
                $len = 0;
            } else {
                $tmp[] = $products[$i];
                $len++;
            }
        }

        $result[] = $tmp;

        return $this->render("products/index.html.twig", [
            'result' => $result,
            'catId' => $catId,
            'discount' => $user->getDiscount()
        ]);
    }

    /**
     * @Route("/product/{id}", name="product_info")
     */
    public function product(int $id)
    {
        $userId = $this->getUser()->getId();

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($userId);

        $product = $this->getDoctrine()
            ->getRepository("AppBundle:Products")
            ->findOneBy(['id' => $id]);

        return $this->render("products/product.html.twig", [
            'product' => $product,
            'discount' => $user->getDiscount()
        ]);
    }

    /**
     * @Route("/add-product", name="add_product")
     */
    public function addProduct(Request $request)
    {
        $product = new Products();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mimeType = $form['imageName']->getData()->getMimeType();

            if ($mimeType == 'image/jpeg' || $mimeType == 'image/jpg') {
                $flag = true;
            } else {
                return $this->render(
                    'products/add_product.html.twig', [
                        'form' => $form->createView()
                    ]
                );
            }

            if ($form['quantity']->getData() > 0) {
                $flag = true;
            } else {
                return $this->render(
                    'products/add_product.html.twig', [
                        'form' => $form->createView()
                    ]
                );
            }

            if ($form['price']->getData() > 0) {
                $flag = true;
            } else {
                return $this->render(
                    'products/add_product.html.twig', [
                        'form' => $form->createView()
                    ]
                );
            }

            if ($mimeType == 'image/jpeg' || $mimeType == 'image/jpg') {
                $flag = true;
            } else {
                return $this->render(
                    'products/add_product.html.twig', [
                        'form' => $form->createView()
                    ]
                );
            }

            if ($flag) {
                $em = $this->getDoctrine()->getManager();

                $extension = explode("/", $mimeType)[1];
                $newImgName = time() . "-" . rand(1, 999999) . "." . $extension;

                $form['imageName']->getData()->move('images/products', $newImgName);

                $product->setImageName($newImgName);

                $em->persist($product);
                $em->flush();
            }

            return $this->redirectToRoute('admin_products');
        }

        return $this->render(
            'products/add_product.html.twig', [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/add-category", name="add_category")
     */
    public function addCategory(Request $request)
    {
        $category = new Categories();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mimeType = $form['imageName']->getData()->getMimeType();

            if ($mimeType == 'image/jpeg' || $mimeType == 'image/jpg') {
                $em = $this->getDoctrine()->getManager();

                $extension = explode("/", $mimeType)[1];
                $newImgName = time() . "-" . rand(1, 999999) . "." . $extension;

                $form['imageName']->getData()->move('images/categories', $newImgName);

                $category->setImageName($newImgName);

                $em->persist($category);
                $em->flush();
            } else {
                return $this->render(
                    'products/add_category.html.twig', [
                        'form' => $form->createView()
                    ]
                );
            }

            return $this->redirectToRoute('admin_categories');
        }

        return $this->render(
            'products/add_category.html.twig', [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/categories", name="admin_categories")
     */
    public function adminCategories()
    {
        $categories = $this->getDoctrine()
            ->getRepository("AppBundle:Categories")
            ->findAll();

        return $this->render('products/admin_categories.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/products", name="admin_products")
     */
    public function adminProducts()
    {
        $products = $this->getDoctrine()
            ->getRepository('AppBundle:Products')
            ->findAll();

        return $this->render('products/admin_products.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * @Route("/users", name="admin_users")
     */
    public function adminUsers()
    {
        $users = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findAll();

        return $this->render('products/admin_products.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * Displays a form to edit an existing project entity.
     *
     * @Route("/edit-product/{id}", name="edit_product")
     */
    public function editProduct(Request $request, Products $product)
    {
        $editForm = $this->createForm(ProductEditType::class, $product);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            if ($editForm['quantity']->getData() < 0) {
                $this->get('session')->getFlashBag()->add('error', 'Quantity should be 0 or bigger!');

                return $this->render('products/edit_product.html.twig', array(
                    'product' => $product,
                    'edit_form' => $editForm->createView()
                ));
            }

            if ($editForm['price']->getData() <= 0) {
                $this->get('session')->getFlashBag()->add('error', 'Price should be bigger than 0!');

                return $this->render('products/edit_product.html.twig', array(
                    'product' => $product,
                    'edit_form' => $editForm->createView()
                ));
            }

            if (!($editForm['discount']->getData() >= 0 &&  $editForm['discount']->getData() <= 100)) {
                $this->get('session')->getFlashBag()->add('error', 'Discount should be number between 0 and 100!');

                return $this->render('products/edit_product.html.twig', array(
                    'product' => $product,
                    'edit_form' => $editForm->createView()
                ));
            }

            $em->flush();

            return $this->redirectToRoute('admin_products');
        }

        $this->get('session')->getFlashBag()->add('success', 'Product is edited successfully!');

        return $this->render('products/edit_product.html.twig', array(
            'product' => $product,
            'edit_form' => $editForm->createView()
        ));
    }

    /**
     * Displays a form to edit an existing project entity.
     *
     * @Route("/edit-category/{id}", name="edit_category")
     */
    public function editCategory(Request $request, Categories $category)
    {
        $editForm = $this->createForm(EditCategoryType::class, $category);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            if (!($editForm['discount']->getData() >= 0 &&  $editForm['discount']->getData() <= 100)) {
                $this->get('session')->getFlashBag()->add('error', 'Discount should be 0 or bigger!');

                return $this->render('products/edit_category.html.twig', array(
                    'category' => $category,
                    'edit_form' => $editForm->createView()
                ));
            }

            $em->flush();

            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('products/edit_category.html.twig', array(
            'category' => $category,
            'edit_form' => $editForm->createView()
        ));
    }

    /**
     * Deletes a project entity.
     *
     * @Route("/delete-product/{id}", name="product_delete")
     */
    public function deleteProduct(Products $product)
    {
        $boughtProduct = $this->getDoctrine()
            ->getRepository('AppBundle:BoughtProducts')
            ->find($product->getId());

        if (!is_null($boughtProduct)) {
            $this->get('session')->getFlashBag()->add('error', 'Product is bought by user!');

            return $this->redirectToRoute('admin_products');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Product was deleted successfully!');

        return $this->redirectToRoute('admin_products');
    }

    /**
     * Deletes a project entity.
     *
     * @Route("/delete-category/{id}", name="category_delete")
     */
    public function deleteCategory(Request $request, Categories $category)
    {
        $products = $this->getDoctrine()
            ->getRepository('AppBundle:Products')
            ->findBy(['catId' => $category->getId()]);

        if (count($products) > 0) {
            $this->get('session')->getFlashBag()->add('error', 'Category has 1 or more products and should not be deleted!');

            return $this->redirectToRoute('admin_categories');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('admin_categories');
    }
}