<?php
/**
 * PHP script to place order programmatically
 * @author NeoSOFT Team
 **/

# Path to Magento Bootstrap file
require_once('/var/www/html/MageFresh/app/bootstrap.php');
use \Magento\Framework\App\Bootstrap;
$result;
$orderData = $_POST;
$indexStart = 1;
$indexEnd = $_POST["index"];

# Generate array from order data
$shippingAddress = [
	'shipping_address' =>[
            'firstname'    => $orderData["firstname"], //address Details
            'lastname'     => $orderData["lastname"],
            'street' => $orderData["street1"],
            'city' => $orderData["city"],
            'country_id' => $orderData["country"],
            'region' => $orderData["region"],
            'postcode' => $orderData["postcode"],
            'telephone' => $orderData["telephone"],
            'save_in_address_book' => $orderData["saveinaddress"]
        ]
    ];
	# Turn on "display errors" property
    ini_set('display_errors',1);
    try{
		# Required variables declaration
    	$bootstrap = Bootstrap::create(BP, $_SERVER);
    	$objectManager = $bootstrap->getObjectManager();
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$state = $objectManager->get("\Magento\Framework\App\State");
    	$state->setAreaCode('frontend');
    	$storeManager = $objectManager->get("\Magento\Store\Model\StoreManagerInterface");
    	$customerFactory = $objectManager->get("\Magento\Customer\Model\CustomerFactory");
    	$product = $objectManager->get("\Magento\Catalog\Model\Product");
    	$cartRepositoryInterface = $objectManager->get("\Magento\Quote\Api\CartRepositoryInterface");
    	$cartManagementInterface = $objectManager->get("\Magento\Quote\Api\CartManagementInterface");
    	$customerRepository = $objectManager->get("\Magento\Customer\Api\CustomerRepositoryInterface");
    	$order = $objectManager->get("\Magento\Sales\Model\Order");

    	try{
			# Get website ID
    		$websiteId  = $storeManager->getWebsite()->getWebsiteId();
    		$store=$storeManager->getStore();

    		$customer=$customerFactory->create();
    		$customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']);						  # load customer by email address
        if(!$customer->getEntityId()){
            
            # If not avilable then create this customer 
        	$customer->setWebsiteId($websiteId)
        	->setStore($store)
        	->setFirstname($orderData['firstname'])
        	->setLastname($orderData['lastname'])
        	->setEmail($orderData['email']) 
        	->setPassword($orderData['email']);
        	$customer->save();
        }

        $cartId = $cartManagementInterface->createEmptyCart(); 				   # Create empty cart
        $quote = $cartRepositoryInterface->get($cartId); 					   # load empty cart quote
        $quote->setStore($store);
        
        # if you have allready buyer id then you can load customer directly 
        $customer= $customerRepository->getById($customer->getEntityId());
        $quote->setCurrency();
        $quote->assignCustomer($customer); //Assign quote to customer

        # add items in quote
        for ($i=1; $i <=$indexEnd ; $i++) { 
            $product=$product->load($orderData['productid'.$i]);
            $product->setPrice($product->getPrice());
            $quote->addProduct($product, intval($orderData['productqty'.$i]));
        }

        # Set Address to quote
        $quote->getBillingAddress()->addData($shippingAddress['shipping_address']);
        $quote->getShippingAddress()->addData($shippingAddress['shipping_address']);

        # Collect Rates and Set Shipping & Payment Method
        $shippingAddress=$quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
        ->collectShippingRates()
                        ->setShippingMethod($orderData["shippingmethod"]); 		# shipping method
        $quote->setPaymentMethod($orderData["paymentmethod"]); 					# payment method
        $quote->setInventoryProcessed(false); 									# not effetc inventory

        # Set Sales Order Payment
        $quote->getPayment()->importData(['method' => $orderData["paymentmethod"]]);
        $quote->save(); 														# Now Save quote and your quote is ready

        # Collect Totals
        $quote->collectTotals();

        # Create Order From Quote
        $quote = $cartRepositoryInterface->get($quote->getId());
        $orderId = $cartManagementInterface->placeOrder($quote->getId());
        $order = $order->load($orderId);
        
        $order->setEmailSent(0);
        $increment_id = $order->getRealOrderId();
        if($order->getEntityId()){
        	$result['order_id']= $order->getRealOrderId();
        	echo json_encode(["result"=>"success","order_id"=>$result["order_id"]]);exit;
        }else{
        	echo json_encode(["result"=>"error"]);exit;
        }


    }
    catch (Exception $e) {
    	echo json_encode(["result"=>$e->getMessage()]);exit;
    }


}
catch(Exception $e){
	echo json_encode(["result"=>$e->getMessage()]);exit;
}