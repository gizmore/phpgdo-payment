<?php
namespace GDO\Payment;

use GDO\Language\GDO_Language;
use GDO\User\GDO_User;
use GDO\Mail\Mail;
use GDO\Language\Trans;

/**
 * Billing mail functionality.
 * 
 * @see PaymentPDF
 * @author gizmore
 * @version 6.10
 * @since 6.10
 */
final class BillingMails
{
	################
	### Pre Bill ###
	################
	public static function sendBillMail(GDO_Order $order)
	{
		$user = $order->getCreator();
		GDO_Language::withIso($user->getLangISO(), function() use ($user, $order) {
			$o = $order->getOrderable();
			$mail = self::getMail($user);
			$mail->setSubject(t('mail_subj_payment_bill', [sitename(), $o->getOrderTitle(Trans::$ISO)]));
			$tVars = array(
				$user->renderUserName(),
				sitename(),
				$o->getOrderTitle(Trans::$ISO),
				$order->displayPrice(),
				$order->getPaymentModule()->displayPaymentMethodName(),
			);
			$mail->setBody(t('mail_body_payment_bill', $tVars));
			$pdf = PaymentPDF::generate($user, $order);
			$mail->addAttachmentFile($pdf->displayName(), $pdf->getPath());
			$mail->sendToUser($user);
		});
	}
	
	#################
	### Bill paid ###
	#################
	public static function sendBillPaidMails(GDO_Order $order)
	{
		$module = Module_Payment::instance();
		# To Staff
		if ($to = $module->cfgMailTo())
		{
			self::sendBillPaidMailSingle($to, $order);
		}
		else
		{
			foreach (GDO_User::admins() as $admin)
			{
				self::sendBillPaidMailAdmin($admin, $order);
			}
		}
		
		# To user
		self::sendBillPaidMail($order->getCreator(), $order);
	}
	
	private static function sendBillPaidMailSingle($to, GDO_Order $order)
	{
		self::sendBillPaidMailAdmin(self::getMailToUser($to), $order);
	}
	
	private static function sendBillPaidMailAdmin(GDO_User $user, GDO_Order $order)
	{
		GDO_Language::withIso($user->getLangISO(), function() use ($user, $order) {
			$mail = self::getMail($user);
			$creator = $order->getCreator();
			$mail->setSubject(tusr($user, 'mail_subj_payment_bill_paid_staff', [$creator->displayName(), $order->getID()]));
			$tVars = array(
				$user->renderUserName(),
				$creator->displayName(),
				$order->getOrderable()->getOrderTitle($user->getLangISO()),
				$order->displayPrice(),
				$order->getPaymentModule()->displayPaymentMethodName(),
			);
			$mail->setBody(tusr($user, 'mail_body_payment_bill_paid_staff', $tVars));
			$pdf = PaymentPDF::generate($user, $order);
			$mail->addAttachmentFile($pdf->displayName(), $pdf->getPath());
			$mail->sendToUser($user);
		});
	}
	
	private static function sendBillPaidMail(GDO_User $user, GDO_Order $order)
	{
		GDO_Language::withIso($user->getLangISO(), function() use ($user, $order) {
			$mail = self::getMail($user);
			$mail->setSubject(t('mail_subj_payment_bill_paid', [sitename(), $order->getOrderable()->getOrderTitle($user->getLangISO())]));
			$tVars = array(
				$user->renderUserName(),
				$order->displayPrice(),
				sitename(),
			);
			$mail->setBody(t('mail_body_payment_bill_paid', $tVars));
			$pdf = PaymentPDF::generate($user, $order);
			$mail->addAttachmentFile($pdf->displayName(), $pdf->getPath());
			$mail->sendToUser($user);
		});
	}
	
	############
	### Util ###
	############
	private static function getMail(GDO_User $user)
	{
		$module = Module_Payment::instance();
		$mail = new Mail();
		$mail->setSender($module->cfgMailFrom());
		$mail->setSenderName(tusr($user, 'billing_mail_sender_name'));
		return $mail;
	}
	
	private static function getMailToUser($to)
	{
		$module = Module_Payment::instance();
		$iso = $module->cfgMailLanguage();
		return GDO_User::blank(array(
			'user_name' => tiso($iso, 'billing_mail_receiver_name'),
			'user_email' => $to,
			'user_language' => $iso,
		));
	}
	
}
