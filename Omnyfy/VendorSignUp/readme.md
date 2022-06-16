## Setup

1. **Enable module**
    - In Admin panel navigate to 
      _Stores->Configuration->Omnymart(Omnyfy)->Vendor Sign Up_.
      Under 'Vendor Sign Up' tab set Enable to 'Yes'
      
2. **Create Vendor Sign Up page**
    - In admin panel navigate to
   _Content->Elements->Pages_.
      Add a new page.
      To display Subscription plans, add following block to content
      
      ```bash 
      {{block name="type_plan_form" class="\Omnyfy\VendorSubscription\Block\Form\Type" vendor_type_id=1 }}
      ```
      vendor_type_id - is an id of Vendor Type and can be found under _Marketplace Management->Vendor Types_ in ID column
      
      **Note:** Under Design tab make sure Layout is not set to 'Empty' as page will appear without a header and footer in this case.  
      
3. **Create Sign Up Fail and Success pages**
      By default fail and success message after sign up appear on homepage as standard messages. 
   You can create separate page to enhance user experience.
   
   - In admin panel navigate to
       _Content->Elements->Pages_.
       Add a new page Vendor Signup Fail and fill in content.
       Copy page's url under Search Engine Optimization tab.

       - In Admin panel navigate to
     _Stores->Configuration->Omnymart(Omnyfy)->Vendor Sign Up_.
     Insert copied url into 'Return Url for error of fail'
     
   - Make the same for Vendor Sign Up Success page
   
   - Save configuration and Flush cache.
   
4. **Enable Google captcha**
   - In admin panel navigate to 
   _Stores->Configuration->Security->Google reCAPTCHA Storefront
     Choose reCAPTCHA type and set Google API website key and secret key.
     Open Storefront tab and choose reCAPTCHA type for Vendor Signup Form
     
5. **Add vendor attributes to Sign Up form**
   - In admin panel navigate to
    _Marketplace Management->Vendor Attributes_
     Select or create attribute. Open it and select Storefront Properties tab. Set 'Used in registration form' to 'Yes'.
     