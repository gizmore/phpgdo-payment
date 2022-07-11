<?php
use GDO\Payment\Module_Payment;
use GDO\Address\Module_Address;
use GDO\Payment\GDO_Order;
/**
 * @var GDO_Order $order
 */
$order instanceof GDO_Order;
$ma = Module_Address::instance();
$mp = Module_Payment::instance();
$a = $ma->cfgAddress();
?>
<table>
  <tr>
    <td><?=sitename()?></td>
    <td><?=$a->display('address_name')?></td>
    <td><?=sitename()?></td>
  </tr>
  <tr>
    <td><?=$a->display('address_street')?></td>
    <td><?=t('pdfbill_payment')?></td>
    <td><?=t('vat')?>: <?=$mp->cfgVat()?></td>
  </tr>
  <tr>
    <td><?=$a->display('address_zip').' '.$a->display('address_city')?></td>
    <td><?=$order->getPaymentModule()->displayPaymentMethodName()?></td>
    <td><?=t('financial_office') . $mp->cfgVatOffice()?></td>
  </tr>
</table>
