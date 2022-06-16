# Omnyfy Webhook

The Webhook module is to dispatch events notification for different internal event inside of Omnyfy marketplace, for example “order created” event or “customer logged in” to a specific endpoint provider by external system.

## Installation

```
git clone git@bitbucket.org:omnyfyteam/omnyfy-webhook.git
```

## Module Configuration
Location: `Stores > Configuration > Omnyfy > Webhook`

#### General
* `Is Enable` - enable/disable the module
* `Enable Scheduled Delivery`
  * Yes: the event will be saved on `EventSchedule` table and will be dispatched through cron job
  * No: the event will be dispatched immediately
* `Event History Rotation` - the records from `EventHistory` table will be deleted after some days (can be set between 1-60)

#### Authentication
* `Authentication Type` - the type of authentication
  * `Basic` - will use username and password
  * `Bearer token` - will use bearer token

## Available Webhook Event Types
There are 8 webhook types:

### 1. order.created

Event data example:
```
{
    "event_id": "603e5070d7193",
    "event_type": "order.created",
    "created_utc": 1615139840,
    "data":
    {
        "entity_id": "1",
        "object": "order",
        "timestamp": "1611640026",
        "currency": "AUD",
        "total": "99.50",
        "discount": "0.99",
        "status": "pending",
        "payment_status": "paid",
        "fulfilment_status": "TBC",
        "items":
        [
            {
                "sku": "SKU001",
                "qty": 9,
                "vendor":
                {
                    "object": "vendor",
                    "entity_id": "1",
                    "name": "Vendor Name"
                }
            }
        ],
        "customer":
        {
            "entity_id": "1",
            "object": "customer",
            "email_address": "john.smith@gmail.com",
            "first_name": "John",
            "last_name": "Smith"
        },
        "shipping_address":
        {
            "entity_id": "1",
            "city": "Melbourne",
            "street":
            [
                "Road Street"
            ],
            "country_code": "AU",
            "postcode": "3000",
            "first_name": "John",
            "last_name": "Smith",
            "telephone": "8888-8888"
        }
    }
}
```

* `event_id` - unique identifier for the event
* `event_type` - type of webhook event
* `created_utc` - event creation time stamp as UTC
* `data` - object for event data
* `data.entity_id` - order id
* `data.timestamp` - timestamp of order creation time
* `data.status` - order status
* `data.payment_status` - order payment status (paid/unpaid)
* `data.items` - object for order items
* `data.customer` - customer object
* `data.customer.entity_id` - customer entity id
* `data.shipping_address` - object of order's shipping address
* `data.shipping_address.entity_id` - order shipping address id

### 2. order.updated

Event data example:
```
{
    "event_id": "603e5070d7193",
    "event_type": "order.updated",
    "created_utc": 1615139840,
    "data":
    {
        "entity_id": "1",
        "object": "order",
        "timestamp": "1611640026",
        "currency": "AUD",
        "total": "99.50",
        "discount": "0.99",
        "status": "processing",
        "payment_status": "paid",
        "fulfilment_status": "TBC",
        "items":
        [
            {
                "sku": "SKU001",
                "qty": 9,
                "vendor":
                {
                    "object": "vendor",
                    "entity_id": "1",
                    "name": "Vendor Name"
                }
            }
        ],
        "customer":
        {
            "entity_id": "1",
            "object": "customer",
            "email_address": "john.smith@gmail.com",
            "first_name": "John",
            "last_name": "Smith"
        },
        "shipping_address":
        {
            "entity_id": "1",
            "city": "Melbourne",
            "street":
            [
                "Road Street"
            ],
            "country_code": "AU",
            "postcode": "3000",
            "first_name": "John",
            "last_name": "Smith",
            "telephone": "8888-8888"
        }
    }
}
```

* `event_id` - unique identifier for the event
* `event_type` - type of webhook event
* `created_utc` - event creation time stamp as UTC
* `data` - object for event data
* `data.entity_id` - order id
* `data.timestamp` - timestamp of order creation time
* `data.status` - order status
* `data.payment_status` - order payment status (paid/unpaid)
* `data.items` - object for order items
* `data.customer` - customer object
* `data.customer.entity_id` - customer entity id
* `data.shipping_address` - object of order's shipping address
* `data.shipping_address.entity_id` - order shipping address id

### 3. cart.added

Event data example:
```
{
    "event_id": "6041d99327624",
    "event_type": "cart.added",
    "created_utc": 1614928275,
    "data":
    {
        "entity_id": "1",
        "object": "quote",
        "items":
        [
            {
                "sku": "SKU001",
                "qty": 1,
                "vendor":
                {
                    "object": "vendor",
                    "entity_id": "1",
                    "name": "Vendor Name"
                }
            }
        ],
        "customer":
        {
            "entity_id": "1",
            "object": "customer",
            "email_address": "john.smith@gmail.com",
            "first_name": "John",
            "last_name": "Smith"
        }
    }
}
```

* `event_id` - unique identifier for the event
* `event_type` - type of webhook event
* `created_utc` - event creation time stamp as UTC
* `data` - object for event data
* `data.entity_id` - quote id
* `data.items` - object for quote items
* `data.customer` - customer object
* `data.customer.entity_id` - customer entity id

### 4. cart.updated

Event data example:
```
{
    "event_id": "6041d99327624",
    "event_type": "cart.updated",
    "created_utc": 1614928275,
    "data":
    {
        "entity_id": "1",
        "object": "quote",
        "items":
        [
            {
                "sku": "SKU001",
                "qty": 1,
                "vendor":
                {
                    "object": "vendor",
                    "entity_id": "1",
                    "name": "Vendor Name"
                }
            }
        ],
        "customer":
        {
            "entity_id": "1",
            "object": "customer",
            "email_address": "john.smith@gmail.com",
            "first_name": "John",
            "last_name": "Smith"
        }
    }
}
```

* `event_id` - unique identifier for the event
* `event_type` - type of webhook event
* `created_utc` - event creation time stamp as UTC
* `data` - object for event data
* `data.entity_id` - quote id
* `data.items` - object for quote items
* `data.customer` - customer object
* `data.customer.entity_id` - customer entity id

### 5. cart.deleted

Event data example:
```
{
    "event_id": "6041d99327624",
    "event_type": "cart.deleted",
    "created_utc": 1614928275,
    "data":
    {
        "entity_id": "1",
        "object": "quote",
        "items":
        [
            {
                "sku": "SKU001",
                "qty": 1,
                "vendor":
                {
                    "object": "vendor",
                    "entity_id": "1",
                    "name": "Vendor Name"
                }
            }
        ],
        "customer":
        {
            "entity_id": "1",
            "object": "customer",
            "email_address": "john.smith@gmail.com",
            "first_name": "John",
            "last_name": "Smith"
        }
    }
}
```

* `event_id` - unique identifier for the event
* `event_type` - type of webhook event
* `created_utc` - event creation time stamp as UTC
* `data` - object for event data
* `data.entity_id` - quote id
* `data.items` - object for quote items
* `data.customer` - customer object
* `data.customer.entity_id` - customer entity id

### 6. customer.login

Event data example:
```
{
    "event_id": "6041da19a104a",
    "event_type": "customer.login",
    "created_utc": 1614928409,
    "data":
    {
        "customer":
        {
            "entity_id": "1",
            "object": "customer",
            "email_address": "john.smith@gmail.com",
            "first_name": "John",
            "last_name": "Smith"
        }
    }
}
```

* `event_id` - unique identifier for the event
* `event_type` - type of webhook event
* `created_utc` - event creation time stamp as UTC
* `data` - object for event data
* `data.customer` - customer object
* `data.customer.entity_id` - customer entity id

### 7. product.updated
Event data example:
```
{
    "event_id":"622ffe595d036",
    "event_type":"product.updated",
    "data":{
        "product":{
            "id": 1176,
            "sku": "LRTA_39419221180451",
            "name": "Belvia crystal bow heels (black)36EU (5AUS) (Approx. 4 week waiting period from date of order)",
            "attribute_set_id": 4,
            "price": 169.95,
            "status": 1,
            "visibility": 1,
            "type_id": "simple",
            "created_at": "2021-10-27 06:11:07",
            "updated_at": "2022-01-13 22:13:57",
            "weight": 0.001,
            "extension_attributes": {
                "website_ids": [
                    1
                ]
            },
            "product_links": [],
            "options": [],
            "media_gallery_entries": [],
            "tier_prices": [],
            "custom_attributes": [
                {
                    "attribute_code": "url_key",
                    "value": "belvia-crystal-bow-heels-black-36eu-5aus-approx-4-week-waiting-period-from-date-of-order"
                },
                {
                    "attribute_code": "tax_class_id",
                    "value": "2"
                }
            ]
        }
    }
}
```

* `event_id` - unique identifier for the event
* `event_type` - type of webhook event
* `data` - object for event data
* `data.product` - product object

### 8. shipment.updated
Event data example:
```
{
    "event_id":"622ff0389f3ee",
    "event_type":"shipment.updated",
    "data":{
        "shipment":{
            "billing_address_id": 26,
            "created_at": "2021-02-02 06:15:28",
            "customer_id": 2,
            "email_sent": 1,
            "entity_id": 1,
            "increment_id": "000000001",
            "order_id": 19,
            "packages": [],
            "shipping_address_id": 25,
            "store_id": 1,
            "total_qty": 1,
            "updated_at": "2021-02-02 06:15:28",
            "items": [
                {
                    "name": "PX7 Over-ear noise cancelling headphones",
                    "price": 400,
                    "product_id": 2,
                    "sku": "px7",
                    "weight": 1,
                    "order_item_id": 29,
                    "qty": 1
                }
            ],
            "tracks": [],
            "comments": []
        }
    }
}

```
* `event_id` - unique identifier for the event
* `event_type` - type of webhook event
* `data` - object for event data
* `data.shipment` - shipment object

## List of Webhook API
### 1. Get Webhook Type
`GET /V1/omnyfy/webhook_types`

Parameter: SearchCriteria

Example Request URL:
```
{domain}/rest/all/V1/omnyfy/webhook_types?searchCriteria[filterGroups][0][filters][0][field]=entity_id&searchCriteria[filterGroups][0][filters][0][value]=null&searchCriteria[filterGroups][0][filters][0][conditionType]=neq
```

### 2. Get Webhook by Store Id
`GET /V1/omnyfy/{storeid}/webhooks`

Parameter: Store ID, SearchCriteria

Example Request URL:
```
{domain}/rest/all/V1/omnyfy/1/webhooks?searchCriteria[filterGroups][0][filters][0][field]=entity_id&searchCriteria[filterGroups][0][filters][0][value]=null&searchCriteria[filterGroups][0][filters][0][conditionType]=neq
```

### 3. Get Webhook by Id
`GET /V1/omnyfy/webhook/{id}`

Parameter: Webhook ID


### 4. Delete Webhook
`DELETE /V1/omnyfy/webhook/{id}`

Parameter: Webhook ID


### 5. Save New Webhook
`POST /V1/omnyfy/webhook`

Parameter: Webhook

Example Request Parameter:

```
{
  "webhook": {
    "status": 0,
    "store_id": 0,
    "webhook_type_id": 1,
    "method": "POST",
    "endpoint_url": "{endpoint_url}",
    "content_type": "application/json"
  }
}
```

* Status 0: disabled
* Status 1: enabled


### 6. Update Webhook
`PUT /V1/omnyfy/webhook`

Parameter: Webhook

Example Request Parameter:
```
{
  "webhook": {
    "id": 1,
    "status": 1,
    "store_id": 0,
    "webhook_type_id": 1,
    "method": "POST",
    "endpoint_url": "{endpoint_url}",
    "content_type": "application/json"
  }
}
```

### 7. Get Webhook Event Schedules
`GET /V1/omnyfy/webhook_schedules`

Parameter: SearchCriteria

Example Request URL:
```
{domain}/rest/all/V1/omnyfy/webhook_schedules?searchCriteria[filterGroups][0][filters][0][field]=entity_id&searchCriteria[filterGroups][0][filters][0][value]=null&searchCriteria[filterGroups][0][filters][0][conditionType]=neq
```

### 8. Get Webhook Event History by Webhook Id
`GET /V1/omnyfy/webhook_history/{webhookId}`

Parameter: Webhook ID
