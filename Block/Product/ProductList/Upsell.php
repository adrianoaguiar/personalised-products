<?php

namespace Richdynamix\PersonalisedProducts\Block\Product\ProductList;

use \Magento\Framework\View\Element\Template;
use \Magento\Catalog\Block\Product\Context as Context;
use \Magento\Checkout\Model\ResourceModel\Cart as Cart;
use \Magento\Catalog\Model\Product\Visibility as Visibility;
use \Magento\Checkout\Model\Session as Session;
use \Magento\Framework\Module\Manager as Manager;
use Richdynamix\PersonalisedProducts\Helper\Config as Config;
use \Magento\Catalog\Model\ProductFactory as ProductFactory;
use \Magento\Customer\Model\Session as CustomerSession;
use \Richdynamix\PersonalisedProducts\Model\Frontend\Catalog\Product\ProductList\Upsell as PersonalisedUpsell;

/**
 * Rewrite product upsell block to switch out product collection
 * for one returned from PredictionIO
 *
 * @category    Richdynamix
 * @package     PersonalisedProducts
 * @author 		Steven Richardson (steven@richdynamix.com) @mage_gizmo
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Upsell extends \Magento\Catalog\Block\Product\ProductList\Upsell
{
    protected $_config;

    protected $_productFactory;

    protected $_upsell;

    public function __construct(
        Context $context,
        Cart $checkoutCart,
        Visibility $productVisibility,
        Session $checkoutSession,
        Manager $moduleManager,
        ProductFactory $productFactory,
        Config $config,
        PersonalisedUpsell $upsell,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->_config = $config;
        $this->_productFactory = $productFactory;
        $this->_upsell = $upsell;
        $this->_customerSession = $customerSession;
        parent::__construct(
            $context,
            $checkoutCart,
            $productVisibility,
            $checkoutSession,
            $moduleManager,
            $data
        );
    }

    /**
     * @return $this
     */
    protected function _prepareData()
    {

        if (!$this->_config->isEnabled() && $this->_customerSession->isLoggedIn()) {
            return parent::_prepareData();
        }

//        $personalisedIds = $this->_upsell->getProductCollection($this->_customerSession->getCustomerId());
//
//        if ($personalisedIds[''])

        $product = $this->_coreRegistry->registry('product');

        $collection = $this->_productFactory->create()->getCollection();

        // todo filter collection from predictionio results
        $collection->addAttributeToFilter('entity_id', ['in', ['6', '7']]);

        $this->_itemCollection = $collection;

        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }

        $this->_eventManager->dispatch(
            'catalog_product_upsell',
            ['product' => $product, 'collection' => $this->_itemCollection, 'limit' => null]
        );

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

}