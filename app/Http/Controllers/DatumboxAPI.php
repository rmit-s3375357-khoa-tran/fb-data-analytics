<?php
/**
 * Created by PhpStorm.
 * User: Nancy
 * Date: 23-Aug-17
 * Time: 8:32 PM
 */

namespace App\Http\Controllers;

class DatumboxAPI {
    const version='1.0';

    protected $api_key;

    /**
     * Constructor
     *
     * @param string $api_key
     * @return DatumboxAPI
     */
    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    /**
     * Calls the Web Service of Datumbox
     *
     * @param string $api_method
     * @param array $POSTparameters
     *
     * @return string $jsonreply
     */
    protected function CallWebService($api_method,$POSTparameters) {
        $POSTparameters['api_key']= $this->api_key;

//        $mh = curl_multi_init();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.datumbox.com/'.self::version.'/'.$api_method.'.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTparameters);

        $jsonreply = curl_exec ($ch);
        curl_close ($ch);
        unset($ch);

        return $jsonreply;
    }

    /**
     * Parses the API Reply
     *
     * @param mixed $jsonreply
     *
     * @return mixed
     */
    protected function ParseReply($jsonreply) {
        $jsonreply=json_decode($jsonreply,true);

        if(isset($jsonreply['output']['status']) && $jsonreply['output']['status']==1) {
            return $jsonreply['output']['result'];
        }

        if(isset($jsonreply['error']['ErrorCode']) && isset($jsonreply['error']['ErrorMessage'])) {
            echo $jsonreply['error']['ErrorMessage'].' (ErrorCode: '.$jsonreply['error']['ErrorCode'].')';
        }

        return false;
    }

    /**
     * Performs Sentiment Analysis on Twitter.
     *
     * @param string $text The text of the tweet that we evaluate.
     *
     * @return string|false It returns "positive", "negative" or "neutral" on success and false on fail.
     */
    public function TwitterSentimentAnalysis($text) {
        $parameters=array(
            'text'=>$text,
        );

        $jsonreply=$this->CallWebService('TwitterSentimentAnalysis',$parameters);

        return $this->ParseReply($jsonreply);
    }


    /**
     * Asynchronous way of performing Sentiment Analysis on Twitter.
     *
     * @param string $data The results extracted from csv file that we evaluate.
     *
     * @return string|false It returns "positive", "negative" or "neutral" on success and false on fail.
     */
    function multiRequest($data, $options = array()) {
        // array of curl handles
        $curly = array();
        // data to be returned
        $result = array();

        // multi handle
        $mh = curl_multi_init();

        // loop through $data and create curl handles
        // then add them to the multi-handle
        foreach ($data as $id => $d) {

            $curly[$id] = curl_init();

            curl_setopt($curly[$id], CURLOPT_URL,            'http://api.datumbox.com/'.self::version.'/TwitterSentimentAnalysis.json');
            curl_setopt($curly[$id], CURLOPT_HEADER,         0);
            curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);

            // post?
            $POSTparameters['text'] = str_replace('@', "", $d['text']);
            $POSTparameters['api_key']=$this->api_key;
            if (is_array($d)) {
                if (!empty($d['text'])) {
                    curl_setopt($curly[$id], CURLOPT_POST,       1);
                    curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $POSTparameters);
                }
            }

            // extra options?
            if (!empty($options)) {
                curl_setopt_array($curly[$id], $options);
            }

            curl_multi_add_handle($mh, $curly[$id]);
        }

        // execute the handles
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while($running > 0);


        // get content and remove handles
        foreach($curly as $id => $c) {
            $result[$id] = $this->ParseReply(curl_multi_getcontent($c));
            print_r(curl_multi_getcontent($c));
            curl_multi_remove_handle($mh, $c);
        }

        // all done
        curl_multi_close($mh);

        return $result;
    }
}

