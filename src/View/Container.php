<?php

declare(strict_types=1);

namespace Dojo\View;

use Dojo\Exception\Exception;
use Dojo\Exception\InvalidArgumentException;
use Dojo\View\DojoInterface;
use Dojo\View\Helper\Dojo;
use Laminas\Config\Config;
use Laminas\Json\Json;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;

use function array_flip;
use function array_keys;
use function array_key_exists;
use function array_reverse;
use function array_unshift;
use function array_values;
use function htmlspecialchars;
use function in_array;
use function implode;
use function is_string;
use function ob_get_clean;
use function ob_start;
use function preg_match;
use function substr;
use function sprintf;
use function strrpos;
use function str_replace;
use function trim;


final class Container
{
    /** @var PhpRendererInterface|PhpRenderer $view */
    public $view;

    /**
     * addOnLoad capture lock
     * @var bool $captureLock
     */
    protected $captureLock = false;

    /**
     * addOnLoad object on which to apply lambda
     * @var string $captureObj
     */
    protected $captureObj;

    /**
     * Base CDN url to utilize
     * @var string $cdnBase
     */
    protected $cdnBase = DojoInterface::CDN_BASE_GOOGLE;

    /**
     * Path segment following version string of CDN path
     * @var string $cdnDojoPath
     */
    protected $cdnDojoPath = DojoInterface::CDN_DOJO_PATH_GOOGLE;

    /**
     * Dojo version to use from CDN
     * @var string $cdnVersion
     */
    protected $cdnVersion = '1.17.3';

    /**
     * Has the dijit loader been registered?
     * @var bool $dijitLoaderRegistered
     */
    protected $dijitLoaderRegistered = false;

    /**
     * Registered programmatic dijits
     * @var array $dijits
     */
    protected $dijits = [];

    /**
     * Dojo configuration
     * @var array $dojoConfig
     */
    protected $dojoConfig = [];

    /**
     * Whether or not dojo is enabled
     * @var bool $enabled
     */
    protected $enabled = false;

    /**
     * Are we rendering as XHTML?
     * @var bool $isXhtml
     */
    protected $isXhtml = false;

    /**
     * Arbitrary javascript to include in dojo script
     * @var array $javascriptStatements
     */
    protected $javascriptStatements = [];

    /**
     * Dojo layers (custom builds) to use
     * @var array $layers
     */
    protected $layers = [];

    /**
     * Relative path to dojo
     * @var string $localPath
     */
    protected $localPath = null;

    /**
     * Root of dojo where all dojo files are installed
     * @var string $localRelativePath
     */
    protected $localRelativePath = null;

    /**
     * Modules to require
     * @var array $modules
     */
    protected $modules = [
        "aurora/Aurora",
        "dojo/dom",
        "dojo/query",
        "dojo/Nodelist",
        "dojo/html",
        "dojo/topic",
        "dojo/request",
        "dojo/request/notify",
        "dijit/registry",
        "dijit/Dialog",
        "dijit/layout/BorderContainer",
        "dijit/layout/TabContainer",
        "dijit/form/Button",
        "dojox/layout/ContentPane",
        "dijit/layout/StackContainer",
        "dijit/layout/StackController",
        "dojo/parser",
        "dojox/form/Manager",
        "dijit/ProgressBar",
        "dojo/_base/array",
        "dojo/domReady!"
    ];

    /**
     * Registered module paths
     * @var array $modulePaths
     */
    protected $modulePaths = [];

    /**
     * Actions to perform on window load
     * @var array $onLoadActions
     */
    protected $onLoadActions = [];

    /**
     * Register the Dojo stylesheet?
     * @var bool $registerDojoStylesheet
     */
    protected $registerDojoStylesheet = false;

    /**
     * Style sheet modules to load
     * @var array $stylesheetModules
     */
    protected $stylesheetModules = [];

    /**
     * Local stylesheets
     * @var array $stylesheets
     */
    protected $stylesheets = [];

    /**
     * Array of onLoad events specific to Dojo integration operations
     * @var array $dojoLoadActions
     */
    protected $dojoLoadActions = [];

    /**
     * Set view object
     */
    public function setView(RendererInterface $view): void
    {
        $this->view = $view;
    }

    /**
     * Enable dojo
     */
    public function enable(): self
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * Disable dojo
     */
    public function disable(): self
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * Is dojo enabled?
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Add options for the Dojo Container to use
     *
     * @param array|Config
     */
    public function setOptions(array|config $options): self
    {
        if($options instanceof Config) {
            $options = $options->toArray();
        }

        foreach($options as $key => $value) {
            $key = strtolower($key);
            switch($key) {
                case 'requiremodules':
                    $this->requireModule($value);
                    break;
                case 'modulepaths':
                    foreach($value as $module => $path) {
                        $this->registerModulePath($module, $path);
                    }
                    break;
                case 'layers':
                    $value = (array) $value;
                    foreach($value as $layer) {
                        $this->addLayer($layer);
                    }
                    break;
                case 'cdnbase':
                    $this->setCdnBase($value);
                    break;
                case 'cdnversion':
                    $this->setCdnVersion($value);
                    break;
                case 'cdndojopath':
                    $this->setCdnDojoPath($value);
                    break;
                case 'localpath':
                    $this->setLocalPath($value);
                    break;
                case 'dojoConfig':
                    $this->setDojoConfig($value);
                    break;
                case 'stylesheetmodules':
                    $value = (array) $value;
                    foreach($value as $module) {
                        $this->addStylesheetModule($module);
                    }
                    break;
                case 'stylesheets':
                    $value = (array) $value;
                    foreach($value as $stylesheet) {
                        $this->addStylesheet($stylesheet);
                    }
                    break;
                case 'registerdojostylesheet':
                    $this->registerDojoStylesheet($value);
                    break;
                case 'enable':
                    if($value) {
                        $this->enable();
                    } else {
                        $this->disable();
                    }
            }
        }

        return $this;
    }

    /** Specify one or multiple modules to require */
    public function requireModule(string|array $modules): self
    {
        if (! is_string($modules) && ! is_array($modules)) {
            throw new InvalidArgumentException('Invalid module name specified; must be a string or an array of strings');
        }

        $modules = (array) $modules;

        foreach ($modules as $mod) {
            if (! preg_match('/^[a-z][a-z0-9._-]+$/i', $mod)) {
                throw new Exception(sprintf('Module name specified, "%s", contains invalid characters', (string) $mod));
            }

            if (! in_array($mod, $this->modules)) {
                $this->modules[] = $mod;
            }
        }

        return $this;
    }

    /** Retrieve list of modules to require */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * Register a module path
     * The module to register a path for
     * @param  string $module
     *
     * The path to register for the module
     * @param  string $path
     */
    public function registerModulePath(string $module, string $path): self
    {
        $path = (string) $path;
        if (!in_array($module, $this->modulePaths)) {
            $this->modulePaths[$module] = $path;
        }

        return $this;
    }

    /** List registered module paths */
    public function getModulePaths(): array
    {
        return $this->modulePaths;
    }

    /** Add layer (custom build) path */
    public function addLayer(string $path): self
    {
        $path = (string) $path;
        if (!in_array($path, $this->layers)) {
            $this->layers[] = $path;
        }
        return $this;
    }

    /** Get registered layers */
    public function getLayers(): array
    {
        return $this->layers;
    }

    /** Remove a registered layer */
    public function removeLayer(string $path): self
    {
        $path = (string) $path;
        $layers = array_flip($this->layers);
        if (array_key_exists($path, $layers)) {
            unset($layers[$path]);
            $this->layers = array_keys($layers);
        }
        return $this;
    }

    /** Clear all registered layers */
    public function clearLayers(): self
    {
        $this->layers = [];
        return $this;
    }

    /** Set CDN base path */
    public function setCdnBase(string $url): self
    {
        $this->cdnBase = (string) $url;
        return $this;
    }

    /** Return CDN base URL */
    public function getCdnBase(): string
    {
        return $this->cdnBase;
    }

    /**  Use CDN, using version specified */
    public function setCdnVersion(?string $version = null): self
    {
        $this->enable();
        if (preg_match('/^[1-9]\.[0-9](\.[0-9])?$/', $version)) {
            $this->cdnVersion = $version;
        }
        return $this;
    }

    /** Get CDN version */
    public function getCdnVersion(): string
    {
        return $this->cdnVersion;
    }

    /** Set CDN path to dojo (relative to CDN base + version) */
    public function setCdnDojoPath(string $path): self
    {
        $this->cdnDojoPath = $path;
        return $this;
    }

    /** Get CDN path to dojo (relative to CDN base + version) */
    public function getCdnDojoPath(): string
    {
        return $this->cdnDojoPath;
    }

    /** Are we using the CDN? */
    public function useCdn(): bool
    {
        return ! $this->useLocalPath();
    }

    /** Set path to local dojo */
    public function setLocalPath(string $path): self
    {
        $this->enable();
        $this->localPath = $path;
        return $this;
    }

    /** Get local path to dojo */
    public function getLocalPath(): string
    {
        return $this->localPath;
    }

    /** Are we using a local path? */
    public function useLocalPath(): bool
    {
        return (null === $this->localPath) ? false : true;
    }

    /** @param array<string, mixed> $config */
    public function setDojoConfig(array $config): self
    {
        $this->dojoConfig = $config;
        return $this;
    }

    /** Set Dojo configuration option */
    public function setDojoConfigOption(string $option, mixed $value): self
    {
        $this->dojoConfig[$option] = $value;
        return $this;
    }

    /** Retrieve dojo configuration values */
    public function getDojoConfig(): array
    {
        return $this->dojoConfig;
    }

    /** Get dojo configuration value */
    public function getDojoConfigOption(string $option, $default = null): mixed
    {
        if (array_key_exists($option, $this->dojoConfig)) {
            return $this->dojoConfig[$option];
        }
        return $default;
    }

    /** Add a stylesheet by module name */
    public function addStylesheetModule(string $module): self
    {
        if (! preg_match('/^[a-z0-9]+\.[a-z0-9_-]+(\.[a-z0-9_-]+)*$/i', $module)) {
            throw new Exception('Invalid stylesheet module specified');
        }
        if (! in_array($module, $this->stylesheetModules)) {
            $this->stylesheetModules[] = $module;
        }
        return $this;
    }

    /** Get all stylesheet modules currently registered */
    public function getStylesheetModules(): array
    {
        return $this->stylesheetModules;
    }

    /** Add a stylesheet */
    public function addStylesheet(string $path): self
    {
        if (! in_array($path, $this->stylesheets)) {
            $this->stylesheets[] = $path;
        }
        return $this;
    }

    /**
     * Register the dojo.css stylesheet?
     *
     * With no arguments, returns the status of the flag; with arguments, sets
     * the flag and returns the object.
     */
    public function registerDojoStylesheet(?bool $flag = null): Container|Bool
    {
        if (null === $flag) {
             return $this->registerDojoStylesheet;
        }

        $this->registerDojoStylesheet = (bool) $flag;
        return $this;
    }

    /** Retrieve registered stylesheets */
    public function getStylesheets(): array
    {
        return $this->stylesheets;
    }

    /**
     * Add a script to execute onLoad
     *
     * dojo.addOnLoad accepts:
     * - function name
     * - lambda
     *
     * @param  string $callback Lambda
     */
    public function addOnLoad(string $callback): self
    {
        if (! in_array($callback, $this->onLoadActions, true)) {
            $this->onLoadActions[] = $callback;
        }
        return $this;
    }

    /**
     * Prepend an onLoad event to the list of onLoad actions
     *
     * @param  string $callback Lambda
     */
    public function prependOnLoad(string $callback): self
    {
        if (! in_array($callback, $this->onLoadActions, true)) {
            array_unshift($this->onLoadActions, $callback);
        }
        return $this;
    }

    /** Retrieve all registered onLoad actions */
    public function getOnLoadActions(): array
    {
        return $this->onLoadActions;
    }

    /**
     * Start capturing routines to run onLoad
     *
     * @return bool
     */
    public function onLoadCaptureStart()
    {
        if ($this->captureLock) {
            throw new Exception('Cannot nest onLoad captures');
        }

        $this->captureLock = true;
        ob_start();
        return;
    }

    /** Stop capturing routines to run onLoad */
    public function onLoadCaptureEnd(): bool
    {
        $data               = ob_get_clean();
        $this->captureLock = false;

        $this->addOnLoad($data);
        return true;
    }

    /** Add a programmatic dijit */
    public function addDijit(string $id, array $params): self
    {
        if (array_key_exists($id, $this->dijits)) {
            throw new Exception(sprintf('Duplicate dijit with id "%s" already registered', $id));
        }

        $this->dijits[$id] = array(
            'id'     => $id,
            'params' => $params,
        );

        return $this;
    }

    /** Set a programmatic dijit (overwrites) */
    public function setDijit(string $id, array $params): self
    {
        $this->removeDijit($id);
        return $this->addDijit($id, $params);
    }

    /**
     * Add multiple dijits at once
     *
     * Expects an array of id => array $params pairs
     */
    public function addDijits(array $dijits): self
    {
        foreach ($dijits as $id => $params) {
            $this->addDijit($id, $params);
        }
        return $this;
    }

    /**
     * Set multiple dijits at once (overwrites)
     *
     * Expects an array of id => array $params pairs
     */
    public function setDijits(array $dijits): self
    {
        $this->clearDijits();
        return $this->addDijits($dijits);
    }

    /** Is the given programmatic dijit already registered? */
    public function hasDijit(string $id): bool
    {
        return array_key_exists($id, $this->dijits);
    }

    /** Retrieve a dijit by id */
    public function getDijit(string $id): array|null
    {
        if ($this->hasDijit($id)) {
            return $this->dijits[$id]['params'];
        }
        return null;
    }

    /**
     * Retrieve all dijits
     *
     * Returns dijits as an array of assoc arrays
     */
    public function getDijits(): array
    {
        return array_values($this->dijits);
    }

    /** Remove a programmatic dijit if it exists */
    public function removeDijit(string $id): self
    {
        if (array_key_exists($id, $this->dijits)) {
            unset($this->dijits[$id]);
        }

        return $this;
    }

    /** Clear all dijits */
    public function clearDijits(): self
    {
        $this->dijits = [];
        return $this;
    }

    /** Render dijits as JSON structure */
    public function dijitsToJson(): string
    {
        return Json::encode($this->getDijits(), false, ['enableJsonExprFinder' => true]);
    }

    /** Create dijit loader functionality */
    public function registerDijitLoader(): void
    {
        if (! $this->dijitLoaderRegistered) {
            $js =<<<EOJ
            function() {
                arrayUtil.forEach(auroraDijits, function(info) {
                    var n = dom.byId(info.id);
                    if (null != n) {
                        dom.attr(n, dojo.mixin({ id: info.id }, info.params));
                    }
                });
                parser.parse();
            }
            EOJ;
            $this->requireModule('dojo.parser');
            $this->addDojoLoad($js); // TODO track down this method call
            $this->addJavascript('let auroraDijits = ' . $this->dijitsToJson() . ';');
            $this->dijitLoaderRegistered = true;
        }
    }

    /** Add arbitrary javascript to execute in dojo JS container */
    public function addJavascript(string $js): self
    {
        $js = trim($js);
        if (! in_array(substr($js, -1), [';', '}'])) {
            $js .= ';';
        }

        if (in_array($js, $this->javascriptStatements)) {
            return $this;
        }

        $this->javascriptStatements[] = $js;
        return $this;
    }

    /** Return all registered javascript statements */
    public function getJavascript(): array
    {
        return $this->javascriptStatements;
    }

    /** Clear arbitrary javascript stack  */
    public function clearJavascript(): self
    {
        $this->javascriptStatements = array();
        return $this;
    }

    /** Capture arbitrary javascript to include in dojo script */
    public function javascriptCaptureStart(): void
    {
        if ($this->captureLock) {
            throw new Exception('Cannot nest captures');
        }

        $this->captureLock = true;
        ob_start();
        return;
    }

    /** Finish capturing arbitrary javascript to include in dojo script */
    public function javascriptCaptureEnd(): bool
    {
        $data               = ob_get_clean();
        $this->captureLock = false;

        $this->addJavascript($data);
        return true;
    }

    /**
     * String representation of dojo environment
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $this->isXhtml = $this->view->doctype()->isXhtml();

        if (Dojo::useDeclarative()) {
            if (null === $this->getDojoConfigOption('parseOnLoad')) {
                $this->setDojoConfigOption('parseOnLoad', true);
            }
        }

        if (! empty($this->dijits)) {
            $this->registerDijitLoader();
        }

        $html  = $this->renderStylesheets() . PHP_EOL
               . $this->renderDojoConfig() . PHP_EOL
               . $this->renderDojoScriptTag() . PHP_EOL
               . $this->renderLayers() . PHP_EOL
               . $this->renderExtras();
        return $html;
    }

    /** Retrieve local path to dojo resources for building relative paths */
    protected function getLocalRelativePath(): string
    {
        if (null === $this->localRelativePath) {
            $localPath = $this->getLocalPath();
            $localPath = preg_replace('|[/\\\\]dojo[/\\\\]dojo.js[^/\\\\]*$|i', '', $localPath);
            $this->localRelativePath = $localPath;
        }
        return $this->localRelativePath;
    }

    /**
     * Render dojo stylesheets
     *
     * @return string
     */
    protected function renderStylesheets()
    {
        if ($this->useCdn()) {
            $base = $this->getCdnBase()
                  . $this->getCdnVersion();
        } else {
            $base = $this->getLocalRelativePath();
        }

        $registeredStylesheets = $this->getStylesheetModules();
        foreach ($registeredStylesheets as $stylesheet) {
            $themeName     = substr($stylesheet, strrpos($stylesheet, '.') + 1);
            $stylesheet    = str_replace('.', '/', $stylesheet);
            $stylesheets[] = $base . '/' . $stylesheet . '/' . $themeName . '.css';
        }

        foreach ($this->getStylesheets() as $stylesheet) {
            $stylesheets[] = $stylesheet;
        }

        if ($this->registerDojoStylesheet) {
            $stylesheets[] = $base . '/dojo/resources/dojo.css';
        }

        if (empty($stylesheets)) {
            return '';
        }

        array_reverse($stylesheets);
        $style = '<style type="text/css">' . PHP_EOL
               . (($this->isXhtml) ? '<!--' : '<!--') . PHP_EOL;
        foreach ($stylesheets as $stylesheet) {
            $style .= '    @import "' . $stylesheet . '";' . PHP_EOL;
        }
        $style .= (($this->isXhtml) ? '-->' : '-->') . PHP_EOL
                . '</style>';

        return $style;
    }

    /** Render dojoConfig values */
    protected function renderDojoConfig(): string
    {
        $dojoConfigValues = $this->getDojoConfig();
        if (empty($dojoConfigValues)) {
            return '';
        }

        $scriptTag = '<script type="text/javascript">' . PHP_EOL
                   . (($this->isXhtml) ? '//<![CDATA[' : '//<!--') . PHP_EOL
                   . '    var dojoConfig = ' . Json::encode($dojoConfigValues) . ';' . PHP_EOL
                   . (($this->isXhtml) ? '//]]>' : '//-->') . PHP_EOL
                   . '</script>';

        return $scriptTag;
    }

    /**
     * Render dojo script tag
     *
     * Renders Dojo script tag by utilizing either local path provided or the
     * CDN. If any dojoConfig values were set, they will be serialized and passed
     * with that attribute.
     */
    protected function renderDojoScriptTag(): string
    {
        if ($this->useCdn()) {
            $source = $this->getCdnBase()
                    . $this->getCdnVersion()
                    . $this->getCdnDojoPath();
        } else {
            $source = $this->getLocalPath();
        }

        $scriptTag = '<script type="text/javascript" src="' . $source . '"></script>';
        return $scriptTag;
    }

    /** Render layers (custom builds) as script tags */
    protected function renderLayers(): string
    {
        $layers = $this->getLayers();
        if (empty($layers)) {
            return '';
        }

        $enc = 'UTF-8';
        if ($this->view instanceof RendererInterface
            && method_exists($this->view, 'getEncoding')
        ) {
            $enc = $this->view->getEncoding();
        }

        $html = array();
        foreach ($layers as $path) {
            $html[] = sprintf(
                '<script type="text/javascript" src="%s"></script>',
                htmlspecialchars($path, ENT_QUOTES, $enc)
            );
        }

        return implode("\n", $html);
    }

    /** Render dojo module paths and requires */
    protected function renderExtras(): string
    {
        $js = [];
        $modulePaths = $this->getModulePaths();
        if (!empty($modulePaths)) {
            foreach ($modulePaths as $module => $path) {
                $js[] =  'dojo.registerModulePath("' . $module . '", "' . $path . '");';
            }
        }

        $modules = $this->getModules();
        if (!empty($modules)) {
            foreach ($modules as $module) {
                $js[] = 'dojo.require("' . $module . '");';
            }
        }

        $onLoadActions = array();
        // Get Dojo specific onLoad actions; these will always be first to
        // ensure that dijits are created in the correct order
        foreach ($this->getDojoLoadActions() as $callback) {
            $onLoadActions[] = 'dojo.addOnLoad(' . $callback . ');';
        }

        // Get all other onLoad actions
        foreach ($this->getOnLoadActions() as $callback) {
            $onLoadActions[] = 'dojo.addOnLoad(' . $callback . ');';
        }

        $javascript = implode("\n    ", $this->getJavascript());

        $content = '';
        if (! empty($js)) {
            $content .= implode("\n    ", $js) . "\n";
        }

        if (! empty($onLoadActions)) {
            $content .= implode("\n    ", $onLoadActions) . "\n";
        }

        if (! empty($javascript)) {
            $content .= $javascript . "\n";
        }

        if (preg_match('/^\s*$/s', $content)) {
            return '';
        }

        $html = '<script type="text/javascript">' . PHP_EOL
              . (($this->isXhtml) ? '//<![CDATA[' : '//<!--') . PHP_EOL
              . $content
              . (($this->isXhtml) ? '//]]>' : '//-->') . PHP_EOL
              . PHP_EOL . '</script>';
        return $html;
    }

    /**
     * Add an onLoad action related to Dj dijit creation
     *
     * This method is public, but prefixed with an underscore to indicate that
     * it should not normally be called by userland code. It is pertinent to
     * ensuring that the correct order of operations occurs during dijit
     * creation.
     */
    public function addDojoLoad(string $callback): self
    {
        if (!in_array($callback, $this->dojoLoadActions, true)) {
            $this->dojoLoadActions[] = $callback;
        }
        return $this;
    }

    /** Retrieve all Dj dijit callbacks */
    public function getDojoLoadActions(): array
    {
        return $this->dojoLoadActions;
    }
}
