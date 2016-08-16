<?php

/**
 * CustomerIO.php
 *
 * Yii extension for Customer.io
 *
 * @author Egor Rykhnov  <egor.developer@gmail.com>
 * @copyright Copyright (c) 2016, Egor Rykhnov
 * @package CustomerIO
 * @version 1.0
 */
class CustomerIO extends CComponent {

    public $apiKey;
    public $siteId;
    
    /**
     * Default options
     * @var array
     */
    protected $defaultOptions = array(
        'user_id',
        //'first_name',
        //'last_name',
        'email',
        'type',
        'trigger'
    );
    
    protected $defaultValues = array();
    
    private $isSendMail = true;

    public function init() {
        
    }

    /**
     * Creates a customer in Customer.io
     * @param  mixed $customer_id You'll want to set this dynamically to the unique id of the user associated with the event
     * @param  array $attributes Extra customer info
     * @return void
     */
    public function createCustomer($customerId, $attributes) {
        if (!$this->isSendMail) {
            return;
        }

        $session = curl_init();
        $customerioUrl = 'https://track.customer.io/api/v1/customers/';
        curl_setopt($session, CURLOPT_URL, $customerioUrl . $customerId);
        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_HTTPGET, 1);
        curl_setopt($session, CURLOPT_HEADER, true);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($session, CURLOPT_VERBOSE, 1);
        curl_setopt($session, CURLOPT_POSTFIELDS, http_build_query($attributes));
        curl_setopt($session, CURLOPT_USERPWD, $this->siteId . ":" . $this->apiKey);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($session);
        curl_close($session);

        if (strpos($resp, "Unauthorized request") !== false)
            throw new Exception("CustomerIo! Unauthorized request apiKey: $this->apiKey siteId: $this->siteId");
    }

    /**
     * Register an event in Customer.io
     * 
     * @param  mixed $customer_id You'll want to set this dynamically to the unique id of the user associated with the event
     * @param  string $name Event's name
     * @param  array $event_data Event data
     * @return void
     */
    public function trackEvent($customerId, $name, $eventData = array()) {
        if (!$this->isSendMail) {
            return;
        }
        $session = curl_init();
        $customerioUrl = 'https://track.customer.io/api/v1/customers/' . $customerId . '/events';
        $data = ['name' => $name, 'data' => $eventData];
        curl_setopt($session, CURLOPT_URL, $customerioUrl);
        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_VERBOSE, 1);
        curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($session, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($session, CURLOPT_USERPWD, $this->siteId . ":" . $this->apiKey);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($session);

        curl_close($session);
    }

    /**
     * Checking for required fields
     * 
     * @param array $options user data
     * @param array $defaultFields required fields
     * @return type
     * @throws HttpException
     */
    protected function testDefaultOptions($options, $defaultFields = array()) {
        if (!$this->isSendMail) {
            return;
        }
        $createCustomerOptions = array();

        if (empty($defaultFields)) {
            $defaultFields = $this->defaultOptions;
        }
        
        foreach ($defaultFields as $defaultField) {
            if (!isset($options[$defaultField])) {
                throw new HttpException(501, __CLASS__ . ' no default field [' . $defaultField . ']');
            }

            if (!in_array($defaultField, ['trigger', 'user_id'])) {
                $createCustomerOptions[$defaultField] = $options[$defaultField];
            }

            if (isset($this->defaultValues[$defaultField])) {
                $createCustomerOptions[$defaultField] = $this->defaultValues[$defaultField];
            }
        }

        return $createCustomerOptions;
    }

    /**
     * Send Mail
     * 
     * @param array $options
     * @param array $createCustomerOptions
     * @return type
     * @throws Exception
     */
    protected function send($options, $createCustomerOptions = array()) {
        if (!$this->isSendMail) {
            return;
        }

        if (empty($createCustomerOptions)) {
            $createCustomerOptions = $this->defaultOptions;
        }

        try {
            $createCustomerOptions = $this->testDefaultOptions($options, $createCustomerOptions);
        } catch (Exception $e) {
            throw $e;
        }

        if (!isset($options['created_at']) || empty($options['created_at'])) {
            $options['created_at'] = time();
        }

        $this->createCustomer($options['user_id'], $createCustomerOptions);

        $this->trackEvent($options['user_id'], $options['trigger'], $options);
    }

    /**
     * Test
     * 
     * @param array $data
     */
    public function test($data) {
        $options = array(
            'trigger' => 'test',
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'user_id' => $data['user_id'],            
            'type' => 'event'
        );
        $this->send($options);
    } 
}
