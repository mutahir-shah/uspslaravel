<?php

/**
 * Available Laravel Methods
 * Add other USPS API Methods
 * Based on Vincent Gabriel @VinceG USPS PHP-Api https://github.com/VinceG/USPS-php-api
 *
 * @since  1.0
 * @author John Paul Medina
 * @author Vincent Gabriel
 */

namespace Usps;

function __autoload($class_name) {
    include $class_name . '.php';
}

class Usps {

    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function validate($request) {

        $verify = new AddressVerify($this->config['username']);
        $address = new Address;
        $address->setFirmName(null);
        $address->setApt( (array_key_exists('Apartment', $request) ? $request['Apartment'] : null ) );
        $address->setAddress( (array_key_exists('Address', $request) ? $request['Address'] : null ) );
        $address->setCity( (array_key_exists('City', $request) ? $request['City'] : null ) );
        $address->setState( (array_key_exists('State', $request) ? $request['State'] : null ) );
        $address->setZip5( (array_key_exists('Zip', $request) ? $request['Zip'] : null ) );
        $address->setZip4('');

        // Add the address object to the address verify class
        $verify->addAddress($address);

        // Perform the request and return result
        $val1 = $verify->verify();
        $val2 = $verify->getArrayResponse();

        // var_dump($verify->isError());

        // See if it was successful
        if ($verify->isSuccess()) {
            return ['address' => $val2['AddressValidateResponse']['Address']];
        } else {
            return ['error' => $verify->getErrorMessage()];
        }


    }



    public function calculateRates($request) {

         // Initiate and set the username provided from usps
        $rate = new Rate($this->config['username']);
        // During test mode this seems not to always work as expected
        //$rate->setTestMode(true);
        // Create new package object and assign the properties
        // apartently the order you assign them is important so make sure
        // to set them as the example below
        // set the RatePackage for more info about the constants
        $package = new RatePackage;
        $package->setService(RatePackage::SERVICE_FIRST_CLASS);
        $package->setFirstClassMailType(RatePackage::MAIL_TYPE_LETTER);
        $package->setZipOrigination(91601);
        $package->setZipDestination(91730);
        $package->setPounds(0);
        $package->setOunces(3.5);
        $package->setContainer('');
        $package->setSize(RatePackage::SIZE_REGULAR);
        $package->setField('Machinable', true);
        // add the package to the rate stack
        $rate->addPackage($package);
        // Perform the request and print out the result
        echo '<pre>';
        print_r($rate->getRate());
        echo '<hr>';
        print_r($rate->getArrayResponse());
        // Was the call successful
        if ($rate->isSuccess()) {
            echo 'Done';
        } else {
            echo 'Error: ' . $rate->getErrorMessage();
        }

    }// end functions.



       public function getPriorityLabels($request) {
        // Initiate and set the username provided from usps
        $label = new PriorityLabel($this->config['username']);
        // During test mode this seems not to always work as expected
        $label->setTestMode(false);
        $label->setFromAddress('John', 'Doe', '', '5161 Lankershim Blvd', 'North Hollywood', 'CA', '91601', '# 204', '', '8882721214');
        $label->setToAddress('Vincent', 'Gabriel', '', '230 Murray St', 'New York', 'NY', '10282');
        $label->setWeightOunces(1);
        $label->setField(36, 'LabelDate', '30/01/2018');
        //$label->setField(32, 'SeparateReceiptPage', 'true');
        // Perform the request and return result
        $label->createLabel();
        //print_r($label->getArrayResponse());
        //print_r($label->getPostData());
        //var_dump($label->isError());
        // See if it was successful
        if ($label->isSuccess()) {
            //echo 'Done';
            //echo "\n Confirmation:" . $label->getConfirmationNumber();
            $label = $label->getLabelContents();
            if ($label) {
                $contents = base64_decode($label);
                header('Content-type: application/pdf');
                header('Content-Disposition: inline; filename="label.pdf"');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: '.strlen($contents));
                echo $contents;
                exit;
            }
        } else {
            echo 'Error: '.$label->getErrorMessage();


               }
           }// end of function
}
