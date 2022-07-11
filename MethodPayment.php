<?php
namespace GDO\Payment;

use GDO\Core\Method;
use GDO\Session\GDO_Session;

abstract class MethodPayment extends Method
{
	/**
	 * @var GDO_Order
	 */
	private $order;
	
	public function showInSitemap() { return false; }
	
	public function isAlwaysTransactional() { return true; }
	
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
		return $order->responseCard();
	}
	
}
