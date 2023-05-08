<?php
namespace GDO\Payment;

use GDO\Address\GDT_Address;
use GDO\Core\GDO;
use GDO\Core\GDO_Exception;
use GDO\Core\GDT;
use GDO\Core\GDT_Hook;
use GDO\Core\GDT_Response;
use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\Session\GDO_Session;
use GDO\UI\GDT_Button;
use GDO\UI\GDT_HTML;

abstract class Payment_Order extends MethodForm
{

	public function isUserRequired(): bool { return true; }

	public function execute(): GDT
	{
		if (isset($this->inputs['cancel']))
		{
			$this->onCancelOrder();
			GDT_Hook::callHook('CancelOrder');
			GDO_Session::remove('gdo_orderable');
			return $this->message('msg_order_cancelled');
		}
		return parent::execute();
	}

	abstract public function onCancelOrder(): void;

	public function formValidated(GDT_Form $form): GDT
	{
		return $this->initOrderable($form);
	}

	public function initOrderable(GDT_Form $form = null)
	{
// 		$user = GDO_User::current();
		$orderable = $this->getOrderable();
		if (!($orderable instanceof GDO))
		{
			throw new GDO_Exception('err_gdo_type', [$this->order->gdoClassName(), 'GDO']);
		}
		if (!($orderable instanceof Orderable))
		{
			throw new GDO_Exception('err_gdo_type', [$this->order->gdoClassName(), 'Orderable']);
		}

		GDO_Session::set('gdo_orderable', $orderable);
// 		$user->tempSet('gdo_orderable', $orderable);
// 		$user->recache();

		return $this->renderOrderableForm($orderable);
	}

	/**
	 * @return
	 */
	abstract public function getOrderable(): Orderable;

	public function renderOrderableForm(Orderable $orderable)
	{
		$form = GDT_Form::make('form');
		$form->action(href('Payment', 'Choose'));
		$form->addField(GDT_Address::make('order_address')->onlyOwn()->emptyLabel('order_needs_address_first')->notNull());
		foreach (PaymentModule::allPaymentModules() as $module)
		{
			if ($orderable->canPayOrderWith($module))
			{
				$form->addField($module->makePaymentButton());
			}
		}
		$form->addField(GDT_Button::make('link_add_address')->href(href('Address', 'Add', '&_rb=' . ($_SERVER['REQUEST_URI']))));
		return GDT_Response::makeWith(GDT_HTML::make()->var($orderable->renderOrderCard()))->addField(GDT_Response::makeWith($form));
	}

}
