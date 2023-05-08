<?php
namespace GDO\Payment\Method;

use GDO\Core\GDT_Object;
use GDO\Date\Time;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Payment\GDO_Order;
use GDO\Payment\Module_Payment;
use GDO\UI\GDT_Divider;
use GDO\UI\GDT_HTML;
use GDO\Util\Common;

/**
 * Edit an order. Staff method.
 *
 * @version 6.10.6
 * @since 6.3.1
 * @author gizmore
 */
final class Order extends MethodForm
{

	public function getPermission(): ?string { return 'staff'; }

	public function isTrivial(): bool { return false; }

	public function isShownInSitemap(): bool { return false; }

	public function gdoParameters(): array
	{
		return [
			GDT_Object::make('id')->table(GDO_Order::table())->notNull(),
		];
	}

	protected function createForm(GDT_Form $form): void
	{
		$order = $this->getOrder();
		$address = $order->getAddress();
		$form->addField(GDT_HTML::make()->var($order->getOrderable()->renderCard()));
		$form->addField(GDT_Divider::make()->label('div_order_section'));
		$form->addFields(...$order->gdoColumnsExcept('order_item', 'order_title'));
		$form->addFields(...$address->gdoColumnsExcept('address_id'));
		$form->addField(GDT_AntiCSRF::make());
		$form->actions()->addField(GDT_Submit::make('btn_edit')->onclick([$this, 'onEdit']));
		$form->actions()->addField(GDT_Submit::make('btn_execute')->disabled($order->isPaid())->onclick([$this, 'onExecute']));
	}

	/**
	 * @return GDO_Order
	 */
	public function getOrder()
	{
		return GDO_Order::table()->find(Common::getRequestString('id'));
	}

	public function onEdit()
	{
		$form = $this->getForm();
		$order = $this->getOrder();
		$order->saveVars($form->getFormVars());
		if ($address = $order->getAddress())
		{
			$address->saveVars($form->getFormVars());
		}
		$this->resetForm();
		return $this->message('msg_order_edited')->addField($this->renderPage());
	}

	public function onExecute()
	{
		$order = $this->getOrder();
		$order->saveVars([
			'order_paid' => Time::getDate(),
			'order_price_paid' => $order->getPrice(),
		]);
		Module_Payment::instance()->onExecuteOrder($order->getPaymentModule(), $order);
		return $this->message('msg_order_execute')->addField($this->renderPage());
	}

}
