<?php
namespace Local\Integration;

use Bitrix\Main\Config\Configuration;

class B24Integration
{
    private static $instance;
    private $config;

    private function __construct()
    {
        $this->config = Configuration::getInstance()->get('b24_integration');
    }

    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function crmLeadFields()
    {
        $result = false;

        try {
            $result = $this->call('crm.lead.fields', [], 'get');
        } catch (\Exception $e) {
            // do some log hear if you need to
        }

        return $result;
    }

    public function crmLeadAdd($fields)
    {
        $result = false;

        try {
            $requiredFieldNames = ['TITLE'];
            $availableFieldNames = ['TITLE', 'NAME', 'LAST_NAME', 'EMAIL', 'PHONE', 'COMMENTS'];

            foreach ($requiredFieldNames as $fieldName) {
                $emptyFields = [];
                if (empty($fields[$fieldName])) {
                    $emptyFields[] = $fieldName;
                }

                if (!empty($emptyFields)) {
                    throw new \Exception('Не заполнены обязательные поля: ' . implode('; ', $emptyFields));
                }
            }

            $dataFields = [];
            foreach ($availableFieldNames as $availableFieldName) {
                if (!isset($fields[$availableFieldName])) {
                    continue;
                }

                switch ($availableFieldName) {
                    case 'EMAIL':
                    case 'PHONE':
                        $value = [['VALUE' => strip_tags($fields[$availableFieldName]), 'VALUE_TYPE' => 'WORK',]];
                        break;
                    default:
                        $value = strip_tags($fields[$availableFieldName]);
                }

                $dataFields[$availableFieldName] = $value;
            }

            $params = [
                'fields' => $dataFields,
                'params' => [
                    'REGISTER_SONET_EVENT' => 'Y',
                ]
            ];

            $result = $this->call('crm.lead.add', $params);
        } catch (\Exception $e) {
            // do some log hear if you need to
        }

        return $result;
    }

    private function call($method, $params = [], $requestMethod = 'post')
    {
        $url = $this->config['host']
            . $this->config['rest_path']
            . '/' . $this->config['hook_code']
            . '/' . $method . '.json';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
	    ]);

        if ($requestMethod === 'post') {
            curl_setopt($curl, CURLOPT_POST, 1);
        }

        if (!empty($params)) {
            $paramsJson = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-type: application/json; charset=utf-8',]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $paramsJson);
        }

        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            throw new \Exception('Ошибка запроса: ' . curl_error($curl));
        }
        curl_close($curl);

        $result = json_decode($result, true);

        if (!$result) {
            throw new \Exception('Пустой ответ от сервер');
        }

        return $result;
    }
}
