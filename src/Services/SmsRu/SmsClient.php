<?php


namespace Kirshin\Services\SmsRu;


use JsonException;

class SmsClient
{
    private int $partnerId = 277296;
    private string $apiId;
    private string $baseUri;
    private string $message;
    private array $data;
    private mixed $phone;
    private mixed $balance;
    private mixed $body;
    private mixed $ch;

    public mixed $json;
    public mixed $statusCode;
    public mixed $statusText;

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
     * @param mixed $phone
     * @param string $message
     * @throws JsonException
     */
    public function sendSms (mixed $phone, string $message): void
    {
        $this->setPhone($phone);
        $this->setMessage($message);

        foreach ($this->phone as $value) {
            $this->data['multi'][(string) $value] = $this->message;
        }

        $this->build();
    }

    /**
     * build message
     * @throws JsonException
     */
    private function build (): void
    {
        $this->ch = curl_init($this->baseUri);

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS,
            http_build_query(
                [
                    'multi' => $this->data['multi'],
                    'api_id' => $this->apiId,
                    'partner_id' => $this->partnerId,
                    'json' => 1
                ]
            ));

        $this->body = curl_exec($this->ch);

        curl_close($this->ch);

        $this->json = json_decode($this->body, false, 512, JSON_THROW_ON_ERROR);

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
    public function setApiId(string $apiId): void
    {
        $this->apiId = $apiId;
    }

    /**
     * @param string $baseUri
     */
    public function setBaseUri(string $baseUri): void
    {
        $this->baseUri = $baseUri;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone(mixed $phone): void
    {
        $this->phone = explode(',', $phone);
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @param array $data
     */
    public function setData(array $data = []): void
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getBalance(): mixed
    {
        return $this->balance;
    }

    /**
     * @param mixed $balance
     */
    public function setBalance(mixed $balance): void
    {
        $this->balance = $balance;
    }

    /**
     * @return mixed
     */
    public function getStatusCode(): mixed
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $statusCode
     */
    public function setStatusCode(mixed $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return mixed
     */
    public function getStatusText(): mixed
    {
        return $this->statusText;
    }

    /**
     * @param mixed $statusText
     */
    public function setStatusText(mixed $statusText): void
    {
        $this->statusText = $statusText;
    }
}