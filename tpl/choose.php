<?php
use GDO\Payment\GDO_Order;
use GDO\Payment\Orderable;
use GDO\Payment\PaymentModule;
use GDO\UI\GDT_Bar;
use GDO\User\GDO_User;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Hidden;

/**
 * @var $user GDO_User
 * @var $orderable Orderable
 * @var $payment PaymentModule
 * @var $order GDO_Order
 */

$user instanceof GDO_User;
$orderable instanceof Orderable;
$payment instanceof PaymentModule;
$order instanceof GDO_Order;

// echo $orderable->renderOrderCard();

echo $order->renderCard();

$form = GDT_Form::make('form');
$form->addFields(array(
	GDT_Hidden::make('order_address')->initial($order->getAddressId()),
	GDT_Hidden::make('order_module')->initial($payment->getName()),
));
$bar = GDT_Bar::make()->horizontal();
$bar->addField($payment->makePaymentButton($order));
$form->addField($bar);
$form->addField(GDT_AntiCSRF::make());
echo $form->render();
