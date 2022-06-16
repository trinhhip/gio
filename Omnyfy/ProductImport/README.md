# Omnyfy Product Import v4

Provide aggregated product import API with better performance. Import product, image upload, assign vendor and set vendor location quantity should be done within one API call.

## Installation
```
git clone git@bitbucket.org:omnyfyteam/omnyfy-product-import.git app/code/Omnyfy/ProductImport
```

## Log File Location
```
<magento_root>\var\log\omnyfy_product_import.log
```

## Module Dependency
- [BigBridge_ProductImport](https://github.com/bigbridge-nl/product-import)
- [BigBridge Importer documentation](https://github.com/bigbridge-nl/product-import/blob/master/doc/importer.md)

## API Endpoints

### Create products endpoint
URL: `{domain}/rest/V1/omnyfy/products`  
Method: `POST`

####  Attributes Description
- `items.product_data.sku` - Product SKU, this field is mandatory 
- `items.product_data.name` - Product name, this field is mandatory 
- `items.product_data.type_id` - Product type, support 'simple' and 'configurable' value in this field.
- `items.product_data.attribute_set_id` - Product's attribute_set_id, this field is mandatory, and the value of this field has to be existed in marketplace.
- `items.product_data.status` - Product status, 1 or 0.
- `items.product_data.visibility` - Product visibility, possible value 1 : Not Visible Individually / 2: Catalog / 3: Search / 4: Catalog and Search
- `items.product_data.website_codes` - assign product to websites, by using website code inside an array, default value is `["base"]`
- `items.product_data.custom_attributes.category_ids` - product category ids
- `items.price.value` - product price value
- `items.extension_attributes.stock_item.is_in_stock`
- `items.inventory.vendor_ids` - assign product to specific vendor, this field will be optional if passing vendor authentication token through API
- `items.inventory.items.source_code` - assign product to specific source
- `items.inventory.items.qty` - assign product qty to the mentioned source
- `items.inventory.items.is_in_stock` - set stock status to specific source
- `items.product_data.media_gallery_entries.file` - image url
- `items.product_data.media_gallery_entries.import_mode` - "sync" or "async", default value is "sync". Use async will download image asynchronously.

#### Example request for Simple Product

```
{
  "items": [
    {
      "inventory": {
        "vendor_ids": [1],
        "items": [
          {
            "source_code": "source_code",
            "qty": 100,
            "is_in_stock":true
          }
        ]
      },
      "price": {
        "value": 15,
        "currency": "AUD"
      },
      "product_data": {
        "sku": "sweet-strawberry-250gr",
        "name": "Sweet Strawberry 250gr",
        "attribute_set_id": 4,
        "status": 1,
        "visibility": 4,
        "type_id": "simple",
        "weight": 0.5,
        "website_codes": ["base"],
        "custom_attributes": [
          {
            "attribute_code": "url_key",
            "value": "sweet-strawberry-250gr"
          },
          {
            "attribute_code": "name",
            "value": "Sweet Strawberry 250gr"
          },
          {
            "attribute_code": "description",
            "value": "A pack of 250 grams sweet strawberry."
          },
          {
            "attribute_code": "category_ids",
            "value": ["2", "3"]
          }
        ],
        "media_gallery_entries": [
          {
            "media_type": "image",
            "label": "Sweet Strawberry 250gr",
            "position": 1,
            "disabled": false,
            "types": [
              "image",
              "small_image",
              "thumbnail"
            ],
            "file": "https://image.freepik.com/free-photo/strawberries_1194-2304.jpg",
            "content": {
              "name": "sweet-strawberry-250gr_1.jpg"
            }
          }
        ],
        "extension_attributes": {
          "stock_item": {
            "is_in_stock": true,
            "manage_stock": true
          }
        }
      }
    }
  ]
}
```

#### Example request for Configurable product
- `items.product_data.type_id` use "configurable" for parent product
- `items.product_data.super_attribute_codes` set attribute code for product varitants
- `items.product_data.variant_skus` array of child product skus
```
{
  "items": [
    {
      "inventory": {
        "vendor_ids": [1],
        "items": [
          {
            "source_code": "source_code",
            "qty": 150,
            "is_in_stock":true
          }
        ]
      },
      "price": {
        "value": 15,
        "currency": "AUD"
      },
      "product_data": {
        "sku": "basic-t-shirt-white-s",
        "name": "Basic T-Shirt White S",
        "attribute_set_id": 4,
        "status": 1,
        "visibility": 1,
        "type_id": "simple",
        "weight": 0.5,
        "website_codes": ["base"],
        "custom_attributes": [
          {
            "attribute_code": "url_key",
            "value": "basic-t-shirt-white-s"
          },
          {
            "attribute_code": "omnyfy_dimensions_height",
            "value": "15"
          },
          {
            "attribute_code": "omnyfy_dimensions_length",
            "value": "15"
          },
          {
            "attribute_code": "omnyfy_dimensions_width",
            "value": "5"
          },
          {
            "attribute_code": "name",
            "value": "Basic T-Shirt White S"
          },
          {
            "attribute_code": "description",
            "value": "Basic T-Shirt White S"
          },
          {
            "attribute_code": "color",
            "value": "5"
          },
          {
            "attribute_code": "size",
            "value": "7"
          },
          {
            "attribute_code": "category_ids",
            "value": [
              "2", "3"
            ]
          }
        ],
        "media_gallery_entries": [
          {
            "media_type": "image",
            "label": "Basic T-Shirt White S",
            "position": 1,
            "disabled": false,
            "types": [
              "image",
              "small_image",
              "thumbnail"
            ],
            "file": "https://image.freepik.com/free-vector/white-t-shirt-mockup-t-shirt-with-short-sleeves_107791-2029.jpg",
            "content": {
              "base64_encoded_data": "",
              "type": "image/jpeg",
              "name": "basic-t-shirt-white-s_1.jpg"
            }
          }
        ],
        "extension_attributes": {
          "stock_item": {
            "is_in_stock": true,
            "manage_stock": true
          }
        }
      }
    },
    {
      "inventory": {
        "vendor_ids": [1],
        "items": [
          {
            "source_code": "source_code",
            "qty": 200,
            "is_in_stock":true
          }
        ]
      },
      "price": {
        "value": 15,
        "currency": "AUD"
      },
      "product_data": {
        "sku": "basic-t-shirt-black-m",
        "name": "Basic T-Shirt Black M",
        "attribute_set_id": 4,
        "status": 1,
        "visibility": 1,
        "type_id": "simple",
        "weight": 0.5,
        "website_codes": ["base"],
        "custom_attributes": [
          {
            "attribute_code": "url_key",
            "value": "basic-t-shirt-black-m"
          },
          {
            "attribute_code": "omnyfy_dimensions_height",
            "value": "15"
          },
          {
            "attribute_code": "omnyfy_dimensions_length",
            "value": "15"
          },
          {
            "attribute_code": "omnyfy_dimensions_width",
            "value": "5"
          },
          {
            "attribute_code": "name",
            "value": "Basic T-Shirt Black M"
          },
          {
            "attribute_code": "description",
            "value": "Basic T-Shirt Black M"
          },
          {
            "attribute_code": "color",
            "value": "6"
          },
          {
            "attribute_code": "size",
            "value": "8"
          },
          {
            "attribute_code": "category_ids",
            "value": [
              "2", "3"
            ]
          }
        ],
        "media_gallery_entries": [
          {
            "media_type": "image",
            "label": "Basic T-Shirt Black M",
            "position": 1,
            "disabled": false,
            "types": [
              "image",
              "small_image",
              "thumbnail"
            ],
            "file": "https://image.freepik.com/free-psd/black-sport-t-shirts-front-back-mock-up-template-your-design_34168-1390.jpg",
            "content": {
              "base64_encoded_data": "",
              "type": "image/jpeg",
              "name": "basic-t-shirt-black-m_1.jpg"
            }
          }
        ],
        "extension_attributes": {
          "stock_item": {
            "is_in_stock": true,
            "manage_stock": true
          }
        }
      }
    },
    {
      "inventory": {
        "vendor_ids": [1],
        "items": [
          {
            "source_code": "source_code"
          }
        ]
      },
      "price": {
        "value": 15,
        "currency": "AUD"
      },
      "product_data": {
        "sku": "basic-t-shirt",
        "name": "Basic T-Shirt",
        "attribute_set_id": 4,
        "status": 1,
        "visibility": 4,
        "type_id": "configurable",
        "weight": 0.5,
        "website_codes": ["base"],
        "super_attribute_codes": ["size", "color"],
        "variant_skus":[
          "basic-t-shirt-white-s",
          "basic-t-shirt-black-m"
        ],
        "custom_attributes": [
          {
            "attribute_code": "url_key",
            "value": "basic-t-shirt"
          },
          {
            "attribute_code": "omnyfy_dimensions_height",
            "value": "15"
          },
          {
            "attribute_code": "omnyfy_dimensions_length",
            "value": "15"
          },
          {
            "attribute_code": "omnyfy_dimensions_width",
            "value": "5"
          },
          {
            "attribute_code": "name",
            "value": "Basic T-Shirt"
          },
          {
            "attribute_code": "description",
            "value": "Basic T-Shirt"
          },
          {
            "attribute_code": "category_ids",
            "value": [
              "2", "3"
            ]
          }
        ],
        "media_gallery_entries": [
          {
            "media_type": "image",
            "label": "Basic T-Shirt",
            "position": 1,
            "disabled": false,
            "types": [
              "image",
              "small_image",
              "thumbnail"
            ],
            "file": "https://image.freepik.com/free-vector/realistic-t-shirt-isolated-illustration-set_1284-39978.jpg",
            "content": {
              "base64_encoded_data": "",
              "type": "image/jpeg",
              "name": "basic-t-shirt_1.jpg"
            }
          }
        ],
        "extension_attributes": {
          "stock_item": {
            "is_in_stock": true,
            "manage_stock": true
          }
        }
      }
    }
  ]
}
```

#### Response - HTTP 200

- `items.success` - true or false
- `items.error` - error message for the import if product is not imported.
- `items.product_data.id` - marketplace product entity id
- `items.product_data.sku` - product sku

```
{
  "items":[
    {
      "success": true,
      "error":"error messages",
      "product_data": {
        "id":111,
        "sku":"product-sku",
      }
    }
  ]
}
```

### Update products endpoint
URL: `{domain}/rest/V1/omnyfy/products`  
Method: `PUT`  

#### Request
Refer to the request definition and example for POST method 

#### Response - HTTP 200

- `items.success` - true or false
- `items.error` - error message for the import if product is not imported.
- `items.product_data.id` - marketplace product entity id
- `items.product_data.sku` - product sku

```
{
  "items":[
    {
      "success": true,
      "error":"error messages",
      "product_data": {
        "id":111,
        "sku":"product-sku",
      }
    }
  ]
}
```

## Notes
- Make sure the custom attributes are exist before importing.
- For configurable product's child, use `option_id` (not the text) as the attribute's variant value.
