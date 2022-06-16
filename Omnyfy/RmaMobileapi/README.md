### Module installnation

```
git clone git@bitbucket.org:omnyfyteam/omnyfy-rma-api.git app/code/Omnyfy/RmaMobileapi
```

### Permission
Assign following permission to the user role for vendors to allow vendor user able to use RMA feature on mobile via API. 

* Resources > RMA Mobile API

### Sample Curl for the API 

"data.items.key", this key should be passed the parent order item id if the item is a child item of configurable product.

```
curl --location --request PUT 'https://smm.omnyfy.com/rest/V1/rma/vendor/save' --header 'Content-Type: application/json' --header 'Authori
zation: Bearer j1m7cpbeqdg5nwd305pm14v1f29ux8nr' --header 'Cookie: PHPSESSID=5jaduna8ha24ul8b3fdvhjkh10' --data-raw '{"data":
    {"data":
        {
        "order_id": 70,
        "store_id": 1,"order_ids": {"1":70 },
        "items":
            {
                "178":
                    {
                        "is_return": true,
                        "qty_requested": 1,
                        "reason_id": "1",
                        "condition_id": "1",
                        "resolution_id": "1","order_id":70
                    }
            }
        ,
        "reply": "Manual change attempt"
        }
    }
}'
```