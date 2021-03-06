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
	
	public function renderCell() : string
	{
		return self::renderPrice($this->getValue());
	}
	
	public static function renderPrice($price)
	{
		return $price === null ? '---' :
		  sprintf('%s%.0'.self::$CURR_DIGITS.'f', self::$CURR, $price);
	}
	
}
