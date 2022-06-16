# Omnyfy Featured Vendor

Allows the market place owner to display featured vendors and/or location is a website.

### FrontendSetup guide
* Open Dashboard and navigate to cms page where you want to display Featured vendors
* switch to html edit mode
* insert following code
 
**Old style**
```
{{block class="Omnyfy\VendorFeatured\Block\Vendor\Featured" 
template="Omnyfy_VendorFeatured::featured.phtml"}}
```

**New style**
```
{{block class="Omnyfy\VendorFeatured\Block\Vendor\Featured" 
template="Omnyfy_VendorFeatured::unified-featured.phtml"}}
```

**Pass parameters for owl carousel**

_Note: pass only those that you want to override_
```
{{block class="Omnyfy\VendorFeatured\Block\Vendor\Featured" 
template="Omnyfy_VendorFeatured::unified-featured.phtml"
        autoplay='true'
        autoplayTimeout='5000'
        autoplayHoverPause=true
        margin='20'
        nav='true'
        navText='["<em class=\'porto-icon-left-open-big\'></em>","<em class=\'porto-icon-right-open-big\'></em>"]'
        navTextPrev="<em class='porto-icon-left-open-big'></em>"
        navTextNext="<em class='porto-icon-right-open-big'></em>"
        dots='true'
        loop='true'
        items_0='2'
        items_640='3'
        items_768='4'
        items_992='5'
        items_1200='5'
}}
```
**Promotional Vendor Widget**
1. Setting: Marketplace Management > Featured Vendors > Vendor Homepage Product Promo
2. insert the following code to content block/page
{{block class="Omnyfy\VendorFeatured\Block\Vendor\Featured" template="Omnyfy_VendorFeatured::widget/promo.phtml"}}

### Versions
Version 1.0.6
- Add Vendor Spotlight Banner Functionality

Version 1.0.5
- Enhance Promotional Vendor Widget Functionality: add sort order and configuration for number of product

Version 1.0.4
- Add Promotional Vendor Widget Functionality

Version 1.0.3
* Unified vendors layout with Vendor Search module results layout in alternative template
* Added carousel script into template    

Version 1.0.2
* Ability display all the featured vendors without tag filter
* Configurable option to enable/display featured vendors
* Configurable option to enable/disable featured vendor tags 
* Improve the default template based on the configuration 

Version 1.0.1
* Add featured location
* Fixed bug saving featured vendor tags and displaying in the admin area

Version 1.0.0
* Add featured vendors
* Add featured vendor tags
* Display featured vendors filtered by tags 