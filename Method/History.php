<?php
namespace GDO\Payment\Method;

use GDO\Payment\GDO_Order;
use GDO\Table\MethodQueryList;
use GDO\User\GDO_User;
/**
 * Table of orders for staff.
 * @author gizmore
 * @version 5.0
 */
final class History extends MethodQueryList
{
	public function isUserRequired() : bool { return true; }
	
	public function gdoTable() { return GDO_Order::table(); }
	
	public function getQuery()
	{
		return GDO_Order::table()->select()->where('order_by='.GDO_User::current()->getID());
	}
}
