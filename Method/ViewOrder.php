<?php
namespace GDO\Payment\Method;

use GDO\Core\Method;
use GDO\User\GDO_User;
use GDO\Payment\GDO_Order;
use GDO\Core\GDT_Object;

final class ViewOrder extends Method
{
	public function gdoParameters() : array
	{
		return array(
			GDT_Object::make('id')->table(GDO_Order::table())->notNull(),
		);
	}
	
	/**
	 * @return GDO_Order
	 */
	public function getOrder()
	{
		return $this->gdoParameterValue('id');
	}
	
	public function hasPermission(GDO_User $user) : bool
	{
		if ($order = $this->getOrder())
		{
			return $order->getCreator() === $user;
		}
		return $this->error('err_no_permission');
	}
	
	public function execute()
	{
		$tVars = array(
			'order' => $this->getOrder(),
		);
		return $this->templatePHP('view_order.php', $tVars);
	}

	
}
