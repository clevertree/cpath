<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Build\API;

use CPath\Base;
use CPath\Compile;
use CPath\Config;
use CPath\Describable\IDescribable;
use CPath\Framework\Api\Field\Field;
use CPath\Framework\Api\Types\AbstractAPI;
use CPath\Framework\Api\Util\APIRenderUtil;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Types\DataResponse;
use CPath\Framework\Build\IBuildable;
use CPath\Framework\Build\IBuilder;
use CPath\Log;
use CPath\Route\IRoutable;
use CPath\Route\IRoute;
use CPath\Route\RoutableSet;
use CPath\Route\Route;

class Build extends AbstractAPI implements IRoutable {

    const ROUTE_PATH = '/build';    // Allow manual building from command line: 'php index.php build'
    const ROUTE_METHOD = 'CLI';    // CLI only
    const ROUTE_API_VIEW_TOKEN = false;   // Add an APIView route entry for this API


    const ROUTE_IGNORE_FILES = '.buildignore';
    const ROUTE_IGNORE_DIR = '.git|.idea';

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    protected function setupAPI(){
        $this->addField(new Field('v', "Display verbose messages"));
        $this->addField( new Field('s', "Skip broken files"));
        $this->addField(new Field('f', "Filter built files by wildcard *?"));
        //$this->generateFieldShorts();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRoute instance for this render which contains the request and args
     * @return DataResponse the api call response with data, message, and status
     */
    final function execute(IRequest $Request) {
        static $built = false;
        if($built)
            return new DataResponse(false, "Build can only occur once per execution. Skipping Build...");
        $built = true;

        if(!empty($Request['v']))
            Log::setDefaultLevel(4);
        if(!empty($Request['s']))
            self::$mSkipBroken = true;
        if(!empty($Request['f']))
            self::$mFilter = $Request['f'];

        $Response = new DataResponse(false, "Starting Build");
        $exCount = Build::build(true);
        if($exCount)
            return $Response
                ->update(false, "Build Failed: {$exCount} Exception(s) Occurred");
        return $Response
            ->update(true, "Build Complete");
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Build All classes";
    }

    /**
     * Returns the route for this IRender
     * @return IRoute|RoutableSet a new IRoute (typically a RouteableSet) instance
     */
    function loadRoute() {
        return Route::fromHandler($this, static::ROUTE_METHOD, static::ROUTE_PATH);
    }

    // Statics

    /** @var IBuilder[] $mBuilders */
    private static $mBuilders = array();
    private static $mClassFiles = array();
    /** @var \ReflectionClass[] $mBuilders */
    private static $mClasses = array();
    private static $mBuildConfig = NULL;
    private static $mForce = false;
    private static $mSkipBroken = false;
    private static $mFilter = false;
    private static $mBrokenFiles = array();

    /**
     * Return the build config full path
     * @return string build config full path
     */
    private static function getConfigPath() {
        static $path = NULL;
        return $path ?: $path = Config::getGenPath().'build.gen.php';
    }

    /**
     * Load build config data for a class
     * @param null $class optional class to load data for
     * @return mixed build config data
     */
    private static function &loadConfig($class=NULL) {
        if(self::$mBuildConfig === NULL) {
            $path = self::getConfigPath();
            $config = array();
            if(file_exists($path))
                include ($path);
            self::$mBuildConfig = $config;
        }
        if(!$class) return self::$mBuildConfig;
        if(is_object($class)) $class = get_class($class);
        if(!isset(self::$mBuildConfig[$class])) self::$mBuildConfig[$class] = array();
        return self::$mBuildConfig[$class];
    }

    /**
     * Load build config data for a class
     * @param $class string class to load data for
     * @param null $key if provided, only return data for this key
     * @return mixed build config data
     */
    public static function &getConfig($class, $key=NULL) {
        $Config =& self::loadConfig($class);
        if($key) return $Config[$key];
        return $Config;
    }

    /**
     * Set build config data for a class
     * @param string $class class to load data for
     * @param string $key data key
     * @param string $val data value
     * @param boolean $commit data value
     */
    public static function setConfig($class, $key, $val, $commit=false) {
        $Config =& self::loadConfig($class);
        $Config[$key] = $val;
        if($commit)
            self::commitConfig();
    }

    /**
     * Commit the build config to the file
     */
    public static function commitConfig() {
        $config =& self::loadConfig();
        $php = "<?php\n\$config=".var_export($config, true).";";
        $path = self::getConfigPath();
        if(!is_dir(dirname($path)))
            mkdir(dirname($path), NULL, true);
        file_put_contents($path, $php);
    }

    /**
     * Returns true if the current build should be forced
     * @return bool whether or not to force build
     */
    public static function force() { return self::$mForce; }

    public static function buildDomainPath() {
        return 'http://'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : gethostname()).'/';
    }

    /**
     * Build all classes
     */
    public static function build($force=false) {
        Log::v(__CLASS__, "Starting Builds");
        if(!Config::$BuildEnabled) {
            Log::e(__CLASS__, "Building is not allowed. Config::\$BuildEnabled==false");
            return 0;
        }

        self::$mBrokenFiles = self::getConfig(__CLASS__, 'brokenFiles') ?: array();
        if(self::$mSkipBroken) {
            if($lastFile = self::getConfig(__CLASS__, 'lastFile')) {
                if(!in_array($lastFile, self::$mBrokenFiles)) {
                    self::$mBrokenFiles[] = $lastFile;
                    self::setConfig(__CLASS__, 'brokenFiles', self::$mBrokenFiles);
                    Log::v(__CLASS__, "Adding broken file to skip list: {$lastFile}");
                }
            }
            if(self::$mBrokenFiles) {
                Log::v(__CLASS__, "Skipping (%s) broken files(es)", sizeof(self::$mBrokenFiles));
                foreach(self::$mBrokenFiles as $classFile)
                    Log::v2(__CLASS__, "Skipping broken file: {$classFile}");
            }
        } else {
            if(self::$mBrokenFiles) {
                self::$mBrokenFiles = array();
                self::setConfig(__CLASS__, 'brokenFiles', self::$mBrokenFiles);
                Log::v(__CLASS__, "Clearing (%d) broken files(es)", sizeof(self::$mBrokenFiles));
            }
        }
        self::commitConfig();

        self::$mForce = $force;
        self::$mBuilders = array();
        $exCount = self::findClassFiles(Base::getBasePath(), '');
        self::findClasses();
        $exCount += self::buildClasses();
        /** @var $Class \ReflectionClass */
        self::setConfig(__CLASS__, 'lastFile', NULL, true);
        /** @var $Builder \CPath\Framework\Build\IBuilder */
        foreach(self::$mBuilders as $Builder)
            $Builder->buildComplete();

        $v = ++ Compile::$BuildInc;
        Compile::commit();
//        $v = Base::getConfig('build.inc', 0);
//        Base::commitConfig('build.inc', ++$v);

        Log::v(__CLASS__, "All Builds Complete (inc={$v})");
        return $exCount;
    }

    private static function findClasses() {
        $classes = array_merge(get_declared_classes(), get_declared_interfaces());
        foreach($classes as $className) {
            $Class = new \ReflectionClass($className);
            $classFile = $Class->getFileName();
            if(!isset(self::$mClassFiles[$classFile])){
                Log::v2(__CLASS__, "Skipping non-framework class '{$Class->getName()}' at {$classFile}");
                continue;
            }
            if(self::$mBrokenFiles && in_array($classFile, self::$mBrokenFiles)) {
                Log::v2(__CLASS__, "Skipping broken class '{$Class->getName()}'");
                continue;
            }

            $name = strtr($className, '\\', '/');
            $classFile2 = realpath(Base::getBasePath() . $name . '.php');
            if($classFile !== $classFile2) {
                Log::v2(__CLASS__, "Skipping secondary class '{$Class->getName()}' in $classFile");
                continue;
            }


            /* RENAME

            $newClassFile = dirname($classFile) . '/' . basename($className) . '.php';
            Log::v(__CLASS__, "Coppied class file to " . $newClassFile);
            copy($classFile, $newClassFile);

            $newFolder = dirname($newClassFile);
            $folderName = basename($newFolder);
            $baseClassName = basename(dirname($className));
            if($folderName !== $baseClassName) {
                $newFolder2 = dirname($newFolder) . '/' . $baseClassName;
                rename($newFolder, $newFolder2);
                Log::v(__CLASS__, "Renamed $newFolder to $newFolder2");
            }
*/

            if($Class->getConstant('BUILD_IGNORE')) {
            }
//            if($Class->isAbstract()) {
//                Log::v2(__CLASS__, "Ignoring Abstract Class '{$className}' in '{$classFile}'");
//                continue;
//            }

            $ns = dirname(__NAMESPACE__);
            if($Class->implementsInterface($ns . "\\IBuildable")) {

                if($Class->getConstant('BUILD_IGNORE')===true) {
                    Log::v2(__CLASS__, "Ignoring Class '{$className}' marked with BUILD_IGNORE===true");
                    continue;
                } else {
                    if($Class->implementsInterface($ns . "\\IBuilder")) {
                        Log::v(__CLASS__, "Found Builder Class: {$className}");
                        //call_user_func(array($Class->getName(), 'build'), $Class);
                        static::$mBuilders[] = $Class->getMethod('createBuildableInstance')->invoke(null);
                    }
                    Log::v2(__CLASS__, "Found Class '{$Class->getName()}'");
                    self::$mClasses[] = $Class;
                    continue;
                }
            }

            Log::v2(__CLASS__, "Ignoring Class '{$className}' in '{$classFile}'");
            continue;
        }
    }

    private static function buildClasses() {
        $exCount = 0;
        foreach(self::$mClasses as $Class) {
            $Method = $Class->getMethod('createBuildableInstance');
            if($Method->isAbstract()) {
                Log::v2(__CLASS__, "Ignoring abstract method found in '{$Class->getName()}'");
                continue;
            }
            $Buildable = $Method->invoke(null);

            if(!$Buildable) {
                Log::v2(__CLASS__, "No Buildable instance returned for '{$Class->getName()}'");
                self::$mClasses[] = $Class;
                continue;
            }

            if(!$Buildable instanceof IBuildable){
                Log::e(__CLASS__, "Buildable instance returned does not implement IBuildable for '{$Class->getName()}'");
                $exCount++;
                continue;
            }

            Log::v2(__CLASS__, "Building '{$Class->getName()}'");
            foreach(self::$mBuilders as $Builder) try {
                $Builder->build($Buildable);
            } catch (\Exception $ex) {
                $exCount++;
                Log::ex(get_class($Builder), $ex);
            }
        }
        return $exCount;
    }
    /**
     * Find all classes by folder
     * @param $path string the class path
     * @param $dirClass string the class namespace
     * @throws \Exception if a build fails
     * @return int The number of exceptions that occurred
     */
    private static function findClassFiles($path, $dirClass) {
        $exCount = 0;
        Log::v2(__CLASS__, "Scanning '{$path}'");
        $pathName = basename($path);

        foreach(explode('|', self::ROUTE_IGNORE_DIR) as $dir)
            if($dir == $pathName) {
                Log::v2(__CLASS__, "Found '{$dir}'. Ignoring Directory '{$path}'");
                return $exCount;
            }

        foreach(explode('|', self::ROUTE_IGNORE_FILES) as $file)
            if(file_exists($path . '/' . $file)) {
                Log::v2(__CLASS__, "Found File '{$file}'. Ignoring Directory '{$path}'");
                return $exCount;
            }

        foreach(scandir($path) as $file) {
            if(in_array($file, array('.', '..')))
                continue;
            $filePath = realpath($path . '/' . $file);
            if(is_dir($filePath)) {
                $exCount += self::findClassFiles($filePath, $dirClass .'\\'. ucfirst($file));
                continue;
            }
            if(substr($file, -4) !== '.php'){
                Log::v2(__CLASS__, "Skipping non-php file: {$filePath}");
                continue;
            }

            //Log::v(__CLASS__, "Coppied class file to " . $filePath . '.old');
            //copy($filePath, $filePath . '.old');

            //$name = substr($file, 0, strlen($file) - 10);
            //$class = $dirClass . '\\' . ucfirst($name);

            if(self::$mFilter
                && !fnmatch(self::$mFilter, $filePath, FNM_NOESCAPE)) {
                //&& strpos($filePath, __DIR__) !== 0) {
                Log::v2(__CLASS__, "Skipping filtered file (" . self::$mFilter . "): {$filePath}");
                continue;
            }

            if(self::$mBrokenFiles && in_array($filePath, self::$mBrokenFiles)) {
                Log::v(__CLASS__, "Skipping broken file: {$filePath}");
                continue;
            }

            self::setConfig(__CLASS__, 'lastFile', $filePath, true);

            // Content

            $isClass = false;
            $handle = fopen($filePath, 'r');
            while (($buffer = fgets($handle)) !== false) {
                if (preg_match('/(^|\s+)(class|interface)\s+/i', $buffer)) {
                    $isClass = true;
                    break;
                }
            }
            fclose($handle);

            if(!$isClass) {
                Log::v(__CLASS__, "Skipping non-class php file: {$filePath}");
                continue;
            }

            try{
                require_once($filePath);
            } catch (\Exception $ex) {
                //self::setConfig(__CLASS__, 'lastFile', NULL, true);
                $exCount++;
                Log::ex("Exception occurred while loading {$filePath}", $ex);
                continue;
            }
            self::setConfig(__CLASS__, 'lastFile', NULL);
            self::$mClassFiles[$filePath] = true;
            Log::v2(__CLASS__, "Found Class file: {$filePath}");
//            $Class = new \ReflectionClass($class);
//            if($Class === NULL) // TODO: can this be null?
//                throw new \Exception("Class '{$class}' not found in '{$filePath}'");
//
//            if($Class->getConstant('BUILD_IGNORE')) {
//                Log::v2(__CLASS__, "Ignoring Class '{$class}' in '{$filePath}'");
//                continue;
//            }
//            if($Class->isAbstract()) {
//                Log::v2(__CLASS__, "Ignoring Abstract Class '{$class}' in '{$filePath}'");
//                continue;
//            }
//
//            if($Class->implementsInterface(__NAMESPACE__."\\Interfaces\\IBuilder")) {
//                Log::v(__CLASS__, "Found Builder Class: {$class}");
//                //call_user_func(array($Class->getName(), 'build'), $Class);
//                static::$mBuilders[] = $Class->newInstance();
//            }
        }
        return $exCount;
    }

    /**
     * Return an instance of the class for building purposes
     * @return IBuildable|NULL an instance of the class or NULL to ignore
     */
    static function createBuildableInstance() {
        return new static();
    }

    /**
     * Render this request
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function render(IRequest $Request)
    {
        $Util = new APIRenderUtil($this);
        $Util->render($Request);
    }
}