<?php
namespace GDO\Payment\Method;

use GDO\Core\Method;
use GDO\Core\GDO;
use GDO\Language\Trans;
use GDO\Payment\GDO_Order;
use GDO\Payment\Orderable;
use GDO\Payment\PaymentModule;
use GDO\User\GDO_User;
use GDO\Util\Common;
use GDO\Core\ModuleLoader;
use GDO\Core\GDT_Serialize;
use GDO\Session\GDO_Session;
use GDO\Address\GDO_Address;
use GDO\Util\Strings;
use GDO\Core\Website;
use GDO\Address\GDT_Address;

/**
 * Your article has been selected.
 * Step 1 – Choose a payment processor
 * @author gizmore
 */
final class Choose extends Method
{
	public function showInSitemap() { return false; }
	
	public function isTrivial() { return false; }
	
	/**
	 * @var GDO_User
	 */
	private $user;
	
	/**
	 * @var Orderable
	 */
	private $orderable;
	
	/**
	 * @var PaymentModule
	 */
	private $paymentModule;
	
	/**
	 * @var GDO_Order
	 */
	private $order;

	/**
	 * @var GDO_Address
	 */
	private $address;
	
	/**
	 * @return Orderable|GDO
	 */
	public function getOrderable()
	{
		return GDO_Session::get('gdo_orderable');
	}
	
	public function gdoParameters() : array
	{
	    return [
	        GDT_Address::make('order_address')->onlyOwn()->notNull(),
	    ];
	}
	
	public function onInit() : void
	{
		$this->address = $this->gdoParameterValue('order_address');
		
		foreach (array_keys($_REQUEST) as $k)
		{
			if (str_starts_with($k, 'buy_'))
			{
				$_REQUEST['payment'] = Strings::substrFrom($k, 'buy_');
			}
		}
	}
	
	public function execute()
	{
		$moduleName = Common::getRequestString('payment');
		if (!($this->paymentModule = ModuleLoader::instance()->getModule($moduleName)))
		{
			return $this->error('err_module', [html($moduleName)]);
		}
		
		if (Common::getFormString('order_module'))
		{
			if (GDO_Session::get('gdo_order'))
			{
				return Website::redirect(href($this->paymentModule->getName(), 'InitPayment'));
			}
		}

		$this->user = GDO_User::current();
		
		if (!($this->orderable = $this->getOrderable()))
		{
			return $this->error('err_orderable');
		}
		
		$this->order = GDO_Order::blank([
			'order_title_en' => $this->orderable->getOrderTitle('en'),
			'order_title' => $this->orderable->getOrderTitle(Trans::$ISO),
			'order_price' => $this->paymentModule->getPrice($this->orderable, $this->address),
			'order_price_tax' => $this->paymentModule->getTax($this->orderable, $this->address),
			'order_item' => GDT_Serialize::serialize($this->orderable),
			'order_address' => $this->address->getID(),
			'order_module' => $this->paymentModule->getID(),
		]);

		GDO_Session::set('gdo_order', $this->order);
		
		$tVars = [
			'user' => $this->user,
			'orderable' => $this->orderable,
			'payment' => $this->paymentModule,
			'order' => $this->order,
		];
		
		return $this->templatePHP('choose.php', $tVars);
	}
	
}
