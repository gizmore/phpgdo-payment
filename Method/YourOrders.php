<?php
namespace GDO\Payment\Method;

use GDO\Core\GDO;
use GDO\Core\GDT;
use GDO\Core\GDT_Response;
use GDO\DB\Query;
use GDO\Payment\GDO_Order;
use GDO\Table\MethodQueryTable;
use GDO\UI\GDT_Bar;
use GDO\UI\GDT_Button;
use GDO\UI\GDT_Link;
use GDO\User\GDO_User;

final class YourOrders extends MethodQueryTable
{

	public function isUserRequired(): bool { return true; }

	public function gdoTable(): GDO
	{
		return GDO_Order::table();
	}

	public function getQuery(): Query
	{
		return GDO_Order::table()->select()->where('order_by=' . GDO_User::current()->getID());
	}

	public function gdoHeaders(): array
	{
		$gdo = GDO_Order::table();
		return [
// 			GDT_EditButton::make(),
			$gdo->gdoColumn('order_id'),
			GDT_Button::make('pdf')->label('btn_pdf_bill'),
			$gdo->gdoColumn('order_at'),
			$gdo->gdoColumn('order_title'),
			$gdo->gdoColumn('order_price'),
			$gdo->gdoColumn('order_paid'),
			$gdo->gdoColumn('order_executed'),
			GDT_Button::make('view'),
		];
	}

	public function execute(): GDT
	{
		return GDT_Response::makeWith(
			GDT_Bar::makeWith(
				GDT_Link::make('link_add_address')->href(href('Address', 'AddAddress')),
				GDT_Link::make('link_own_addresses')->href(href('Address', 'OwnAddresses')),

			)->horizontal()
		)->addField(parent::execute());
	}

}
