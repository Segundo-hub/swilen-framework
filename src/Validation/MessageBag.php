<?php

namespace Swilen\Validation;

use Swilen\Shared\Support\MessageBag as MessageBagContract;

class MessageBag implements MessageBagContract
{
    /**
     * The messages bag.
     *
     * @var array<string, string[]>
     */
    protected $messages = [];

    /**
     * The allowed placeholders for replace in message.
     *
     * @var string[]
     */
    protected $placeholders = [
        Rule::PLACEHOLDER_ATTRIBUTE,
        Rule::PLACEHOLDER_VALUE,
        Rule::PLACEHOLDER_ALLOW,
    ];

    /**
     * Create a MessageBag instance with given messages.
     *
     * @param array<string, string> $messages
     *
     * @return void
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
    }

    /**
     * Get the keys present in the message bag.
     *
     * @return string[]
     */
    public function keys()
    {
        return array_keys($this->messages);
    }

    /**
     * Get first message from key and format.
     *
     * @param string      $key
     * @param string|null $format
     *
     * @return string
     */
    public function first($key, $format = null)
    {
        if (is_null($messages = $this->get($key, $format))) {
            return null;
        }

        return array_shift($messages);
    }

    /**
     * Get the first for every messages with format.
     *
     * @param string|null $format
     *
     * @return array<string, string>
     */
    public function firstOfAll($format = null)
    {
        $messages = [];

        foreach ($this->messages as $key => $value) {
            $messages[$key] = $this->first($key, $format);
        }

        return $messages;
    }

    /**
     * Add a new message to given message key.
     *
     * @param string $key
     * @param string $message
     *
     * @return void
     */
    public function add($key, $message)
    {
        $this->messages[$key][] = $message;
    }

    /**
     * Remove one message contains given key or keys.
     *
     * @param string[]|string $key
     *
     * @return void
     */
    public function remove($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $keyed) {
            unset($this->messages[$keyed]);
        }
    }

    /**
     * Merge a new array of messages into the bag.
     *
     * @param array $messages
     *
     * @return void
     */
    public function merge($messages)
    {
        $this->messages = array_merge($this->messages, $messages);
    }

    /**
     * Determine if messages exist for a given key.
     *
     * @param string|array $key
     *
     * @return bool
     */
    public function has($key)
    {
        return key_exists($key, $this->messages);
    }

    /**
     * Get all messages from key and format or null id not exists.
     *
     * @param string      $key
     * @param string|null $format
     *
     * @return string[]|null
     */
    public function get($key, $format = null)
    {
        if (!$this->has($key)) {
            return null;
        }

        $messages = $this->messages[$key];

        if ($format) {
            foreach ($messages as $key => &$message) {
                $messages[$key] = $this->formatted($message, $format);
            }
        }

        return $messages;
    }

    /**
     * Get the raw messages in the container.
     *
     * @return array<string, string[]>
     */
    public function getMessages()
    {
        return $this->all();
    }

    /**
     * Get the default message format.
     *
     * @return array
     */
    public function getFormat()
    {
        return $this->placeholders;
    }

    /**
     * Get formatted message with present :message placeholder.
     *
     * @param string $message
     * @param string $format
     *
     * @return string
     */
    protected function formatted(string $message, string $format)
    {
        return str_replace(':message', $message, $format);
    }

    /**
     * Get all of the messages for every key in the bag.
     *
     * @param string|null $format
     *
     * @return array
     */
    public function all($format = null)
    {
        if ($format === null) {
            return $this->messages;
        }

        $messages = [];

        foreach ($this->messages as $key => $values) {
            $messages[$key] = array_map(function ($message) use ($format) {
                return $this->formatted($message, $format);
            }, $values);
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * Determine if the message bag has any messages.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->messages) || $this->messages === [];
    }

    /**
     * Determine if the message bag contains messages.
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    /**
     * {@inheritdoc}
     *
     * @param string $offset
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $offset
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Get one message from given offset.
     *
     * @param string $offset
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set given value to given offset.
     *
     * @param string $offset
     * @param mixed  $value
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->add($offset, $value);
    }

    /**
     * Returns the number of messages saved.
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return \count($this->messages);
    }

    /**
     * Returns an iterator for messages saved.
     *
     * @return \ArrayIterator<string, string[]>
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->messages);
    }
}
