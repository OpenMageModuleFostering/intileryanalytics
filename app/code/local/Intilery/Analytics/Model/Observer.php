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
				'price' => $product->getPrice(),
				'sku' => $product->getSku(),
				'image' => $product->getMediaConfig()->getMediaUrl($product->getData('image')),
				'description' => $product->getDescription(),
				'category' => implode(', ', $categories),
				'categoryIds' => $product->getCategoryIds()
			)
		);		

	}
	
	public function logCategoryView(Varien_Event_Observer $observer) {
	
		# Set intilery action
		Mage::getSingleton('core/session')->setData('intileryTagType', 'CategoryView');
		
		# Get the product		
		$category = $observer->getCategory();
				
		# Store in the session
		Mage::getSingleton('core/session')->setData('categoryViewData', array(
				'name' => $category->getName()
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
	
		# Get product
		$quoteItem = $observer->getItem();
    		$quantity = $quoteItem->getQty();
   		$product = $quoteItem->getProduct();
		
		# Valid product?
		if(!$product->getId()) {
			return;
		}
		
		# Store in the session
		Mage::getSingleton('core/session')->setData('productUpdateShoppingCart', array(
               		 	'id' => $product->getId(),
                		'name' => $product->getName(),
                		'quantity' => $quantity,
                		'price' => $product->getPrice(),
				'sku' => $product->getSku()
			)
		);
		
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
		
		# Store in the session
		Mage::getSingleton('core/session')->setData('productInShoppingCart', array(
                		'id' => $product->getId(),
                		'quantity' => Mage::app()->getRequest()->getParam('qty', 1),
                		'name' => $product->getName(),
                		'price' => $product->getPrice(),
				'sku' => $product->getSku(),
				'category_name' => Mage::getModel('catalog/category')->load($categories[0])->getName(),
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
                		'price' => $product->getPrice(),
				'sku' => $product->getSku()
			)
		);
		
		# Set intilery action
		Mage::getSingleton('core/session')->setData('intileryTagType', 'RemoveCart');
		
	}
	
}

?>