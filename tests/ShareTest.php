<?php

namespace Tests;

use Hebrahimzadeh\Share\ShareFacade as Share;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Support\Facades\View;

/**
 * @noRector Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector
 */
class ShareTest extends TestCase
{
    use InteractsWithViews;

    protected $expected = [
        'blogger' => 'https://www.blogger.com/blog-this.g?u=http%3A%2F%2Fwww.example.com&n=Example',
        'digg' => 'https://digg.com/news/submit-link?url=http%3A%2F%2Fwww.example.com',
        'email' => 'mailto:?subject=Example&body=http%3A%2F%2Fwww.example.com',
        'evernote' => 'http://www.evernote.com/clip.action?url=http%3A%2F%2Fwww.example.com&title=Example',
        'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fwww.example.com&quote=Example',
        'gmail' => 'https://mail.google.com/mail/?su=http%3A%2F%2Fwww.example.com&body=Example&view=cm&fs=1&to=&ui=2&tf=1',
        'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=http%3A%2F%2Fwww.example.com',
        'pinterest' => 'https://pinterest.com/pin/create/button/?url=http%3A%2F%2Fwww.example.com',
        'reddit' => 'https://www.reddit.com/submit?url=http%3A%2F%2Fwww.example.com&title=Example',
        'scoopit' => 'https://www.scoop.it/bookmarklet?url=http%3A%2F%2Fwww.example.com',
        'telegram' => 'https://telegram.me/share/url?url=http%3A%2F%2Fwww.example.com&text=Example',
        'tumblr' => 'https://www.tumblr.com/widgets/share/tool?canonicalUrl=http%3A%2F%2Fwww.example.com&title=Example',
        'twitter' => 'https://twitter.com/intent/tweet?url=http%3A%2F%2Fwww.example.com&text=Example',
        'vk' => 'http://vk.com/share.php?url=http%3A%2F%2Fwww.example.com&title=Example&image=Media&noparse=false',
        'whatsapp' => 'whatsapp://send?text=Example%20http%3A%2F%2Fwww.example.com',
        'service' => 'http://service.example.com?url=http%3A%2F%2Fwww.example.com&title=Example&media=Media',
        'service2' => 'http://service2.example.com?url=http%3A%2F%2Fwww.example.com&title=Example&extra1=Extra%201&extra2=Extra%202',
    ];

    public function testCallMethod()
    {
        $view = $this->view('social-share::default', [
                'service' => ['uri' => 'http://service2.example.com', 'extra' => [
                    'extra1' => 'Extra 1',
                    'extra2' => 'Extra 2',
                ]],
                'url' => 'http://www.example.com',
                'title' => '',
                'media' => '',
                'sep' => '&amp;',
            ]);

        $view->assertSee(Share::page('http://www.example.com')->service2());
    }

    public function testALink()
    {
        $view = $this->view('social-share::default', [
            'service' => ['uri' => 'http://service.example.com', 'mediaName' => 'media'],
            'url' => 'http://www.example.com',
            'title' => '',
            'media' => '',
            'sep' => '&',
        ]);
        $view->assertSee(Share::page('http://www.example.com')->service());
    }

    public function testViewItems()
    {
        $this->app->config->set('social-share.fontAwesomeVersion', 5);
        $html = Share::page('http://www.example.com', 'This page is')->services(['telegram']);
        $this->assertStringContainsString('<div id="social-links"><ul><li><a target="_blank" href="https://telegram.me/share/url?url=http%3A%2F%2Fwww.example.com&text=This%20page%20is" class="social-button " id="" title="" rel=""><span class="fab fa-telegram"></span></a></li></ul></div>', $html);
    }

    public function testRenderUrlOnly()
    {
        $this->assertEquals(
            'http://service.example.com?url=http%3A%2F%2Fwww.example.com',
            Share::page('http://www.example.com')->service()
        );
    }

    public function testRenderUrlAndTitle()
    {
        $this->assertEquals(
            'http://service.example.com?url=http%3A%2F%2Fwww.example.com&title=Example',
            Share::page('http://www.example.com', 'Example')->service()
        );
    }

    public function testRenderUrlTitleAndMedia()
    {
        $this->assertEquals(
            'http://service.example.com?url=http%3A%2F%2Fwww.example.com&title=Example&media=Media',
            Share::page('http://www.example.com', 'Example', 'Media')->service()
        );
    }

    public function testRenderExtra()
    {
        $this->assertEquals(
            'http://service2.example.com?url=http%3A%2F%2Fwww.example.com&extra1=Extra%201&extra2=Extra%202',
            Share::page('http://www.example.com')->service2()
        );
    }

    public function testSeparator()
    {
        $this->app->config->set('social-share.separator', '&amp;');
        $this->assertEquals(
            'http://service.example.com?url=http%3A%2F%2Fwww.example.com&amp;title=Example',
            Share::page('http://www.example.com', 'Example')->service()
        );
    }

    public function testServices()
    {
        $actual = Share::page('http://www.example.com', 'Example', 'Media')->services(
            'blogger',
            'digg',
            'email',
            'evernote',
            'facebook',
            'gmail',
            'linkedin',
            'pinterest',
            'reddit',
            'scoopit',
            'telegram',
            'tumblr',
            'twitter',
            'vk',
            'whatsapp',
            'service',
            'service2'
        );

        $this->assertEquals($this->expected, $actual->getLinks());
    }

    public function testServicesWithArray()
    {
        $actual = Share::page('http://www.example.com', 'Example', 'Media')->services(
            [
                'blogger',
                'digg',
                'email',
                'evernote',
                'facebook',
                'gmail',
                'linkedin',
                'pinterest',
                'reddit',
                'scoopit',
                'telegram',
                'tumblr',
                'twitter',
                'vk',
                'whatsapp',
                'service',
                'service2',
            ]
        );

        $this->assertEquals($this->expected, $actual->getLinks());
    }

    public function testDefaultIsAll()
    {
        $actual = Share::page('http://www.example.com', 'Example', 'Media')->services();
        $this->assertEquals($this->expected, $actual->getLinks());
    }

    protected function assertPageFound($url)
    {
        $client = new Client([
            'http_errors' => false,
        ]);

        $response = $client->head($url);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @group live
     */
    public function testBlogger()
    {
        $url = 'https://www.blogger.com/blog-this.g?u=http%3A%2F%2Fwww.example.com&n=Example';
        $this->assertEquals($url, Share::page('http://www.example.com', 'Example')->blogger());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testDigg()
    {
        $url = 'https://digg.com/news/submit-link?url=http%3A%2F%2Fwww.example.com';
        $this->assertEquals($url, Share::page('http://www.example.com')->digg());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testEmail()
    {
        $url = 'mailto:?subject=Example&body=http%3A%2F%2Fwww.example.com';
        $this->assertEquals($url, Share::page('http://www.example.com', 'Example')->email());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testEvernote()
    {
        $url = 'http://www.evernote.com/clip.action?url=http%3A%2F%2Fwww.example.com&title=Example';
        $this->assertEquals($url, Share::page('http://www.example.com', 'Example')->evernote());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testFacebook()
    {
        $url = 'https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fwww.example.com&quote=Example';
        $this->assertEquals($url, Share::page('http://www.example.com', 'Example')->facebook());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testGmail()
    {
        $url = 'https://mail.google.com/mail/?su=http%3A%2F%2Fwww.example.com&body=Example&view=cm&fs=1&to=&ui=2&tf=1';
        $this->assertEquals($url, Share::page('http://www.example.com', 'Example')->gmail());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testLinkedin()
    {
        $url = 'https://www.linkedin.com/sharing/share-offsite/?url=http%3A%2F%2Fwww.example.com';
        $this->assertEquals($url, Share::page('http://www.example.com')->linkedin());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testPinterest()
    {
        $url = 'https://pinterest.com/pin/create/button/?url=http%3A%2F%2Fwww.example.com';
        $this->assertEquals($url, Share::page('http://www.example.com')->pinterest());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testReddit()
    {
        $url = 'https://www.reddit.com/submit?url=http%3A%2F%2Fwww.example.com&title=Example';
        $this->assertEquals($url, Share::page('http://www.example.com', 'Example')->reddit());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testScoopit()
    {
        $url = 'https://www.scoop.it/bookmarklet?url=http%3A%2F%2Fwww.example.com';
        $this->assertEquals($url, Share::page('http://www.example.com')->scoopit());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testTelegram()
    {
        $url = 'https://telegram.me/share/url?url=http%3A%2F%2Fwww.example.com&text=Example';
        $this->assertEquals($url, Share::page('http://www.example.com', 'Example')->telegram());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testTumblr()
    {
        $url = 'https://www.tumblr.com/widgets/share/tool?canonicalUrl=http%3A%2F%2Fwww.example.com&title=Example';
        $this->assertEquals($url, Share::page('http://www.example.com', 'Example')->tumblr());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testTwitter()
    {
        $url = 'https://twitter.com/intent/tweet?url=http%3A%2F%2Fwww.example.com&text=Example';
        $this->assertEquals($url, Share::page('http://www.example.com', 'Example')->twitter());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testVk()
    {
        $url = 'http://vk.com/share.php?url=http%3A%2F%2Fwww.example.com&title=Example&image=Media&noparse=false';
        $this->assertEquals($url, Share::page('http://www.example.com', 'Example', 'Media')->vk());
        // $this->assertPageFound($url);
    }

    /**
     * @group live
     */
    public function testWhatsapp()
    {
        $url = 'whatsapp://send?text=Example%20http%3A%2F%2Fwww.example.com';
        $this->assertEquals($url, Share::page('http://www.example.com', 'Example')->whatsapp());
        // $this->assertPageFound($url);
    }

    protected function getPackageProviders($app)
    {
        return [
            \Hebrahimzadeh\Share\ShareServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Share' => \Hebrahimzadeh\Share\ShareFacade::class,
        ];
    }

    protected function getEnvironmentSetup($app)
    {
        $app->config->set('social-share.services.service', [
            'uri' => 'http://service.example.com',
            'mediaName' => 'media',
        ]);

        $app->config->set('social-share.services.service2', [
            'uri' => 'http://service2.example.com',
            'extra' => ['extra1' => 'Extra 1', 'extra2' => 'Extra 2'],
        ]);
    }
}
