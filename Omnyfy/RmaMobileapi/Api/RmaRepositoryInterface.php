<?php
namespace Omnyfy\RmaMobileapi\Api;

interface RmaRepositoryInterface
{
   /**
     * Returns RMA for order id to user
     *
     * @api
     * @param int $orderId Order ID.
     * @return \Omnyfy\Core\Api\Json
     */
    public function getByOrderId($orderId);

    /**
      * Returns RMA items for rma id
      *
      * @api
      * @param int $rmaId RMA Id.
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaItemsForRmaId($rmaId);

    /**
      * Returns RMA items for rma ids
      *
      * @api
      * @param string $rmaIds RMA Ids.
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaItemsForRmaIds($rmaIds);

    /**
      * Returns RMA reason list
      *
      * @api
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaReasonList();

    /**
      * Returns RMA condition list
      *
      * @api
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaConditionList();

    /**
      * Returns RMA status list
      *
      * @api
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaStatusList();

    /**
      * Returns RMA resolution list
      *
      * @api
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaResolutionList();

    /**
      * Returns RMA address list
      *
      * @api
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaAddressList();

    /**
      * Save RMA request
      *
      * @api
      * @param \Omnyfy\RmaMobileapi\Api\Data\RmaOrderDataInterface $data
      * @return \Omnyfy\Core\Api\Json
      */

    public function saveRma($data);


    /**
      * Returns Test string
      *
      * @api
      * @return \Omnyfy\Core\Api\Json
      */

    public function test();


    /**
     * Vendor Save RMA request
     *
     * @api
     * @param \Omnyfy\RmaMobileapi\Api\Data\RmaOrderDataInterface $data
     * @return \Omnyfy\Core\Api\Json
     */

    public function VendorSaveRma($data);


    /**
     * Returns RMA items for rma ids
     *
     * @api
     * @param string $rmaIds RMA Ids.
     * @return \Omnyfy\Core\Api\Json
     */

    public function getRmaItems($rmaIds);



}

