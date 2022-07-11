<?php
namespace GDO\Payment;

use GDO\TCPDF\GDOTCPDF;
use GDO\User\GDO_User;
use GDO\File\GDO_File;
use GDO\Address\Module_Address;
use GDO\Language\Trans;
use GDO\Language\GDO_Language;

class PaymentPDF extends GDOTCPDF
{
	private $iso = GDO_LANGUAGE;
	private $user;
	private $order;
	
	public function __construct($iso, GDO_User $user, GDO_Order $order)
	{
		parent::__construct('L', 'mm', 'A4', true, 'UTF-8', false, false);
		$this->iso = $iso;
		$this->user = $user;
		$this->order = $order;
	}
	
	public function getDefaultFilename()
	{
		$o = $this->order;
		$tVars = array(
			sitename(),
			$o->getID(),
			$o->getOrderable()->getOrderTitle($this->iso),
		);
		return tiso($this->iso, 'pdfbill_filename', $tVars);
	}
	
	public static function getCompanyTinyLine()
	{
		$mod = Module_Address::instance();
		$tVars = array(
			Module_Payment::instance()->cfgCompanyName(),
			$mod->cfgStreet(),
			$mod->cfgZIP(),
			$mod->cfgCity(),
		);
		return t('pdfbill_company_underline', $tVars);
	}
	
	/**
	 * 
	 * @param GDO_User $user
	 * @param GDO_Order $order
	 * @return GDO_File
	 */
	public static function generate(GDO_User $user, GDO_Order $order, $iso=null)
	{
		$iso = $iso ? $iso : Trans::$ISO;
		return GDO_Language::withIso($iso, function() use ($user, $order, $iso) {
			return self::generateB($user, $order, $iso);
		});
	}
	
	private static function generateB(GDO_User $user, GDO_Order $order, $iso)
	{
		$creator = $order->getCreator();
		
		$paymentModule = $order->getPaymentModule();
		$addressModule = Module_Address::instance();
		
		$pdf = new self($iso, $user, $order);

		$pdf->SetCreator('GDO6');
		$pdf->SetAuthor('GDO6 - TCPDF');
		$pdf->SetTitle($pdf->getDefaultFilename());
		$pdf->SetSubject(tiso($iso, 'pdf_subject_payment'));
		$pdf->SetKeywords(sprintf('%s, billing', sitename()));
		
		$pdf->title(sitename());
		$pdf->subtitle($order->isPaid() ? t('pdf_subtitle_paid') : t('pdf_subtitle_unpaid'));
		$pdf->footerHTML($paymentModule->getFooterHTML());
		
		$pdf->AddPage();
		
		$ourAddress = $addressModule->cfgAddress();
		$hisAddress = $addressModule->cfgUserAddress($creator);
		
		$pdf->MoveToOrigin();
		$y = $pdf->GetY();
		
// 		# Print his address and small signature.
		$pdf->SetY($y);
// 		$pdf->Ln(5.0);
		$pdf->smallParagraph($pdf->getCompanyTinyLine());
		$pdf->Ln(8.0);
		$pdf->Address($hisAddress, 'L', true);
		$y2 = $pdf->GetY() + 10.0;
		
		# Print our address and bill metadata
		$pdf->SetY($y);
		$pdf->Address($ourAddress, 'R', false);
		$pdf->paragraph(t('pdfbill_ordered_at', [$order->displayOrderedAt()]), 'R');
		if ($order->isExecuted())
		{
			$pdf->paragraph(t('pdfbill_executed_at', [$order->displayExecutedAt()]), 'R');
		}
		$pdf->paragraph(t('pdfbill_customer', [$creator->getID() + 1000]), 'R');
		
		$pdf->SetY($y2);

		# Title and billnr
		$pdf->largeParagraph($pdf->subtitle);
		$pdf->paragraph(t('pdfbill_number', [html($order->getXToken())]));
		$pdf->smallParagraph(t('pdfbill_number_hint'));
		
		# Line
		$pdf->Ln(5.0);
		$pdf->HR();
		$pdf->Ln(3.0);
		
		# Order Details
		$pdf->HTML($order->renderPDF());
		
		
		# Line
		$pdf->Ln(5.0);
		$pdf->HR();
		$pdf->Ln(3.0);
		
		# Last words
		$pdf->paragraph(t('pdfbill_pay_until', [$order->displayPayMaxDate()]));
		
		# Write file
		$path = $pdf->tempPath();
		$pdf->Output($path, 'F');
		return GDO_File::fromPath($pdf->getDefaultFilename(), $path);
	}
	
}
