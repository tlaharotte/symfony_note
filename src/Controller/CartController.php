<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Command;
use App\Form\CommandType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart.index")
     */
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('panier', []);
        $products=[];
        
        $total=0;

        foreach($cart as $id => $quantity)
        {
            $products[$id] = $productRepository->find($id);
            $total += $products[$id]->getPrice();
        }

        $command = new Command();
        $commandForm = $this->createForm(CommandType::class, $command);

        return $this->render('cart/index.html.twig', [
            'products' => $products,
            'commandForm' => $commandForm->createView(),
            'total'=>$total
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="cart.add")
     */
    public function add(ProductRepository $productRepository, SessionInterface $session, Request $request)
    {
        $id = $request->attributes->get('id');
        $product=$productRepository->find($id);
        if (!$product){
            return $this->json([
                'message' => 'nok'
            ], 404);
        }
        $cart = $session->get('panier', []);
        $cart[$id] = 1;
        $session->set('panier', $cart);

        return $this->json([
            'message' => "ok"
        ], 200);
    }

    /**
     * @Route("/cart/delete/{id}", name="cart.delete")
     */
    public function delete(ProductRepository $productRepository, SessionInterface $session, Request $request)
    {
        $id = $request->attributes->get('id');
        $product=$productRepository->find($id);
        $cart = $session->get('panier', []);
        if(!isset($cart[$id])){
            $this->addFlash('alert-error', "Le produit non présent dans le panier");
            return $this->redirectToRoute('cart');
        }

        unset($cart[$id]);
        $session->set('panier', $cart);
        return $this->redirectToRoute('cart.index');
    }
}
