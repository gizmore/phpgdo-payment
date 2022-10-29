<?php
namespace GDO\Payment;

use GDO\Core\Method;
use GDO\Session\GDO_Session;
use GDO\UI\GDT_HTML;

abstract class MethodPayment extends Method
{
	
	public function getMethodTitle(): string
	{
		return t('payment');
	}
	
	private GDO_Order $order;
	
	public function isShownInSitemap() : bool { return false; }
	
	public function isAlwaysTransactional() : bool { return true; }
	
	/**
	 * @return GDO_Order
	 */
	public function getOrder()
	{
		return GDO_Session::get('gdo_order');
	}
	
	public function setOrder(GDO_Order $order)
	{
		GDO_Session::set('gdo_order', $order);
		$this->order = $order;
	}

	/**
	 * @return GDO_Order
	 */
	public function getOrderPersisted()
	{
		if ($this->order = $this->getOrder())
		{
			if ($this->order instanceof GDO_Order)
			{
				if (!$this->order->isPersisted())
				{
					$this->order->insert();
					$this->setOrder($this->order);
				}
			}
		}
		return $this->order;
	}
	
	public function renderOrder(GDO_Order $order)
	{
		return GDT_HTML::make()->var($order->renderCard());
	}
	
}
