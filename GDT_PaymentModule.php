<?php
namespace GDO\Payment;

use GDO\Core\GDT_ObjectSelect;
use GDO\Core\GDO_Module;

class GDT_PaymentModule extends GDT_ObjectSelect
{
	public function defaultLabel() : self { return $this->label('payment'); }
	
	protected function __construct()
	{
		$this->table(GDO_Module::table());
	}
	
	public function getChoices()
	{
		return  PaymentModule::allPaymentModules();
	}
	
}
