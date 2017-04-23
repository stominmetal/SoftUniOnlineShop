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
use AppBundle\Form\ProductType;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
        $products = $this->getDoctrine()
            ->getRepository("AppBundle:Products")
            ->findBy(['catId' => $catId]);

        return $this->render("products/index.html.twig", [
            'products' => $products,
            'catId' => $catId
        ]);
    }

    /**
     * @Route("/product/{id}", name="product_info")
     */
    public function product(int $id)
    {
        $product = $this->getDoctrine()
            ->getRepository("AppBundle:Products")
            ->findOneBy(['id' => $id]);

        return $this->render("products/product.html.twig", [
            'product' => $product
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

        $flag = false;

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

        $flag = false;

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
    public function adminCategories() {
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
    public function adminProducts() {
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
    public function adminUsers() {
        $users = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findAll();

        return $this->render('products/admin_products.html.twig', [
            'users' => $users
        ]);
    }
}