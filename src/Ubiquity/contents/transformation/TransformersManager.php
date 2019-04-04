<?php

namespace Ubiquity\contents\transformation;

use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UArray;
use Ubiquity\contents\transformation\transformers\DateTime;

/**
 * Transform objects after loading
 *
 * Ubiquity\contents\transformation\transformers$TransformersManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class TransformersManager {
	/**
	 *
	 * @var array|mixed
	 */
	private static $transformers = [ 'datetime' => DateTime::class ];
	private static $key = "contents/transformers";

	/**
	 * Do not use at runtime !
	 */
	public static function start() {
		if (CacheManager::$cache->exists ( self::$key )) {
			self::$transformers = CacheManager::$cache->fetch ( self::$key );
		}
	}

	public static function registerClass($transformer, $classname) {
		self::$transformers [$transformer] = $classname;
	}

	public static function registerAndSaveClass($transformer, $classname) {
		self::start ();
		self::registerClass ( $transformer, $classname );
		self::store ();
	}

	public static function registerClasses($transformersAndClasses) {
		foreach ( $transformersAndClasses as $transformer => $class ) {
			self::registerClass ( $transformer, $class );
		}
	}

	public static function registerClassesAndSave($transformersAndClasses) {
		self::start ();
		foreach ( $transformersAndClasses as $transformer => $class ) {
			self::registerClass ( $transformer, $class );
		}
		self::store ();
	}

	public static function getTransformerClass($transformer) {
		if (isset ( self::$transformers [$transformer] )) {
			return self::$transformers [$transformer];
		}
		return null;
	}

	public static function store() {
		CacheManager::$cache->store ( self::$key, "return " . UArray::asPhpArray ( self::$transformers, 'array' ) . ';' );
	}
}

