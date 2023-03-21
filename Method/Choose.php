<?php
namespace GDO\Payment\Method;

use GDO\Address\GDO_Address;
use GDO\Address\GDT_Address;
use GDO\Core\GDO;
use GDO\Core\GDT_Serialize;
use GDO\Core\Method;
use GDO\Core\ModuleLoader;
use GDO\Language\Trans;
use GDO\Payment\GDO_Order;
use GDO\Payment\Orderable;
use GDO\Payment\PaymentModule;
use GDO\Session\GDO_Session;
use GDO\User\GDO_User;
use GDO\Util\Strings;

/**
 * Your article has been selected.
 * Step 1 – Choose a payment processor
 *
 * @author gizmore
 */
final class Choose extends Method
{

	private GDO_User $user;

	private Orderable $orderable;

	private PaymentModule $paymentModule;

	private GDO_Order $order;

	private GDO_Address $address;

	public function isShownInSitemap(): bool { return false; }

	public function isTrivial(): bool { return false; }

	public function gdoParameters(): array
	{
		return [
			GDT_Address::make('order_address')->onlyOwn()->notNull(),
		];
	}

	public function onMethodInit(): void
	{
		$this->address = $this->gdoParameterValue('order_address');

		foreach (array_keys($this->getInputs()) as $k)
		{
			if (str_starts_with($k, 'buy_'))
			{
				$this->inputs['payment'] = Strings::substrFrom($k, 'buy_');
			}
		}
	}

	public function execute()
	{
		$moduleName = $this->inputs['payment'];
		if (!($this->paymentModule = ModuleLoader::instance()->getModule($moduleName)))
		{
			return $this->error('err_module', [html($moduleName)]);
		}

		if (isset($this->inputs['order_module']))
		{
			if (GDO_Session::get('gdo_order'))
			{
				return $this->redirect(href($this->paymentModule->getName(), 'InitPayment'));
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

	/**
	 * @return Orderable|GDO
	 */
	public function getOrderable(): Orderable
	{
		return GDO_Session::get('gdo_orderable');
	}

}
