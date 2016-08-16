#Yii extension for Customer.io

##Setup

Copy to /protected/extencions/custumerio/CustomerIO.php

Add to your config file:

```php
//...
    'components'=>array(
        //...
        'customerio' => array(
            'class' => 'ext.customerio.CustomerIO',
            'siteId' => '<SITE ID>',
            'apiKey' => '<API KEY>',
        ),
    ),
//...
```
##Usage

```php
// Creating a customer
Yii::app()->customerio->createCustomer(
    Yii::app()->user->id,
    array(
        'first_name' => 'FirstName',
        'last_name' => 'LastName',
        'email' => 'example@example.com',
        'user_id' => '1',
        // add as many fields as you want
    )
);

// Tracking an event
Yii::app()->customerio->trackEvent(Yii::app()->user->id, 'createdNewProject');

// Tracking an event with extra fields
Yii::app()->customerio->trackEvent(
    Yii::app()->user->id, 
    'purchased',
    array(
        'var1' => 'value1',
        'var2' => 'value2',
        //...
    )
);

// Send Test
Yii::app()->customerio->test(
    array(
        'first_name' => 'FirstName',
        'last_name' => 'LastName',
        'email' => 'example@example.com',
        'user_id' => '1'
    )
);

```
