<?php

/*
 * This file is part of Elasticsearch Indexer.
 *
 * (c) Wallmander & Co <mikael@wallmanderco.se>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wallmander\ElasticsearchIndexer\Model;

/**
 * Fetches config files and handles WordPress options.
 *
 * @author Mikael Mattsson <mikael@wallmanderco.se>
 */
class Config
{
    /**
     * Prefix for options.
     */
    const OPTION_PREFIX = 'esi_';

    /**
     * Fetch config array from a file in the config directory.
     *
     * @param string $config
     *
     * @return array
     */
    public static function load($config)
    {
        return require ESI_PATH.'config/'.$config.'.php';
    }

    /**
     * Get option from wp_options table.
     *
     * @param $key
     *
     * @return mixed|void
     */
    public static function option($key)
    {
        $o = get_option(static::OPTION_PREFIX.$key, null);
        if ($o !== null) {
            return $o;
        }
        $defaults = static::load('defaults');

        return $defaults[$key];
    }

    /**
     * Save an option to wp_options table.
     *
     * @param             $key
     * @param             $value
     * @param null|string $autoload
     */
    public static function setOption($key, $value, $autoload = null)
    {
        update_option(static::OPTION_PREFIX.$key, $value, $autoload);
    }

    /**
     * Prepend the option prefix to a key.
     *
     * @param $key
     *
     * @return string
     */
    public static function optionKey($key)
    {
        return static::OPTION_PREFIX.$key;
    }

    public static function getHosts()
    {
        $hosts = [];
        // hosts separated by comma (,) is deprecated.
        $option = str_replace(',', "\n", static::option('hosts'));
        foreach (explode("\n", $option) as $h) {
            if (strpos($h, '://') === false) {
                $hosts[] = trim('http://'.$h);
            } else {
                $hosts[] = trim($h);
            }
        }

        return $hosts;
    }

    public static function getFirstHost()
    {
        return static::getHosts()[0];
    }

    public static function getIndexName($blogID)
    {
        $indexName = static::option('index_name');

        if (!$indexName) {
            // Generate a name
            $siteUrl = get_site_url($blogID);

            $indexName = preg_replace('#https?://(www\.)?#i', '', $siteUrl);
            $indexName = preg_replace('#[^\w]#', '', $indexName);
            static::setOption('index_name', $indexName);
        }

        $indexName .= '-'.$blogID;

        return apply_filters('esi_index_name', $indexName);
    }
}
