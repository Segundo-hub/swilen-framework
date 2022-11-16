<?php

namespace Swilen\Routing;

final class RouteAction
{
    /**
     * Parse the given action into an array.
     *
     * @param string $uri
     * @param mixed  $action
     *
     * @return array
     */
    public static function parse($uri, $action)
    {
        // Return missing action when action is null
        // also missing when it is an empty array
        if (is_null($action) || empty($action)) {
            return static::missingAction($uri);
        }

        if (static::isCallable($action, true)) {
            $action = !is_array($action) ? ['uses' => $action] : [
                'uses' => $action[0].'@'.$action[1],
                'controller' => $action[0].'@'.$action[1],
            ];

            if (is_string($action['uses']) && strpos($action['uses'], '@') !== false) {
                $action['controller'] = $action['uses'];
            }
        } elseif (!isset($action['uses'])) {
            $action['uses'] = $action[0];
        }

        if (is_string($action['uses']) && !strpos($action['uses'], '@') !== false) {
            $action['uses'] = static::makeInvokableController($action['uses']);
        }

        return $action;
    }

    /**
     * Get an action for a route that has no action.
     *
     * @param string $uri
     *
     * @return array
     *
     * @throws \LogicException
     */
    private static function missingAction($uri)
    {
        return ['uses' => function () use ($uri) {
            throw new \LogicException("Missing action for route: [{$uri}].");
        }];
    }

    /**
     * Determine action is closure.
     *
     * @param mixed $action
     * @param bool  $check
     *
     * @return bool
     */
    private static function isCallable($action, bool $check = false)
    {
        if (!is_array($action)) {
            return is_callable($action, $check);
        }

        if (
            (!isset($action[0]) || !isset($action[1])) ||
            !is_string($action[1] ?? null)
        ) {
            return false;
        }

        if (
            $check && (is_string($action[0]) || is_object($action[0])) &&
            is_string($action[1])
        ) {
            return true;
        }

        $class = is_object($action[0]) ? get_class($action[0]) : $action[0];

        return class_exists($class);
    }

    /**
     * Make an action for an invokable controller.
     *
     * @param string $action
     *
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    private static function makeInvokableController($action)
    {
        if (!method_exists($action, '__invoke')) {
            throw new \UnexpectedValueException("Invalid route action: [{$action}].");
        }

        return $action.'@__invoke';
    }

    /**
     * Parse controller action to array.
     *
     * @param string|array $action
     *
     * @return array
     */
    public static function parseControllerAction($action)
    {
        return strpos($action, '@') !== false ? explode('@', $action, 2) : $action;
    }
}
