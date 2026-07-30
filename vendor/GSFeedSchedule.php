<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */

require_once 'GSBase.php';

class GSFeedSchedule extends GSBase
{

    private $feed_name = '';
    private $feed_config = array();
    private $feed_fetch_url = '';

    const CONTENT_TYPE = 'products';
    const INTEND_DESTINATION = 'Shopping';
    const FILE_ENCODING = 'utf-8';
    const COLUMN_DELIMITER = 'tab';
    const QUOTING_MODE = 'value quoting';

    //function defination to insert feed in Google Shopping
    public function insertDataFeed($feed_config, $feed_fetch_url)
    {
        $module = Module::getInstanceByName('kbgoogleshopping');
        $this->feed_config = $feed_config;
        $this->feed_name = $this->feed_config['feed_label'];
        $this->feed_fetch_url = $feed_fetch_url;
        $lang_iso_code = Language::getIsoById($this->feed_config['id_lang']);
        $country_iso_code = Country::getIsoById($this->feed_config['id_country']);
        $target  = new Google_Service_ShoppingContent_DatafeedTarget();
        $target->setLanguage($lang_iso_code);
        $target->setCountry($country_iso_code);
        //$target->setIncludedDestinations([self::INTEND_DESTINATION]);

        $this->setContentLanguage($lang_iso_code)
                ->setTargetCountry($country_iso_code);

        $datafeed = new Google_Service_ShoppingContent_Datafeed();

        $datafeed->setName($this->feed_name);
        $datafeed->setContentType(self::CONTENT_TYPE);
        $datafeed->setFileName($this->feed_name);
        $datafeed->setAttributeLanguage($this->getContentLanguage());
        $datafeed->setTargets([$target]);

        $format = new Google_Service_ShoppingContent_DatafeedFormat();
        $format->setFileEncoding(self::FILE_ENCODING);
        $format->setColumnDelimiter(self::COLUMN_DELIMITER);
        $format->setQuotingMode(self::QUOTING_MODE);

        $datafeed->setFormat($format);

        $fetch_schedule = new Google_Service_ShoppingContent_DatafeedFetchSchedule();

        if ($this->feed_config['upload_type'] == 'weekly') {
            $fetch_schedule->setWeekday($this->feed_config['weekday']);
        } elseif ($this->feed_config['upload_type'] == 'monthly') {
            $fetch_schedule->setDayOfMonth($this->feed_config['day_of_month']);
        }
        $fetch_schedule->setHour($this->feed_config['upload_hour']);
        $fetch_schedule->setTimeZone(date_default_timezone_get());
        $fetch_schedule->setFetchUrl($this->feed_fetch_url);

        $datafeed->setFetchSchedule($fetch_schedule);

        try {
            $response = $this->service->datafeeds->insert($this->merchant_id, $datafeed);
            return $response;
        } catch (Google_IO_Exception $ex) {
            return sprintf($module->l('Data feed cannot be uploaded as have error: %s', 'GSFeedSchedule'), $ex->getMessage());
        } catch (Google_Auth_Exception $ex) {
            return sprintf($module->l('Data feed cannot be uploaded as have error: %s', 'GSFeedSchedule'), $ex->getMessage());
        } catch (Google_Service_Exception $ex) {
            return sprintf($module->l('Data feed cannot be uploaded as have error: %s', 'GSFeedSchedule'), $ex->getMessage());
        } catch (Google_Exception $ex) {
            return sprintf($module->l('Data feed cannot be uploaded as have error: %s', 'GSFeedSchedule'), $ex->getMessage());
        } catch (Exception $ex) {
            return sprintf($module->l('Data feed cannot be uploaded as have error: %s', 'GSFeedSchedule'), $ex->getMessage());
        }
    }
    
    public function getFeedSchedule($data_feed_id, $feed_config, $feed_fetch_url)
    {
        $this->feed_config = $feed_config;
        $this->feed_fetch_url = $feed_fetch_url;
        try {
            $datafeed = $this->service->datafeeds->get($this->merchant_id, $data_feed_id);
            return true;
        } catch (Google_IO_Exception $ex) {
            return $ex->getMessage();
        } catch (Google_Auth_Exception $ex) {
            return $ex->getMessage();
        } catch (Google_Service_Exception $ex) {
            return $ex->getMessage();
        } catch (Google_Exception $ex) {
            return $ex->getMessage();
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
        return $datafeed;
    }

    //function defination to update feed schedule
    public function updateFeedSchedule($data_feed_id, $feed_config, $feed_fetch_url)
    {
        /**
         * To fix error while updating feed. Error: Call to a member function setHour() on null.
         * Used the same code that is used to create a feed
         * TGjune2023 Feed-Issue
         * @date 10-06-2023
         * @author Tanisha Gupta
         */
//        $module = Module::getInstanceByName('kbgoogleshopping');
//        $this->feed_config = $feed_config;
//        $this->feed_fetch_url = $feed_fetch_url;
//        $datafeed = $this->service->datafeeds->get($this->merchant_id, $data_feed_id);
//
//        if ($this->feed_config['upload_type'] == 'weekly') {
//            $datafeed->getFetchSchedule()->setWeekday($this->feed_config['weekday']);
//        } elseif ($this->feed_config['upload_type'] == 'monthly') {
//            $datafeed->getFetchSchedule()->setDayOfMonth($this->feed_config['day_of_month']);
//        }
//        $datafeed->getFetchSchedule()->setHour($this->feed_config['upload_hour']);
//
//        $datafeed->getFetchSchedule()->setFetchUrl($this->feed_fetch_url);
        $module = Module::getInstanceByName('kbgoogleshopping');
        $this->feed_config = $feed_config;
        $this->feed_name = $this->feed_config['feed_label'];
        $this->feed_fetch_url = $feed_fetch_url;
        $lang_iso_code = Language::getIsoById($this->feed_config['id_lang']);
        $country_iso_code = Country::getIsoById($this->feed_config['id_country']);
        $target  = new Google_Service_ShoppingContent_DatafeedTarget();
        $target->setLanguage($lang_iso_code);
        $target->setCountry($country_iso_code);
        //$target->setIncludedDestinations([self::INTEND_DESTINATION]);

        $this->setContentLanguage($lang_iso_code)
                ->setTargetCountry($country_iso_code);

        $datafeed = new Google_Service_ShoppingContent_Datafeed();

        $datafeed->setName($this->feed_name);
        $datafeed->setContentType(self::CONTENT_TYPE);
        $datafeed->setFileName($this->feed_name);
        $datafeed->setAttributeLanguage($this->getContentLanguage());
        $datafeed->setTargets([$target]);

        $format = new Google_Service_ShoppingContent_DatafeedFormat();
        $format->setFileEncoding(self::FILE_ENCODING);
        $format->setColumnDelimiter(self::COLUMN_DELIMITER);
        $format->setQuotingMode(self::QUOTING_MODE);

        $datafeed->setFormat($format);

        $fetch_schedule = new Google_Service_ShoppingContent_DatafeedFetchSchedule();

        if ($this->feed_config['upload_type'] == 'weekly') {
            $fetch_schedule->setWeekday($this->feed_config['weekday']);
        } elseif ($this->feed_config['upload_type'] == 'monthly') {
            $fetch_schedule->setDayOfMonth($this->feed_config['day_of_month']);
        }
        $fetch_schedule->setHour($this->feed_config['upload_hour']);
        $fetch_schedule->setTimeZone(date_default_timezone_get());
        $fetch_schedule->setFetchUrl($this->feed_fetch_url);
        // Set the data feed ID
        $datafeed->setId($data_feed_id); 
        $datafeed->setFetchSchedule($fetch_schedule);

        try {
            $response = $this->service->datafeeds->update($this->merchant_id, $data_feed_id, $datafeed);
            return $response;
        } catch (Google_IO_Exception $ex) {
            return sprintf($module->l('Error: %s', 'GSFeedSchedule'), $ex->getMessage());
        } catch (Google_Auth_Exception $ex) {
            return sprintf($module->l('Error: %s', 'GSFeedSchedule'), $ex->getMessage());
        } catch (Google_Service_Exception $ex) {
            return sprintf($module->l('Error: %s', 'GSFeedSchedule'), $ex->getMessage());
        } catch (Google_Exception $ex) {
            return sprintf($module->l('Error: %s', 'GSFeedSchedule'), $ex->getMessage());
        } catch (Exception $ex) {
            return sprintf($module->l('Error: %s', 'GSFeedSchedule'), $ex->getMessage());
        }
    }

    //function defination to delete feed
    public function deleteFeed($feed_id)
    {
        try {
            $response = $this->service->datafeeds->delete($this->merchant_id, $feed_id);
            return $response;
        } catch (Google_IO_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        } catch (Google_Auth_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        } catch (Google_Service_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        } catch (Google_Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        } catch (Exception $ex) {
            return array('error' => true, 'message' => $ex->getMessage());
        }
    }
}
