<?php

namespace OmnyfyCustomzation\CmsBlog\Api;

interface ManagementInterface
{
    /**
     * Create new item.
     *
     * @param string $data .
     * @return string.
     * @api
     */
    public function create($data);

    /**
     * Update item by id.
     *
     * @param int $id .
     * @param string $data .
     * @return string.
     * @api
     */
    public function update($id, $data);

    /**
     * Remove item by id.
     *
     * @param int $id .
     * @return bool.
     * @api
     */
    public function delete($id);
}
