<?php

namespace MG\HP;

class Pict
{

	private static $_inited = false;
	private static $isPng = true;
	private static $clientSupportWebp = false;
	private static $sizes = array(
		'min-width: 1366px' => array('1x' => 1366, '2x' => 2732),
		'min-width: 768px' => array('1x' => 768, '2x' => 1536),
		'other' => array('1x' => 375, '2x' => 750)
	);

	private static function checkFormat($str)
	{
		if ($str === 'image/png')
		{
			self::$isPng = true;

			return true;
		}
		elseif ($str === 'image/jpeg')
		{
			self::$isPng = false;

			return true;
		}
		else
			return false;
	}

	private static function implodeSrc($arr)
	{

		$arr[count($arr) - 1] = '';

		return implode('/', $arr);
	}

	private static function generateSrc($str)
	{

		$arPath = explode('/', $str);

		if ($arPath[2] === 'resize_cache')
		{
			$arPath = self::implodeSrc($arPath);

			return str_replace('resize_cache/iblock', 'webp/resize_cache', $arPath);
		}
		else
		{
			$arPath = self::implodeSrc($arPath);

			return str_replace('upload/iblock', 'upload/webp/iblock', $arPath);
		}
	}

	private static function resizePict($file, $width, $height, $isProportional = true, $intQuality = 90)
	{

		$file = \CFile::ResizeImageGet($file, array('width' => $width, 'height' => $height), ($isProportional ? BX_RESIZE_IMAGE_PROPORTIONAL : BX_RESIZE_IMAGE_EXACT), false, false, false, $intQuality);

		return $file['src'];
	}

	private static function getWebp($array, $intQuality = 90)
	{

		if (self::checkFormat($array['CONTENT_TYPE']))
		{
			$array['WEBP_PATH'] = self::generateSrc($array['SRC']);

			if (self::$isPng)
			{
				$array['WEBP_FILE_NAME'] = str_replace('.png', '.webp', strtolower($array['FILE_NAME']));
			}
			else
			{
				$array['WEBP_FILE_NAME'] = str_replace('.jpg', '.webp', strtolower($array['FILE_NAME']));
				$array['WEBP_FILE_NAME'] = str_replace('.jpeg', '.webp', strtolower($array['WEBP_FILE_NAME']));
			}

			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $array['WEBP_PATH']))
			{
				mkdir($_SERVER['DOCUMENT_ROOT'] . $array['WEBP_PATH'], 0777, true);
			}

			$array['WEBP_SRC'] = $array['WEBP_PATH'] . $array['WEBP_FILE_NAME'];

			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $array['WEBP_SRC']))
			{
				if (self::$isPng)
				{
					$im = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . $array['SRC']);
				}
				else
				{
					$im = imagecreatefromjpeg($_SERVER['DOCUMENT_ROOT'] . $array['SRC']);
				}

				imagewebp($im, $_SERVER['DOCUMENT_ROOT'] . $array['WEBP_SRC'], $intQuality);

				imagedestroy($im);

				if (filesize($_SERVER['DOCUMENT_ROOT'] . $array['WEBP_SRC']) % 2 == 1)
				{
					file_put_contents($_SERVER['DOCUMENT_ROOT'] . $array['WEBP_SRC'], "\0", FILE_APPEND);
				}
			}
		}

		return $array;
	}

	private static function init()
	{

		if (!self::$_inited)
		{
			if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false || strpos($_SERVER['HTTP_USER_AGENT'], ' Chrome/') !== false)
			{
				self::$clientSupportWebp = true;
			}
			else
			{
				self::$clientSupportWebp = false;
			}
			self::$_inited = true;
		}
	}

	/**
	 * Преобразовать изображение jpeg и png в webp
	 *
	 * @param  array $file Массив описания файла для преобразования (CFile::GetFileArray())
	 * @param  int $width ширина
	 * @param  int $height высота
	 * @param  bool $isProportional сохранить пропорции Defaults to `true`
	 * @param  int $intQuality качество преобразования Defaults to 70
	 * @param  bool $ignoreSupport игнорировать поддержку браузером Defaults to `false`
	 * @return string src изображения webp или исходного формата
	 */
	public static function getResizeWebpSrc($file, $width, $height, $isProportional = true, $intQuality = 90, $ignoreSupport = false)
	{

		self::init();

		$file['SRC'] = self::resizePict($file, $width, $height, $isProportional, $intQuality);
		if ($ignoreSupport || self::$clientSupportWebp)
		{
			$file = self::getWebp($file, $intQuality);
		}

		return @$file['WEBP_SRC'] ? $file['WEBP_SRC'] : $file['SRC'];
	}

	/**
	 * установить разрешения для изображений
	 *
	 * @param  array $sizes Массив вида <pre>[
	  'min-width: 1366px' => ['1x' => 1366, '2x' => 2732],
	  'min-width: 768px' => ['1x' => 768, '2x' => 1536],
	  'other' => ['1x' => 375, '2x' => 750]
	  ]</pre>
	 * @return bool
	 */
	public static function setPictureSizes($sizes = array())
	{

		if (count($sizes))
		{
			self::$sizes = $sizes;

			return true;
		}

		return false;
	}

	/**
	 * получить содержимое тега `<picture>`
	 *
	 * @param  array $file Массив описания файла для преобразования (CFile::GetFileArray())
	 * @param  bool $convertToWebp преобразовать в webp Defaults to `true`
	 * @param  bool $ignoreSupport игнорировать поддержку браузером Defaults to `false`
	 * @return string
	 */
	public static function getRetinaElementPictureIn($file, $convertToWebp = true, $ignoreSupport = false, $alt = "")
	{
		$alt = (empty($alt) && $file["DESCRIPTION"])?$file["DESCRIPTION"]:$alt; 
		$ret = '';
		foreach (self::$sizes as $media => $size)
		{
			if ($media != 'other')
			{
				$x1 = $convertToWebp ? self::getResizeWebpSrc($file, $size['1x'], $size['1x'] * 100, true, 90, $ignoreSupport)
					: self::resizePict($file, $size['1x'], $size['1x'] * 100, true, 90);
				$x2 = $convertToWebp ? self::getResizeWebpSrc($file, $size['2x'], $size['2x'] * 100, true, 90, $ignoreSupport)
					: self::resizePict($file, $size['1x'], $size['1x'] * 100, true, 90);
				$ret .= '<source media="(' . $media . ')" srcset="' . $x1 . ', ' . $x2 . ' 2x" >';
			}
			else
			{
				$x1 = $convertToWebp ? self::getResizeWebpSrc($file, $size['1x'], $size['1x'] * 100, true, 90, $ignoreSupport)
					: self::resizePict($file, $size['1x'], $size['1x'] * 100, true, 90);
				$x2 = $convertToWebp ? self::getResizeWebpSrc($file, $size['2x'], $size['2x'] * 100, true, 90, $ignoreSupport)
					: self::resizePict($file, $size['1x'], $size['1x'] * 100, true, 90);
				$ret .= '<img src="' . $x1 . '" srcset="' . $x2 . ' 2x" alt="' . $alt . '">';
			}
		}

		return $ret;
	}

	/**
	 * получить описание файла по прямому пути 
	 * @param mixed $SRC путь к файлу
	 */
	public static function getFileInfoFromPath(string $SRC)
	{
		$file = \CFile::MakeFileArray($SRC);
		return array(
			"SRC" => $SRC,
			"FILE_NAME" =>  $file["name"],
			"CONTENT_TYPE" => $file["type"]
		);
	}
}
