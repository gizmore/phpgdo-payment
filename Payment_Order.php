<?php
namespace GDO\Payment;

use GDO\Core\GDO_Error;
use GDO\Core\GDO;
use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\Core\GDT_Response;
use GDO\Session\GDO_Session;
use GDO\UI\GDT_HTML;
use GDO\Address\GDT_Address;
use GDO\UI\GDT_Button;
use GDO\Core\GDT_Hook;

abstract class Payment_Order extends MethodForm
{
	/**
	 * @return 
	 */
	public abstract function getOrderable(): Orderable;
	
	public abstract function onCancelOrder(): void;
	
	public function isUserRequired() : bool { return true; }
	
	public function execute()
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
	
	public function formValidated(GDT_Form $form)
	{
		return $this->initOrderable($form);
	}
	
	public function initOrderable(GDT_Form $form=null)
	{
// 		$user = GDO_User::current();
		$orderable = $this->getOrderable();
		if (!($orderable instanceof GDO))
		{
			throw new GDO_Error('err_gdo_type', [$this->order->gdoClassName(), 'GDO']);
		}
		if (!($orderable instanceof Orderable))
		{
			throw new GDO_Error('err_gdo_type', [$this->order->gdoClassName(), 'Orderable']);
		}
		
		GDO_Session::set('gdo_orderable', $orderable);
// 		$user->tempSet('gdo_orderable', $orderable);
// 		$user->recache();
		
		return $this->renderOrderableForm($orderable);
	}
	
	public function renderOrderableForm(Orderable $orderable)
	{
		$form = GDT_Form::make('form');
		$form->action(href('Payment', 'Choose'));
		$form->addField(GDT_Address::make('order_address')->onlyOwn()->emptyLabel('order_needs_address_first')->required()); 
		foreach (PaymentModule::allPaymentModules() as $module)
		{
			if ($orderable->canPayOrderWith($module))
			{
				$form->addField($module->makePaymentButton());
			}
		}
		$form->addField(GDT_Button::make('link_add_address')->href(href('Address', 'Add', "&_rb=".($_SERVER['REQUEST_URI']))));
		return GDT_Response::makeWith(GDT_HTML::make()->var($orderable->renderOrderCard()))->addField(GDT_Response::makeWith($form));
	}
	
}
