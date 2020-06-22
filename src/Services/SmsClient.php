<?php


namespace Kirshin\Services;


class SmsClient
{
    private $baseUri;
    private $apiId;
    private $phone;
    private $message;
    private $body;
    private $data;
    private $balance;
    private $ch;
    public $json;
    public $statusCode;
    public $statusText;

    /**
     * SmsClient constructor.
     * @param string $apiId
     */
    public function __construct(string $apiId)
    {
        $this->setApiId($apiId);
        $this->setBaseUri('https://sms.ru/sms/send');
        $this->setData();
    }

    /**
     * @param string $phone
     * @param string $message
     */
    public function sendSms (string $phone, string $message) {
        $this->setPhone($phone);
        $this->setMessage($message);

        foreach ($this->phone as $value) {
            $this->data['multi'][(string) $value] = $this->message;
        }

        $this->build();
    }

    /**
     * build message
     */
    private function build () {
        $this->ch = curl_init($this->baseUri);

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS,
            http_build_query(
                [
                    'multi' => $this->data['multi'],
                    'api_id' => $this->apiId,
                    'json' => 1
                ]
            ));

        $this->body = curl_exec($this->ch);

        curl_close($this->ch);

        $this->json = json_decode($this->body, false);

        if ($this->json && $this->json->status === 'OK') {
            foreach ($this->json->sms as $phone => $data) {
                if ($data->status === 'OK') {
                    echo "Сообщение на номер $phone успешно отправлено\n";
                    echo "ID сообщения: $data->sms_id\n";
                } else {
                    echo "Сообщение на номер $phone не отправлено\n";
                    echo "Код ошибки: $data->status_code\n";
                    echo "Текст ошибки: $data->status_text\n";
                }
            }

            $this->setBalance($this->json->balance);

            echo "Баланс после отправки: $this->balance руб.\n";
        } else {

            $this->setStatusCode($this->json->status_code);
            $this->setStatusText($this->json->status_text);

            echo "Запрос не выполнился\n";
            echo "Код ошибки: $this->statusCode\n";
            echo "Текст ошибки: $this->statusText\n";
        }
    }

    /**
     * @param string $apiId
     */
    public function setApiId($apiId)
    {
        $this->apiId = $apiId;
    }

    /**
     * @param string $baseUri
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = explode(',', $phone);
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @param array $data
     */
    public function setData($data = [])
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param mixed $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return mixed
     */
    public function getStatusText()
    {
        return $this->statusText;
    }

    /**
     * @param mixed $statusText
     */
    public function setStatusText($statusText)
    {
        $this->statusText = $statusText;
    }

}
