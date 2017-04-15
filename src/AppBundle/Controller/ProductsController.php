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
}