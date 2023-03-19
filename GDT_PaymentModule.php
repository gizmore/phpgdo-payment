<?php
namespace GDO\Payment;

use GDO\Core\GDT_ObjectSelect;
use GDO\Core\GDO_Module;

class GDT_PaymentModule extends GDT_ObjectSelect
{
	public function defaultLabel(): static { return $this->label('payment'); }
	
	protected function __construct()
	{
		$this->table(GDO_Module::table());
	}
	
	public function getChoices(): array
	{
		return  PaymentModule::allPaymentModules();
	}
	
}
