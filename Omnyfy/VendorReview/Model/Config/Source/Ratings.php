<?php


namespace Omnyfy\VendorReview\Model\Config\Source;


class Ratings implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Omnyfy\VendorReview\Model\ResourceModel\Rating\CollectionFactory
     */
    private $ratingCollection;

    public function __construct(
        \Omnyfy\VendorReview\Model\ResourceModel\Rating\CollectionFactory $ratingCollection
    )
    {
        $this->ratingCollection = $ratingCollection;
    }

    public function toOptionArray()
    {
        $options = [];
        $ratingCollection = $this->ratingCollection->create();
        $ratingCollection->setActiveFilter()->setPositionOrder();
        foreach ($ratingCollection as $rating){
            $options[] = [
                'value' => $rating->getId(),
                'label' => $rating->getVendorRatingCode()
            ];
        }
        return $options;
    }

}