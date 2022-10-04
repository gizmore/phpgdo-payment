<?php
namespace GDO\Payment;

use GDO\Core\GDT_Decimal;

/**
 * A money column.
 * @author gizmore
 */
class GDT_Money extends GDT_Decimal
{
	public static $CURR = '€';
	public static $CURRENCY = 'EUR';
	public static $CURR_DIGITS = 2;
	
	public string $icon = 'money';

	public int $digitsBefore = 13;
	public int $digitsAfter = 4;
	
	public function defaultLabel() : self { return $this->label('price'); }
	
	public function renderHTML() : string
	{
		return self::renderPrice($this->getValue());
	}
	
	public static function renderPrice(float $price) : string
	{
		return (!$price)  ? '---' :
			sprintf('%s%.0'.self::$CURR_DIGITS.'f',
				self::$CURR,
				round($price, self::$CURR_DIGITS));
	}
	
}
