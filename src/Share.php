<?php

namespace Hebrahimzadeh\Share;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;

class Share
{
    protected $app;

    protected $url;
    protected $title;
    protected $media;

    /**
     * Font awesome version
     * @var int
     */
    protected $fontAwesomeVersion = 5;

    /**
     * Extra options for the share links
     *
     * @var array
     */
    protected $options = [];

    /**
     * The generated urls
     *
     * @var array
     */
    protected $generatedLinks = [];

    /**
     * Html to prefix before the share links
     *
     * @var string
     */
    protected $prefix = '<div id="social-links"><ul>';

    /**
     * Html to append after the share links
     *
     * @var string
     */
    protected $suffix = '</ul></div>';

    /**
     * The generated html
     *
     * @var string
     */
    protected $html = '';

    /**
     * Return a string with html at the end
     * of the chain.
     *
     * @return string
     */
    public function __toString()
    {
        foreach ($this->generatedLinks as $provider => $url){
            $this->buildLink($provider, $url);
        }
        $this->html = $this->prefix . $this->html;
        $this->html .= $this->suffix;
        return $this->html;
    }


    public function __construct($app)
    {
        $this->app = $app;
        $this->fontAwesomeVersion = config('laravel-share.fontAwesomeVersion', 5);
    }

    public function load($url, $title = '', $media = '')
    {
        $this->url = $url;
        $this->title = $title;
        $this->media = $media;

        return $this;
    }

    public function services()
    {
        $services = func_get_args();

        if (empty($services)) {
            $services = array_keys($this->app->config->get('social-share.services'));
        } elseif (is_array($services[0])) {
            $services = $services[0];
        }

        if ($services) {
            foreach ($services as $service) {
                $this->$service();
            }
        }

        return $this;
    }

    protected function generateUrl($serviceId)
    {
        $vars = [
            'service' => $this->app->config->get("social-share.services.{$serviceId}", []),
            'sep' => $this->app->config->get('social-share.separator', '&'),
        ];

        if (empty($vars['service']['only'])) {
            $only = ['url', 'title', 'media'];
        } else {
            $only = $vars['service']['only'];
        }

        foreach ($only as $varName) {
            $vars[$varName] = $this->$varName;
        }

        $view = Arr::get($vars['service'], 'view', 'social-share::default');

        $this->generatedLinks[$serviceId] = ($url = trim(View::make($view, $vars)->render()));
        return $url;
    }

    public function getLinks()
    {
        return $this->generatedLinks;
    }

    /**
     * Build a single link
     *
     * @param string $provider
     * @param string $url
     */
    protected function buildLink($provider, $url)
    {
        $this->html .= trans("laravel-share::laravel-share-fa$this->fontAwesomeVersion.$provider", [
            'url' => $url,
            'class' => array_key_exists('class', $this->options) ? $this->options['class'] : '',
            'id' => array_key_exists('id', $this->options) ? $this->options['id'] : '',
            'title' => array_key_exists('title', $this->options) ? $this->options['title'] : '',
            'rel' => array_key_exists('rel', $this->options) ? $this->options['rel'] : '',
        ]);

    }

    /**
     * Optionally Set custom prefix and/or suffix
     *
     * @param string $prefix
     * @param string $suffix
     */
    protected function setPrefixAndSuffix($prefix, $suffix)
    {
        if (!is_null($prefix)) {
            $this->prefix = $prefix;
        }

        if (!is_null($suffix)) {
            $this->suffix = $suffix;
        }
    }

    public function __call($name, $arguments)
    {
        return $this->generateUrl($name);
    }
}
