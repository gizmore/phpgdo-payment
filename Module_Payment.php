<?php
namespace GDO\Payment;

use GDO\Core\GDO_Module;
use GDO\UI\GDT_Link;
use GDO\UI\GDT_Page;
use GDO\Date\Time;
use GDO\Core\GDT_Decimal;
use GDO\Core\GDT_String;
use GDO\Mail\GDT_Email;
use GDO\Language\GDT_Language;
use GDO\Address\Module_Address;
use GDO\UI\GDT_Divider;
use GDO\Date\GDT_Duration;
use GDO\Core\GDT_Checkbox;
use GDO\User\GDO_User;
use GDO\UI\GDT_Menu;

/**
 * Base Payment module.
 * Comes with GDO_Order class and Orderable trait for easy making GDO purchasable.
 * Multiple payment provider modules are / will be available.
 * It is advised to use gdo6-session-db when using payment modules,
 * as orderables are stored in the session temporarily.
 * 
 * @author gizmore
 * @version 6.10.6
 * @since 6.3.0
 * 
 * @see Module_PaymentBank
 * @see Module_PaymentPaypal
 * @see Module_PaymentCredits
 */
final class Module_Payment extends GDO_Module
{
	public int $priority = 15;
	public function getDependencies() : array
	{
		return ['Address', 'TCPDF', 'Mail'];
	}

	public function href_administrate_module() : ?string { return href('Payment', 'Orders'); }
	
	public function getClasses() : array { return [GDO_Order::class]; }
	public function onLoadLanguage() : void { $this->loadLanguage('lang/payment'); }
	
	public function getConfig() : array
	{
		return [
			GDT_String::make('company_name')->initial(sitename()),
			GDT_Decimal::make('tax_mwst')->digits(3, 1)->initial("16.0"),
			GDT_String::make('vat')->max(24)->initial('0000000000'),
			GDT_String::make('vat_office')->initial(Module_Address::instance()->cfgCity()),
			GDT_Duration::make('pay_time')->initial('14d'),
			GDT_Divider::make('div_billing_mails'),
			GDT_Language::make('billing_mail_language')->notNull()->initial(GDO_LANGUAGE),
			GDT_Email::make('billing_mail_sender')->initial(GDO_BOT_EMAIL),
			GDT_Email::make('billing_mail_reciver'),
		    GDT_Checkbox::make('payment_feature_vat_no_tax')->initial('1'),
		    GDT_Checkbox::make('right_bar')->initial('1'),
		];
	}
	
	public function cfgCompanyName() { return $this->getConfigVar('company_name'); }
	public function cfgTax() { return $this->getConfigValue('tax_mwst'); }
	public function cfgTaxFactor() { return $this->cfgTax() / 100.0; }
	public function cfgVat() { return $this->getConfigVar('vat'); }
	public function cfgVatNoTax() { return $this->getConfigValue('payment_feature_vat_no_tax'); }
	public function cfgVatOffice() { return $this->getConfigVar('vat_office'); }
	public function cfgPayTime() { return $this->getConfigValue('pay_time'); }
	public function cfgMailLanguage() { return $this->getConfigVar('billing_mail_language'); }
	public function cfgMailTo() { return $this->getConfigVar('billing_mail_reciver'); }
	public function cfgMailFrom() { return $this->getConfigVar('billing_mail_sender'); }
	public function cfgRightBar() { return $this->getConfigValue('right_bar'); }
	
	public function onInitSidebar() : void
	{
	    if ($this->cfgRightBar())
	    {
	    	if (GDO_User::current()->isUser())
	    	{
		        $bar = GDT_Page::$INSTANCE->rightBar();
		        $menu = GDT_Menu::make('menu_payment');
		        $menu->label('payment')->vertical();
		        $menu->addField(GDT_Link::make('link_your_orders')->href(href('Payment', 'YourOrders')));
		        $bar->addField($menu);
	    	}
	    	if (GDO_User::current()->isStaff())
	    	{
	    		$page = GDT_Page::instance();
	    		$rb = $page->rightBar();
	    		$menu = $rb->getField('menu_admin');
	    		$menu->addField(GDT_Link::make('link_orders')->href(href('Payment', 'Orders')));
	    		
	    	}
	    }
	}
	
	public function onExecuteOrder(PaymentModule $module, GDO_Order $order)
	{
		$order->saveVars([
			'order_paid' => Time::getDate(),
		]);
		$order->executeOrder();
		BillingMails::sendBillPaidMails($order);
		return $this->message('msg_order_execute');
	}

	public function onPendingOrder(PaymentModule $module, GDO_Order $order)
	{
		return $this->error('err_order_pending');
	}
	
}
