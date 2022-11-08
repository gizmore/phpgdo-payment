<?php
namespace GDO\Payment;

use GDO\Core\GDO;
use GDO\Core\GDT_AutoInc;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_CreatedBy;
use GDO\Date\GDT_DateTime;
use GDO\Date\Time;
use GDO\Core\GDT_Template;
use GDO\Core\GDT_Serialize;
use GDO\Core\GDT_String;
use GDO\User\GDO_User;
use GDO\Core\ModuleLoader;
use GDO\UI\GDT_Redirect;
use GDO\UI\GDT_Success;
use GDO\Core\GDT_Decimal;
use GDO\Core\GDT_UInt;
use GDO\Address\GDT_Address;
use GDO\Address\GDO_Address;
/**
 * Serializes an orderable.
 * Stores price and item description.
 * 
 * @author gizmore
 * @since 3.0
 * @version 6.10
 * 
 * @see Orderable
 * @see GDT_Money
 * @see GDO_Currency
 * @see PaymentModule
 */
final class GDO_Order extends GDO
{
	public function isTestable(): bool
	{
		return false;
	}
	
	public function gdoColumns() : array
	{
		return [
			GDT_AutoInc::make('order_id'),
			GDT_UInt::make('order_num')->notNull()->initial('0'),
			GDT_CreatedBy::make('order_by'),
			GDT_String::make('order_title'),
			GDT_String::make('order_title_en')->label('order_title'),
			GDT_PaymentModule::make('order_module')->writeable(false),
			GDT_String::make('order_xtoken')->ascii()->caseS()->max(64),
			GDT_Address::make('order_address'),
			GDT_CreatedAt::make('order_at'),
			GDT_Money::make('order_price'),
			GDT_Decimal::make('order_price_tax'),
			GDT_Money::make('order_price_paid')->label('order_price_paid'),
			GDT_DateTime::make('order_paid')->writeable(false)->label('paid_at'),
			GDT_DateTime::make('order_executed')->writeable(false)->label('executed_at'),
			GDT_Serialize::make('order_item'),
		];
	}
	
	public function href_edit() { return href('Payment', 'Order', '&id='.$this->getID()); }
	public function href_view() { return href('Payment', 'ViewOrder', '&id='.$this->getID()); }
	public function href_failure() { return $this->getOrderable()->getOrderCancelURL(GDO_User::current()); }
	public function href_success() { return $this->getOrderable()->getOrderSuccessURL(GDO_User::current()); }
	public function href_pdf() { return href('Payment', 'PDFBill', '&id='.$this->getID()); }

	public function redirectFailure() { return GDT_Redirect::toMessage($this->href_failure()); }
	public function redirectSuccess() { return GDT_Redirect::toMessage($this->href_success()); }
	
	/**
	 * @return GDO_Address
	 */
	public function getAddress() { return $this->gdoValue('order_address'); }
	public function getAddressId() { return $this->gdoVar('order_address'); }
	
	/**
	 * @return GDO_User
	 */
	public function getCreator() { return $this->gdoValue('order_by'); }
	public function getCreatorID() { return $this->gdoVar('order_by');  }
	
	public function isCreator(GDO_User $user) { return $this->getCreatorID() === $user->getID(); }
	
	public function getXToken() { return $this->gdoVar('order_xtoken'); }
	public function isPaid() { return $this->getPaid() !== null; }
	public function getPaid() { return $this->gdoVar('order_paid'); }
	public function getCreated() { return $this->gdoVar('order_at'); }
	public function getCreatedTime() { return Time::getTimestamp($this->getCreated()); }
	public function displayOrderedAt() { return Time::displayDate($this->getCreated(), 'day'); }
	public function getExecuted() { return $this->gdoVar('order_executed'); }
	public function displayExecutedAt() { return Time::displayDate($this->getExecuted(), 'day'); }
	public function isExecuted() { return $this->getExecuted() !== null; }

	public function getPayTime() { return Module_Payment::instance()->cfgPayTime(); }
	public function getPayMaxTime() { return $this->getCreatedTime() + $this->getPayTime(); }
	public function getPayMaxDate() { return Time::getDate($this->getPayMaxTime()); }
	public function displayPayMaxDate() { return Time::displayDate($this->getPayMaxDate(), 'day'); }

	/**
	 * @return GDO_User
	 */
	public function getUser()
	{
		return $this->getCreator();
	}
	
	/**
	 * @return Orderable
	 */
	public function getOrderable()
	{
		return $this->gdoValue('order_item');
	}
	
	/**
	 * @return PaymentModule
	 */
	public function getPaymentModule()
	{
		return ModuleLoader::instance()->getModuleByID($this->gdoVar('order_module'));
	}
	
	public function getPrice() { return $this->gdoVar('order_price'); }
	public function displayPrice() { return $this->gdoColumn('order_price')->renderHTML(); }
	public function displayPriceNetto() { return $this->displayMoney($this->getPriceNetto()); }
	public function getTitle() { return $this->gdoVar('order_title'); }
	public function getTitleEN() { return $this->gdoVar('order_title_en'); }
	
	public function getTax() { return $this->gdoValue('order_price_tax'); }
	public function getTaxFactor() { return $this->getTax() / 100.0; }
	public function getPriceBrutto() { return $this->getPrice(); }
	public function getPriceMWST() { return $this->getPriceBrutto() - $this->getPriceNetto(); }
	public function getPriceNetto() { return $this->getPriceBrutto() / (1.0 + $this->getTaxFactor()); }
	
	public function displayMoney($price)
	{
		return $this->gdoColumnCopy('order_price')->var($price)->renderHTML();
	}
	
	##############
	### Render ###
	##############
	public function renderCard() : string
	{
		return GDT_Template::php('Payment', 'card/order.php', ['gdo' => $this]);
	}
	
	public function renderPDF() : string
	{
		return GDT_Template::php('Payment', 'card/order_pdf.php', ['gdo' => $this]);
	}
	
	###############
	### Execute ###
	###############
	public function executeOrder()
	{
		# Exec Job
		$orderable = $this->getOrderable();
		$response = $orderable->onOrderPaid();

		# Update Order
		$this->saveVar('order_executed', Time::getDate());
		$this->saveValue('order_item', $orderable);
		$this->updateOrderNum();
		
		return GDT_Success::make()->text('msg_order_execute')->addField($response)->addField($this->redirectSuccess());
	}
	
	private function updateOrderNum()
	{
		$subselect = "SELECT ( MAX(order_num) + 1 ) FROM gdo_order";
		$this->updateQuery()->set("order_num = ( $subselect )")->exec();
	}

	##############
	### Static ###
	##############
	public static function getByToken(string $token): self
	{
		return self::getBy('order_xtoken', $token);
	}

}
