<?php
/**
 * Created by PhpStorm.
 * User: stoyanminchev
 * Date: 4/11/17
 * Time: 11:05 AM
 */

namespace AppBundle\Controller;

use AppBundle\Entity\BoughtProducts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CartController extends Controller
{
    /**
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/addtocart/{id}", name="add_to_cart")
     */
    public function addToCart(Request $request)
    {
        $userId = $this->getUser()->getId();

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($userId);

        if ($user->isBan()) {
            $this->get('session')->getFlashBag()->add('error', 'This functionality is not allowed for banned users!');

            return $this->redirectToRoute('blog_index');
        }

        $id = intval($request->attributes->get('id'));

        $product = $this->getDoctrine()
            ->getRepository("AppBundle:Products")
            ->findOneBy(['id' => $id]);

        $session = $request->getSession();

        if ($session->get($userId)) {
            $tmp = $session->get($userId);

            foreach ($tmp as $pid => $quantity) {
                if ($id === $pid) {
                    if ($product->getQuantity() > $quantity) {
                        $tmp[$id]++;
                    }
                } else {
                    $tmp[$id] = 1;
                }
            }

            $session->set($userId, $tmp);
        } else {
            $session->set($userId, [$id => 1]);
        }

        $this->get('session')->getFlashBag()->add('success', 'Product is added to cart successfully!');

        return $this->redirectToRoute('product_info', [
            'id' => $id
        ]);
    }

    /**
     * @Route("/cart", name="cart_list")
     */
    public function listCart(Request $request)
    {
        $userId = $this->getUser()->getId();

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($userId);

        $userId = $this->getUser()->getId();

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($userId);

        if ($user->isBan()) {
            $this->get('session')->getFlashBag()->add('error', 'This functionality is not allowed for banned users!');

            return $this->redirectToRoute('blog_index');
        }

        $session = $request->getSession();

        $products = $session->get($this->getUser()->getId());

        if (is_null($products)) {
            return $this->render('products/cart.html.twig', [
                'products' => 'There are no products in the cart.'
            ]);
        } else {
            foreach ($products as $productId => $quantity) {
                $product = $this->getDoctrine()
                    ->getRepository("AppBundle:Products")
                    ->findOneBy(['id' => $productId]);

                if ($quantity > $product->getQuantity()) {
                    $quantity -= $quantity - $product->getQuantity();
                }

                $products[$productId] = ['name' => $product->getName(), 'price' => $product->getPrice(), 'imageName' => $product->getImageName(), 'quantity' => $quantity];
            }
        }

        return $this->render('products/cart.html.twig', [
            'products' => $products,
            'discount' => $user->getDiscount()
        ]);
    }

    /**
     * @Route("cart/remove_element/{id}", name="remove_element_from_cart")
     */
    public function removeElement(int $id, Request $request)
    {
        $session = $request->getSession();

        $products = $session->get($this->getUser()->getId());

        unset($products[$id]);

        $session->set($this->getUser()->getId(), $products);

        $this->get('session')->getFlashBag()->add('success', 'Product is removed successfully!');

        return $this->redirectToRoute('cart_list');
    }

    /**
     * @Route("cart/buy_all", name="buy_all_products")
     */
    public function buyAllProducts(Request $request)
    {
        $session = $request->getSession();

        $userId = $this->getUser()->getId();

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($userId);

        if ($user->isBan()) {
            $this->get('session')->getFlashBag()->add('error', 'This functionality is not allowed for banned users!');

            return $this->redirectToRoute('blog_index');
        }

        $products = $session->get($userId);

        $em = $this->getDoctrine()->getManager();

        $sum = 0;

        if ($user->getDiscount() == 0) {
            foreach ($products as $productId => $quantity) {
                $boughtProducts = $this->getDoctrine()
                    ->getRepository("AppBundle:Products")
                    ->find($productId);

                $sum += $boughtProducts->getPrice() * $quantity;
            }
        } else {
            foreach ($products as $productId => $quantity) {
                $boughtProducts = $this->getDoctrine()
                    ->getRepository("AppBundle:Products")
                    ->find($productId);

                $sum += (($boughtProducts->getPrice() * (100 - $user->getDiscount())) / 100) * $quantity;
            }
        }

        if ($user->getMoney() < $sum) {
            $this->get('session')->getFlashBag()->add('error', 'You do not have enough money!');

            return $this->redirectToRoute('cart_list');
        } else {
            $user->setMoney($user->getMoney() - $sum);
        }

        foreach ($products as $productId => $quantity) {
            $boughtProducts = $this->getDoctrine()
                ->getRepository("AppBundle:BoughtProducts")
                ->findOneBy(['uid' => $userId, 'pid' => $productId]);

            $product = $this->getDoctrine()
                ->getRepository('AppBundle:Products')
                ->find($productId);

            if ($product->getQuantity() < $quantity) {
                $this->get('session')->getFlashBag()->add('error', 'Product you want to bay has less quantity than you need!');

                return $this->redirectToRoute('cart_list');
            } else {
                $product->setQuantity($product->getQuantity() - $quantity);
            }

            if (is_null($boughtProducts)) {
                $product = new BoughtProducts();

                $product->setUid($userId);
                $product->setPid($productId);
                $product->setQuantity($quantity);

                $em->persist($product);
            } else {
                $boughtProducts->setQuantity(
                    $boughtProducts->getQuantity() + $quantity
                );

                $em->persist($boughtProducts);
            }
        }

        $em->flush();

        $session->remove($userId);

        $this->get('session')->getFlashBag()->add('success', 'Products were bought successfully!');

        return $this->redirectToRoute('blog_index');
    }
}