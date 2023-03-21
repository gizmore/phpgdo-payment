<?php
namespace GDO\Payment\Method;

use Exception;
use GDO\Core\GDT_Object;
use GDO\Core\Method;
use GDO\Core\Website;
use GDO\Language\Trans;
use GDO\Net\Stream;
use GDO\Payment\GDO_Order;
use GDO\Payment\PaymentPDF;
use GDO\User\GDO_User;

/**
 * Download a PDF bill.
 *
 * @version 7.0.1
 * @since 6.10.0
 * @author gizmore
 * @see PaymentPDF
 */
final class PDFBill extends Method
{

	public function getMethodTitle(): string
	{
		return t('btn_pdf_bill');
	}

	public function gdoParameters(): array
	{
		return [
			GDT_Object::make('id')->notNull()->table(GDO_Order::table()),
		];
	}

	public function execute()
	{
		$order = $this->getOrder();

		$file = PaymentPDF::generate($order->getCreator(), $order, Trans::$ISO);

		if (Website::outputStarted())
		{
			return $this->error('err_cannot_stream_output_started');
		}

		Stream::serve($file, '', false);
	}

	public function getOrder(): GDO_Order
	{
		return $this->gdoParameterValue('id');
	}

	public function hasUserPermission(GDO_User $user)
	{
		if ($user->isStaff())
		{
			return true;
		}
		try
		{
			if ($order = $this->getOrder())
			{
				return $order->getCreator() === $user ? true : $this->error('err_order');
			}
		}
		catch (Exception $e)
		{
		}
		return $this->error('err_permission_required');
	}

}
