# Core module documentation
This module contains utils, that are used in other Omnyfy modules

## Logged in user javascript check
Due to inconsistent customer data behavior with Varnish, we set up custom logged in user check.

### How to check logged in user
Core module contains only javascript file Omnyfy/Core/view/frontend/web/js/customer-loggedin.js
To make check in your custom template, use following markup pattern:

```
<div data-bind="scope: `customer_logged_in`">
<!-- ko ifnot: isLoaded -->
     <div><?= __('Loading...')?></div>
<!-- /ko -->

<!-- ko if: isLoggedIn  -->
     <div style="display: none;" data-bind="visible: isLoggedIn">
        content for logged in user
     </div>
<!-- /ko -->

<!-- ko if: isNotLoggedIn  -->
     <div style="display: none;" data-bind="visible: isNotLoggedIn">
          content for not loggedin user
     </div>
<!-- /ko -->
</div>

<script type="text/x-magento-init">
    {
        "*": {
            "Magento_Ui/js/core/app": {
                "components": {
                    "customer_logged_in": {
                        "component": "Omnyfy_Core/js/customer-loggedin"
                    }
                }
            }
        }
    }
</script>
``` 

