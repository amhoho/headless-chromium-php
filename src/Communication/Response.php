<?php
/**
 * @license see LICENSE
 */

namespace HeadlessChromium\Communication;

class Response implements \ArrayAccess
{

    protected $message;

    protected $data;

    /**
     * Response constructor.
     * @param $data
     */
    public function __construct(array $data, Message $message)
    {
        $this->data = $data;
        $this->message = $message;
    }

    /**
     * True if the response is error free
     * @return bool
     */
    public function isSuccessful()
    {
        return !array_key_exists('error', $this->data);
    }

    /**
     * Get the error message if set.
     * @return string|null
     */
    public function getErrorMessage(bool $extended = true)
    {
        $message = [];

        if ($extended && isset($this->data['error']['code'])) {
            $message[] = $this->data['error']['code'];
        }

        if (isset($this->data['error']['message'])) {
            $message[] = $this->data['error']['message'];
        }

        if ($extended && isset($this->data['error']['data']) && is_string($this->data['error']['data'])) {
            $message[] = $this->data['error']['data'];
        }

        return implode(' - ', $message);
    }

    /**
     * Get the error code if set.
     * @return string|null
     */
    public function getErrorCode()
    {
        return $this->data['error']['code'] ?? null;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getResultData($name)
    {
        return $this->data['result'][$name] ?? null;
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * The data returned by chrome dev tools
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        throw new \Exception('Responses are immutable');
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        throw new \Exception('Responses are immutable');
    }
}
