<?php
namespace GDO\Payment;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_ObjectSelect;

class GDT_PaymentModule extends GDT_ObjectSelect
{

	protected function __construct()
	{
		$this->table(GDO_Module::table());
	}

	public function gdtDefaultLabel(): ?string
    { return 'payment'; }

	protected function getChoices(): array
	{
		return PaymentModule::allPaymentModules();
	}

}
