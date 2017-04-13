<?php
/**
 * Created by PhpStorm.
 * User: stoyanminchev
 * Date: 4/9/17
 * Time: 2:41 PM
 */

namespace AppBundle\Controller;

use SoftUniBlogBundle\Entity\Products;
use SoftUniBlogBundle\Entity\Categories;
use SoftUniBlogBundle\Entity\User;
use SoftUniBlogBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductsController extends Controller
{
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