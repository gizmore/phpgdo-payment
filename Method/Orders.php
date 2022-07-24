<?php
namespace GDO\Payment\Method;

use GDO\Payment\GDO_Order;
use GDO\Table\MethodQueryTable;
use GDO\UI\GDT_EditButton;
use GDO\Address\GDO_Address;
use GDO\Core\GDO;

/**
 * Table of orders for staff.
 * 
 * @author gizmore
 * @version 6.10
 * @since 5.0
 */
final class Orders extends MethodQueryTable
{
	public function getPermission() : ?string { return 'staff'; }

	public function gdoTable() : GDO
	{
	    return GDO_Order::table();
	}
	
	public function getQuery()
	{
		return $this->gdoTable()->select()->joinObject('order_address', 'LEFT JOIN');
	}
	
	public function gdoHeaders() : array
	{
		$gdo = GDO_Order::table();
		$add = GDO_Address::table();
		return [
		    GDT_EditButton::make(),
		    $gdo->gdoColumn('order_id'),
		    $gdo->gdoColumn('order_num')->label('order_num_short'),
		    $add->gdoColumn('address_vat'),
		    $add->gdoColumn('address_company'),
		    $add->gdoColumn('address_name'),
		    $gdo->gdoColumn('order_by'),
		    $gdo->gdoColumn('order_at'),
		    $gdo->gdoColumn('order_title'),
		    $gdo->gdoColumn('order_price'),
		    $gdo->gdoColumn('order_paid'),
		    $gdo->gdoColumn('order_executed'),
		];
	}
   
}
