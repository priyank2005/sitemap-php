<?php

/**
 * Sitemap
 *
 * This class used for generating Google Sitemap files
 *
 * @package    Sitemap
 * @author     Osman Üngür <osmanungur@gmail.com>
 * @copyright  2009-2011 Osman Üngür
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version    Version @package_version@
 * @since      Class available since Version 1.0.0
 * @link       http://github.com/osmanungur/sitemap-php
 */
class Sitemap {

	/**
	 *
	 * @var XMLWriter
	 */
	private $writer;
	private $domain;
	private $path;
	private $filename = 'sitemap';
	private $current_item = 0;
	private $current_sitemap = 0;
	private $siteMapType;

	const EXT = '.xml';
	const SCHEMA = 'http://www.sitemaps.org/schemas/sitemap/0.9';
	const DEFAULT_PRIORITY = 0.5;
	const ITEM_PER_SITEMAP = 50000;
	const SEPERATOR = '-';
	const INDEX_SUFFIX = 'index';

	const SITEMAP_TYPE_WEBPAGES=0;
	const SITEMAP_TYPE_NEWS=1;

	/**
	 *
	 * @param string $domain
	 * @param enum $siteMapType
	 */
	public function __construct($domain, $siteMapType=null) {
		$this->setDomain($domain);
		if ($siteMapType!=NULL){
			if ($siteMapType==self::SITEMAP_TYPE_WEBPAGES)
				$this->siteMapType=self::SITEMAP_TYPE_WEBPAGES;
			else if ($siteMapType==self::SITEMAP_TYPE_NEWS)
				$this->siteMapType=self::SITEMAP_TYPE_NEWS;
			else
				 throw new Exception('Unavailable SiteMap Type');
		}else{
			$this->siteMapType=self::SITEMAP_TYPE_WEBPAGES;
		}
	}

	/**
	 * Sets root path of the website, starting with http:// or https://
	 *
	 * @param string $domain
	 */
	public function setDomain($domain) {
		$this->domain = $domain;
		return $this;
	}

	/**
	 * Returns root path of the website
	 *
	 * @return string
	 */
	private function getDomain() {
		return $this->domain;
	}

	/**
	 * Returns XMLWriter object instance
	 *
	 * @return XMLWriter
	 */
	private function getWriter() {
		return $this->writer;
	}

	/**
	 * Assigns XMLWriter object instance
	 *
	 * @param XMLWriter $writer 
	 */
	private function setWriter(XMLWriter $writer) {
		$this->writer = $writer;
	}

	/**
	 * Returns path of sitemaps
	 * 
	 * @return string
	 */
	private function getPath() {
		return $this->path;
	}

	/**
	 * Sets paths of sitemaps
	 * 
	 * @param string $path
	 * @return Sitemap
	 */
	public function setPath($path) {
		$this->path = $path;
		return $this;
	}

	/**
	 * Returns filename of sitemap file
	 * 
	 * @return string
	 */
	private function getFilename() {
		return $this->filename;
	}

	/**
	 * Sets filename of sitemap file
	 * 
	 * @param string $filename
	 * @return Sitemap
	 */
	public function setFilename($filename) {
		$this->filename = $filename;
		return $this;
	}

	/**
	 * Returns current item count
	 *
	 * @return int
	 */
	private function getCurrentItem() {
		return $this->current_item;
	}

	/**
	 * Increases item counter
	 * 
	 */
	private function incCurrentItem() {
		$this->current_item = $this->current_item + 1;
	}

	/**
	 * Returns current sitemap file count
	 *
	 * @return int
	 */
	private function getCurrentSitemap() {
		return $this->current_sitemap;
	}

	/**
	 * Increases sitemap file count
	 * 
	 */
	private function incCurrentSitemap() {
		$this->current_sitemap = $this->current_sitemap + 1;
	}

	/**
	 * Prepares sitemap XML document
	 * 
	 */
	private function startSitemap() {
		$this->setWriter(new XMLWriter());
		if ($this->getCurrentSitemap()) {
			$this->getWriter()->openURI($this->getPath() . $this->getFilename() . self::SEPERATOR . $this->getCurrentSitemap() . self::EXT);
		} else {
			$this->getWriter()->openURI($this->getPath() . $this->getFilename() . self::EXT);
		}
		$this->getWriter()->startDocument('1.0', 'UTF-8');
		$this->getWriter()->setIndent(true);
		$this->getWriter()->startElement('urlset');
		$this->getWriter()->writeAttribute('xmlns', self::SCHEMA);
	}

	/**
	 * Adds an item to sitemap
	 *
	 * @param string $loc URL of the page. This value must be less than 2,048 characters. 
	 * @param string $priority The priority of this URL relative to other URLs on your site. Valid values range from 0.0 to 1.0.
	 * @param string $changefreq How frequently the page is likely to change. Valid values are always, hourly, daily, weekly, monthly, yearly and never.
	 * @param string|int $lastmod The date of last modification of url. Unix timestamp or any English textual datetime description.
	 * @return Sitemap
	 */
	public function addItem($loc, $priority = self::DEFAULT_PRIORITY, $changefreq = NULL, $lastmod = NUL
		, $newsName=NULL
		, $newsLanguage=NULL
		, $newsAccess=NULL
		, $newsGenres=NULL
		, $newsPublicationDate=NULL
		, $newsTitle=NULL
		, $newsKeywords=NULL
		, $newsStockTickers=NULL
		) {
		if (($this->getCurrentItem() % self::ITEM_PER_SITEMAP) == 0) {
			if ($this->getWriter() instanceof XMLWriter) {
				$this->endSitemap();
			}
			$this->startSitemap();
			$this->incCurrentSitemap();
		}
		$this->incCurrentItem();
		$this->getWriter()->startElement('url');
		$this->getWriter()->writeElement('loc', $this->getDomain() . $loc);
		$this->getWriter()->writeElement('priority', $priority);
		if ($changefreq)
			$this->getWriter()->writeElement('changefreq', $changefreq);
		if ($lastmod)
			$this->getWriter()->writeElement('lastmod', $this->getLastModifiedDate($lastmod));
		if ($this->siteMapType==self::SITEMAP_TYPE_NEWS){
			if (empty($newsPublicationDate)){
				if (empty($lastmod))
					$newsPublicationDate=$this->getLastModifiedDate(time());
				else
					$newsPublicationDate=$this->getLastModifiedDate($lastmod);
			}else{
				$newsPublicationDate=$this->getLastModifiedDate($newsPublicationDate);
			}
			if (empty($newsTitle)){
				throw new Exception("Please add Title for news type of sitemap", 1);
			}
			if (empty($newsName)){
				throw new Exception("Please add Name for news type of sitemap", 1);
			}
			if (empty($newsLanguage)){
				throw new Exception("Please add Language for news type of sitemap", 1);
			}
			$this->getWriter()->startElement('news:news');
			$this->getWriter()->startElement('news:publication');
			$this->getWriter()->writeElement('news:name', $newsName);
			$this->getWriter()->writeElement('news:language', $newsLanguage);
			$this->getWriter()->endElement();
			if (!empty($newsAccess))
				$this->getWriter()->writeElement('news:access', $newsAccess);
			if (!empty($newsGenres))
				$this->getWriter()->writeElement('news:genres', $newsGenres);
			$this->getWriter()->writeElement('news:publication_date', $newsPublicationDate);
			$this->getWriter()->writeElement('news:title', $newsTitle);
			if (!empty($newsKeywords))
				$this->getWriter()->writeElement('news:keywords', $newsKeywords);
			if (!empty($newsStockTickers))
				$this->getWriter()->writeElement('news:stock_tickers', $newsStockTickers);
			$this->getWriter()->endElement();
		}
		$this->getWriter()->endElement();
		return $this;
			}
	}

	/**
	 * Prepares given date for sitemap
	 *
	 * @param string $date Unix timestamp or any English textual datetime description
	 * @return string Year-Month-Day formatted date.
	 */
	private function getLastModifiedDate($date) {
		if (ctype_digit($date)) {
			return date(DateTime::ATOM, $date);
		} else {
			$date = strtotime($date);
			return date(DateTime::ATOM, $date);
		}
	}

	/**
	 * Finalizes tags of sitemap XML document.
	 *
	 */
	private function endSitemap() {
		if (!$this->getWriter()) {
			$this->startSitemap();
		}
		$this->getWriter()->endElement();
		$this->getWriter()->endDocument();
	}

	/**
	 * Writes Google sitemap index for generated sitemap files
	 *
	 * @param string $loc Accessible URL path of sitemaps
	 * @param string|int $lastmod The date of last modification of sitemap. Unix timestamp or any English textual datetime description.
	 */
	public function createSitemapIndex($loc, $lastmod = 'Today') {
		$this->endSitemap();
		$indexwriter = new XMLWriter();
		$indexwriter->openURI($this->getPath() . $this->getFilename() . self::SEPERATOR . self::INDEX_SUFFIX . self::EXT);
		$indexwriter->startDocument('1.0', 'UTF-8');
		$indexwriter->setIndent(true);
		$indexwriter->startElement('sitemapindex');
		$indexwriter->writeAttribute('xmlns', self::SCHEMA);
		for ($index = 0; $index < $this->getCurrentSitemap(); $index++) {
			$indexwriter->startElement('sitemap');
			$indexwriter->writeElement('loc', $loc . $this->getFilename() . ($index ? self::SEPERATOR . $index : '') . self::EXT);
			$indexwriter->writeElement('lastmod', $this->getLastModifiedDate($lastmod));
			$indexwriter->endElement();
		}
		$indexwriter->endElement();
		$indexwriter->endDocument();
	}

}