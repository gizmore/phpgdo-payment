<?php
namespace GDO\Payment\Method;

use GDO\Core\GDO;
use GDO\DB\Query;
use GDO\Payment\GDO_Order;
use GDO\Table\MethodQueryList;
use GDO\User\GDO_User;

/**
 * Table of orders for staff.
 *
 * @version 5.0
 * @author gizmore
 */
final class History extends MethodQueryList
{

	public function isUserRequired(): bool { return true; }

	public function gdoTable(): GDO { return GDO_Order::table(); }

	public function getQuery(): Query
	{
		return GDO_Order::table()->select()->where('order_by=' . GDO_User::current()->getID());
	}

}
