<?php

class Intilery_Analytics_Model_Observer {

	public function logProductView(Varien_Event_Observer $observer) {
	
		# Set intilery action
		Mage::getSingleton('core/session')->setData('intileryTagType', 'ProductView');
		
		# Get the product		
		$product = $observer->getProduct();
		
		# Get the categories
		$categories = array();
		$categoryCollection = $product->getCategoryIds();
		
		# Get all categories
		foreach($categoryCollection as $categoryID) {
		
			# Load category model
			$_category = Mage::getModel('catalog/category');
			$_category->load($categoryID);
     			
			# Get the category name
			$categories[] = $_category->getName();
			
			# Clear up
			unset($_category);
			
		}
		
		# Store in the session
		Mage::getSingleton('core/session')->setData('productViewData', array(
				'id' => $product->getId(),
				'name' => $product->getName(),
				'price' => Mage::helper('core')->currency($product->getPrice(), true, false),
				'rrpPrice' => Mage::helper('core')->currency($product->getMsrp(), true, false),
				'sku' => $product->getSku(),
				'image' => Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage()),
				'description' => $product->getDescription(),
				'category' => implode(', ', $categories),
				'categoryIds' => implode(', ', $product->getCategoryIds()),
				'language' => Mage::app()->getLocale()->getLocaleCode(),
				'url' => $product->getProductUrl(),
				'brandName' => $product->getAttributeText('manufacturer'),
				'brandID' => $product->getManufacturer()
			)
		);		
	
	}

	public function logSearch(Varien_Event_Observer $observer) {
	
		# Set intilery action
		Mage::getSingleton('core/session')->setData('intileryTagType', 'Search');
		
		# Get the event		
		$event = $observer->getEvent();
		$data = $event->getDataObject();
		
		# Log data
		Mage::getSingleton('core/session')->setData('searchQuery', $data->getQueryText());
		
	}

	public function logCustomerLogout(Varien_Event_Observer $observer) {
	
		# Set intilery action
		Mage::getSingleton('core/session')->setData('intileryTagType', 'CustomerLogout');
	
	}
	
	public function logCustomerLogin(Varien_Event_Observer $observer) {
		
		# Get customer data
		$customer = $observer->getEvent()->getCustomer();
		
		# Invalid customer?
		if(!$customer->getId()) {
			return;
		}
		
		# Set intilery action
		Mage::getSingleton('core/session')->setData('intileryTagType', 'CustomerLogin');
		
		# Store in the session
		Mage::getSingleton('core/session')->setData('customerLogin', array(
				'email' => $customer->getEmail()
			)	
		);
	
	}

	public function logCustomerRegister(Varien_Event_Observer $observer) {
		
		# Set intilery action
		Mage::getSingleton('core/session')->setData('intileryTagType', 'CustomerRegister');
		Mage::getSingleton('core/session')->setData('intileryCustomerRegister', 'CustomerRegister');
		
		# Get customer data
		$customer = $observer->getEvent()->getCustomer();
		
		# Invalid customer?
		if(!$customer->getId()) {
			return;
		}
		
		# Load up the subscriber if possible
		$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
 
 		# Change boolean
		if(!$subscriber->getId()) {
			$isNewsletter = false;
		} else {
			$isNewsletter = true;
		}
		
		# Store in the session
		Mage::getSingleton('core/session')->setData('intileryCustomerData', array(
				'email' => $customer->getEmail(),
				'forename' => $customer->getFirstname(),
				'surname' => $customer->getLastname(),
				'name' => $customer->getName(),
				'id' => $customer->getId(),
				'subscribe' => $isNewsletter
			)
		);
		
		# Log data
		Mage::log(json_encode(Mage::getSingleton('core/session')->getData('intileryCustomerData')));
	
	}
	
	public function logCartUpdate(Varien_Event_Observer $observer) {

		$newQty = $observer->getEvent()->getInfo();
		$newQty = array_values($newQty);

		$cart = Mage::getModel('checkout/cart')->getQuote();
		$updateCartArray = array();

		foreach ($cart->getAllItems() as $key=>$item) {

			if($newQty[$key]['qty'] != $item->getQty()){

				$updateCartArray[] = array(
					'id' => $item->getProduct()->getId(),
					'quantity' => $newQty[$key]['qty'],
					'price' => Mage::helper('core')->currency($item->getPrice(), true, false)
				);

			}
		}

		# Store in the session
		Mage::getSingleton('core/session')->setData('productUpdateShoppingCart', $updateCartArray);

		# Set intilery action
		Mage::getSingleton('core/session')->setData('intileryTagType', 'UpdateCart');

	}
	
	public function logCartAdd(Varien_Event_Observer $observer) { 
	
		# Get the product added		
		$product = Mage::getModel('catalog/product')->load(
			Mage::app()->getRequest()->getParam('product', 0)
		);
		
		# Valid product?
		if(!$product->getId()) {
			return;
		}
		
		# Get any categories
		$categories = $product->getCategoryIds();

		#GET CURRENCY CODE
		$currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();

		# Store in the session
		Mage::getSingleton('core/session')->setData('productInShoppingCart', array(
				'id' => $product->getId(),
				'quantity' => Mage::app()->getRequest()->getParam('qty', 1),
				'name' => $product->getName(),
				'price' => Mage::helper('core')->currency($product->getPrice(), true, false),
				'sku' => $product->getSku(),
				'language' => Mage::app()->getLocale()->getLocaleCode(),
				'category_name' => Mage::getModel('catalog/category')->load($categories[0])->getName(),
				'url' => $product->getProductUrl()
			)
		);
		
		# Set intilery action
		Mage::getSingleton('core/session')->setData('intileryTagType', 'AddCart');
		
	}
	
	public function logCartRemove(Varien_Event_Observer $observer) {
	
		# Get the product
		$product = $observer->getQuoteItem()->getProduct();
		
		# Valid product?
		if(!$product->getId()) {
			return;
		}
		
		# Store in the session
		Mage::getSingleton('core/session')->setData('productOutShoppingCart', array(
                		'id' => $product->getId(),
                		'name' => $product->getName(),
                		'price' => Mage::helper('core')->currency($product->getPrice(), true, false),
						'sku' => $product->getSku(),
						'qty'=> $observer->getQuoteItem()->getQty(),
						'language'=> Mage::app()->getLocale()->getLocaleCode(),
						'url' => $product->getProductUrl()
			)
		);
		
		# Set intilery action
		Mage::getSingleton('core/session')->setData('intileryTagType', 'RemoveCart');
		
	}

	public function logCartSave(Varien_Event_Observer $observer){

		//USE THIS FUNCTION TO CHECK IF CART HAS BEEN EMPTIED

		$cart = Mage::getModel('checkout/cart')->getQuote();
		$updateCartArray = array();

		if(sizeof($cart->getAllItems()) < 1){
			//CART EMPTIED
			# Set intilery action
			Mage::getSingleton('core/session')->setData('intileryTagType', 'EmptiedCart');

		}
	}

	public function logOrderPlacedAfter(Varien_Event_Observer $observer){

		$orderObj = $observer->getEvent()->getData('order');
		$orderNum = $orderObj->getId();

		//GET ITEMS WITHIN ORDER
		$order = $observer->getEvent()->getOrder();

		//GET STATE
		$regionModel = Mage::getSingleton('directory/region')->load($orderObj->getShippingAddress()->getRegionId());
		$sstate_code = $regionModel->getCode();

		if($order->getId()){
			$ProdustIds = array();

			foreach ($order->getAllVisibleItems() as $key=>$item)
			{

				$product = $item->getProduct();

				# Get the categories
				$categories = array();
				$categoryCollection = $product->getCategoryIds();

				# Get all categories
				foreach($categoryCollection as $categoryID) {

					# Load category model
					$_category = Mage::getModel('catalog/category')->load($categoryID);

					# Get the category name
					$categories[] = $_category->getName();

					# Clear up
					unset($_category);
				}

				$ProdustIds[$key]['ID'] = $item->getProductID();
				$ProdustIds[$key]['name'] = $item->getName();
				$ProdustIds[$key]['sku'] = $item->getSku();
				$ProdustIds[$key]['qty'] = $item->getQtyOrdered();
				$ProdustIds[$key]['price'] = $item->getPrice();
				$ProdustIds[$key]['cat'] = implode(", ", $categories);
			}
		}

		//Set intilery action
		Mage::getSingleton('core/session')->setData('intileryTagType', 'orderAfter');

		# Store in the session
		Mage::getSingleton('core/session')->setData('orderAfterData', array(
				'orderNum' => $orderNum,
				'storeName' => Mage::app()->getStore()->getName(),
				'grandTotal' => $orderObj->getBaseGrandTotal(),
				'shippingCity' => $orderObj->getShippingAddress()->getCity(),
				'state' => $orderObj->getShippingAddress()->getRegion(),
				'country' => $orderObj->getShippingAddress()->getCountryId(),
				'currentCurrency' => Mage::app()->getStore()->getBaseCurrencyCode(),
				'items' => $ProdustIds,
				'tax' => $orderObj->getData('tax_amount'),
				'shipping' => $orderObj->getData('shipping_amount')
			)
		);
	}

}

?>