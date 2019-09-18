<?php


namespace TdWooCheckout\src\api\client;


use Exception;

class ApiRequest
{
    protected $request_options;
    protected $request_result;

    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_PUT = 'PUT';

    /**
     * Api_Client_Base constructor.
     */
    public function __construct()
    {
        $this->request_options = array(
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_SSL_VERIFYHOST=>0,
            CURLOPT_ENCODING =>"",
            CURLOPT_MAXREDIRS =>10,
            CURLOPT_TIMEOUT =>30,
            CURLOPT_HTTP_VERSION =>CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER=>array(
                "cache-control:no-cache",
                'Content-Type:application/json',
            ),

        );
    }

    public function initAuthorization($token)
    {
        array_push($this->request_options[CURLOPT_HTTPHEADER],'Authorization: Bearer '.$token);
    }
    public function post($endpoint,$params,$headers=null)
    {
        return $this->run($endpoint,$params,self::HTTP_METHOD_POST,$headers);
    }

    public function getEx($token,$endpoint,$params=null,$headers=null)
    {
        array_push($this->request_options[CURLOPT_HTTPHEADER],'Authorization: Bearer '.$token);
        return $this->get($endpoint,$params,$headers);
    }
    public function get($endpoint,$params=null,$headers=null)
    {
        return $this->run($endpoint,$params,self::HTTP_METHOD_GET,$headers);
    }

    public function put($endpoint,$params=null,$headers=null)
    {
        return $this->run($endpoint,$params,self::HTTP_METHOD_PUT,$headers);
    }

    private function run($endpoint,$params=array(),$method=self::HTTP_METHOD_GET,array $headers=null)
    {
        switch($method)
        {
            case self::HTTP_METHOD_POST:
                $this->request_options[CURLOPT_POSTFIELDS] = json_encode($params);
                $this->request_options[CURLOPT_POST] = 1;
                break;
            case self::HTTP_METHOD_GET:
                unset($this->request_options[CURLOPT_POSTFIELDS]);
                unset($this->request_options[CURLOPT_POST]);
                if(is_array($params) && count($params)>0)
                {
                    $endpoint .= '?'.http_build_query($params,null,'&');
                }elseif($params)
                {
                    $endpoint .= '?'.$params;
                }
                break;
            case self::HTTP_METHOD_PUT:
                $this->request_options[CURLOPT_POSTFIELDS] = json_encode($params);
                $this->request_options[CURLOPT_CUSTOMREQUEST] = self::HTTP_METHOD_PUT;
                break;
        }

        if(is_array($headers))
        {
            $h = array();
            foreach($headers as $key=>$val)
            {
                $h[] = "$key:$val";
            }
            $this->request_options[CURLOPT_HTTPHEADER] = array_merge($this->request_options[CURLOPT_HTTPHEADER],$h);
        }

        $this->request($endpoint);
        return json_decode($this->request_result);
    }

    private function request($url)
    {
        try {
            $ch = curl_init($url);
            if (FALSE === $ch)
                throw new Exception('Failed to initialize');
            curl_setopt_array($ch, $this->request_options);
            $this->request_result = curl_exec($ch);

            if (FALSE === $this->request_result)
                throw new Exception(curl_error($ch), curl_errno($ch));
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (200 != $http_status && 201 != $http_status) {
                throw new Exception($this->request_result, $http_status);
            }
            curl_close($ch);
        }catch(Exception $e) {
            $error_message = json_decode($e->getMessage());

            throw new Exception(sprintf('Error %s',   $e->getMessage()));

        } catch(Exception $e) {
            trigger_error(sprintf('Curl failed with error #%d: %s',$e->getCode(), $e->getMessage()),E_USER_ERROR);
        }
    }
}