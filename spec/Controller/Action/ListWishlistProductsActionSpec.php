<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace spec\BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use BitBag\SyliusWishlistPlugin\Controller\Action\ListWishlistProductsAction;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Form\Type\WishlistCollectionType;
use BitBag\SyliusWishlistPlugin\Processor\WishlistCommandProcessorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class ListWishlistProductsActionSpec extends ObjectBehavior
{
    function let(
        WishlistContextInterface $wishlistContext,
        CartContextInterface $cartContext,
        FormFactoryInterface $formFactory,
        Environment $twigEnvironment,
        WishlistCommandProcessorInterface $wishlistCommandProcessor
    ): void {
        $this->beConstructedWith(
            $wishlistContext,
            $cartContext,
            $formFactory,
            $twigEnvironment,
            $wishlistCommandProcessor
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ListWishlistProductsAction::class);
    }

    function it_lists_wishlist_items(
        WishlistContextInterface $wishlistContext,
        Request $request,
        WishlistInterface $wishlist,
        CartContextInterface $cartContext,
        OrderInterface $cart,
        Collection $wishlistProducts,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        FormView $formView,
        Environment $twigEnvironment,
        WishlistCommandProcessorInterface $wishlistCommandProcessor,
        ArrayCollection $commandsArray
    ): void {
        $wishlistContext->getWishlist($request)->willReturn($wishlist);
        $cartContext->getCart()->willReturn($cart);
        $wishlist->getWishlistProducts()->willReturn($wishlistProducts);

        $wishlistCommandProcessor->createAddCommandCollectionFromWishlistProducts($wishlistProducts)->willReturn($commandsArray);

        $formFactory
            ->create(
                WishlistCollectionType::class,
                ['items' => $commandsArray],
                ['cart' => $cart]
            )
            ->willReturn($form);

        $form->createView()->willReturn($formView);
        $twigEnvironment
            ->render(
                '@BitBagSyliusWishlistPlugin/WishlistDetails/index.html.twig',
                [
                    'wishlist' => $wishlist,
                    'form' => $formView,
                ]
            )
            ->willReturn('CONTENT');

        $this->__invoke($request)->shouldHaveType(Response::class);
    }
}
