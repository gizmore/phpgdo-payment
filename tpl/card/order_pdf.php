<?php
use GDO\Payment\GDO_Order;
/** @var $gdo GDO_Order **/
$gdo instanceof GDO_Order;
if (!function_exists('th'))
{
	function th($text, $width)
	{
		return sprintf('<th style="width: %s%%; height: 24px; line-height: 20px; border: 1px solid #333; background-color: #aaf;">%s</th>', $width, html($text));
	}
	function td($text, $align="left")
	{
		return sprintf('<td style="height: 24px; line-height: 22px; border: 1px solid #333; background-color: #fff; text-align: %s;">%s</td>', $align, $text);
	}
	function td2($text, $align="right")
	{
		return sprintf('<td style="height: 24px; line-height: 22px; border-right: 1px solid #333; background-color: #fff; text-align: %s;">%s</td>', $align, $text);
	}
	function b($text)
	{
		return sprintf('<b>%s</b>', $text);
	}
}
?>
<table>
  <tr>
    <?=th(t('article'), 70)?>
    <?=th(t('price'), 30)?>
  </tr>
  <tr>
    <?=td(html($gdo->getTitle()))?>
    <?=td($gdo->displayPriceNetto(), 'right')?>
  </tr>
  <tr>
    <?=td2(t('pdforder_sum_netto'))?>
    <?=td($gdo->displayMoney($gdo->getPriceNetto()), 'right')?>
  </tr>
  <tr>
    <?=td2(t('pdforder_sum_tax', [$gdo->getTax()]))?>
    <?=td($gdo->displayMoney($gdo->getPriceMWST()), 'right')?>
  </tr>
  <tr>
    <?=td2(b(t('pdforder_sum_brutto')))?>
    <?=td(b($gdo->displayMoney($gdo->getPriceBrutto())), 'right')?>
  </tr>
</table>
