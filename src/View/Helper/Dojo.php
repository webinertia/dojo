<?php

/** setup and configure like a standard view helper */

declare(strict_types=1);

namespace Dojo\View\Helper;

use Dojo\View\Container;
use Dojo\View\Exception;
use Laminas\View\Helper\AbstractHelper;

class Dojo extends AbstractHelper
{
    public const PROGRAMMATIC_SCRIPT = 1;
    public const PROGRAMMATIC_NOSCRIPT = -1;

    /**
     * @var Container $container
     */
    protected $container;

    /**
     * Whether or not dijits should be declared programmatically
     * @var bool $useProgrammatic
     */
    protected static $useProgrammatic = true;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke()
    {
        $this->container->setView($this->getView());
        return $this->container;
    }

    /**
     * Proxy to container methods
     * @throws Exception For invalid method calls
     */
    public function __call(string $method, array $args): mixed
    {
        if (!method_exists($this->container, $method)) {
            throw new Exception(sprintf('Invalid method "%s" called on dojo view helper', $method));
        }

        return call_user_func_array([$this->container, $method], $args);
    }

    /**
     * Set whether or not dijits should be created declaratively
     */
    public static function setUseDeclarative(): void
    {
        self::$useProgrammatic = false;
    }

    /**
     * Set whether or not dijits should be created programmatically
     *
     * Optionally, specifiy whether or not dijit helpers should generate the
     * programmatic dojo.
     */
    public static function setUseProgrammatic(int $style = self::PROGRAMMATIC_SCRIPT): void
    {
        if (!in_array($style, array(self::PROGRAMMATIC_SCRIPT, self::PROGRAMMATIC_NOSCRIPT))) {
            $style = self::PROGRAMMATIC_SCRIPT;
        }
        self::$useProgrammatic = $style;
    }

    /**
     * Should dijits be created declaratively?
     */
    public static function useDeclarative(): bool
    {
        return (false === self::$useProgrammatic);
    }

    /**
     * Should dijits be created programmatically?
     */
    public static function useProgrammatic(): bool
    {
        return (false !== self::$useProgrammatic);
    }

    /**
     * Should dijits be created programmatically but without scripts?
     */
    public static function useProgrammaticNoScript(): bool
    {
        return (self::PROGRAMMATIC_NOSCRIPT === self::$useProgrammatic);
    }
}
