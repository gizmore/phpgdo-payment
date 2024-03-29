<?php
namespace GDO\Payment;

use GDO\Address\GDO_Address;
use GDO\Core\GDO_Module;
use GDO\Core\GDT_Decimal;
use GDO\Date\Time;
use GDO\Form\GDT_Submit;
use GDO\UI\GDT_Button;
use GDO\Util\Random;

abstract class PaymentModule extends GDO_Module
{

	/**
	 * @var PaymentModule[]
	 */
	public static array $paymentModules = [];
	private static $paymentModulesId = [];
	public int $priority = 25;

	/**
	 * @return PaymentModule[]
	 */
	public static function allPaymentModules() { return self::$paymentModules; }

	public static function allPaymentModuleIDs() { return self::$paymentModulesId; }

	public function onModuleInit(): void
	{
		self::$paymentModules[$this->getName()] = $this;
		self::$paymentModulesId[$this->getID()] = $this;
		parent::onModuleInit();
	}

	public function getConfig(): array
	{
		return [
			GDT_Decimal::make('fee_buy')->digits(1, 4)->initial('0.0000'),
		];
	}

	public function renderOption(): string
	{
		return $this->displayPaymentMethodName();
	}

	#			'order_price' => $this->paymentModule->getPrice($this->orderable->getOrderPrice(), $this->orderable->isPriceWithTax(), $this->address->getVAT()),

	public function displayPaymentMethodName()
	{
		return t('payment_' . strtolower($this->getName()));
	}

	public function getPrice(Orderable $orderable, GDO_Address $address)
	{
		$price = $orderable->getOrderPrice();
		$price = round(($this->cfgFeeBuy() + 1.00) * floatval($price), 2);
		if (!$orderable->isPriceWithTax())
		{
			if (!($address->getVAT() && Module_Payment::instance()->cfgVatNoTax()))
			{
				$mwst = Module_Payment::instance()->cfgTaxFactor();
				$price += $price * $mwst;
			}
		}
		return $price;
	}

	public function cfgFeeBuy() { return $this->getConfigValue('fee_buy'); }

	public function getTax(Orderable $orderable, GDO_Address $address)
	{
		$mp = Module_Payment::instance();
		$tax19 = $mp->cfgTax();
		if ($mp->cfgVatNoTax() && $address->getVAT())
		{
			return 0;
		}
		else
		{
			return $tax19;
		}
	}

	public function displayPaymentFee()
	{
		return sprintf('%.03f%%', $this->cfgFeeBuy());
	}

	/**
	 * @param string $href
	 *
	 * @return GDT_Button
	 */
	public function makePaymentButton(GDO_Order $order = null)
	{
		return GDT_Submit::make('buy_' . $this->getName())->icon('money');
	}

	public function renderOrderFragment(GDO_Order $order)
	{
		return '';
	}

	public function getFooterHTML(GDO_Order $order)
	{
		return $this->templatePHP('pdf_footer_html.php', ['order' => $order]);
	}

	/**
	 * Verwendungszweck / Transfer usage
	 *
	 * @param GDO_Order $order
	 *
	 * @return string
	 */
	public function getTransferPurpose(GDO_Order $order)
	{
		$year = Time::getYear($order->getCreated());
		return sprintf('%s-%s-%s%06d', sitename(), $year, Random::randomKey(4, Random::HEXLOWER), $order->getID());
	}

}
