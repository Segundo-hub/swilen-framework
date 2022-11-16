<?php

namespace Swilen\Shared\Support;

interface MessageBag extends Arrayable, \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * Get the keys present in the message bag.
     *
     * @return array
     */
    public function keys();

    /**
     * Add a message to the bag.
     *
     * @param string $key
     * @param string $message
     *
     * @return $this
     */
    public function add($key, $message);

    /**
     * Merge a new array of messages into the bag.
     *
     * @param array $messages
     *
     * @return $this
     */
    public function merge($messages);

    /**
     * Determine if messages exist for a given key.
     *
     * @param string|array $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Get the first message from the bag for a given key.
     *
     * @param string      $key
     * @param string|null $format
     *
     * @return string
     */
    public function first($key, $format = null);

    /**
     * Get all of the messages from the bag for a given key.
     *
     * @param string      $key
     * @param string|null $format
     *
     * @return array
     */
    public function get($key, $format = null);

    /**
     * Get all of the messages for every key in the bag.
     *
     * @param string|null $format
     *
     * @return array
     */
    public function all($format = null);

    /**
     * Get the raw messages in the container.
     *
     * @return array
     */
    public function getMessages();

    /**
     * Get the default message format.
     *
     * @return string
     */
    public function getFormat();

    /**
     * Determine if the message bag has any messages.
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Determine if the message bag contains messages.
     *
     * @return bool
     */
    public function isNotEmpty();
}
