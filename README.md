本项目为chrome-php/headless-chromium-php的中文分支

### 修改:

2018-11-20 18:25

1.$browser_option进行了修改(支持proxy,对设置项进行了精简)

### 使用(linux平台):
1.chrome安装:
```
yum install https://dl.google.com/linux/direct/google-chrome-stable_current_x86_64.rpm
ln -s /etc/alternatives/google-chrome /usr/bin/chrome
chmod -R 777 /usr/bin/chrome
```

2.也支持chromedriver(推荐):
```
前往页面https://sites.google.com/a/chromium.org/chromedriver/downloads下载并上传为/usr/bin/chromedriver
chmod -R 777 /usr/bin/chromedriver
```

3.中文字体支持和flash
```
字体:
yum -y install *-fonts-*
yum -y install http://linuxdownload.adobe.com/linux/x86_64/adobe-release-x86_64-1.0-1.noarch.rpm
yum install flash-plugin
```

4.php7+(各有各法,就不举例了)

5.安装
```
composer require chrome-php/chrome
```

### 测试代码:(生成test.png截图即成功)

```php
<?php
require_once('vendor/autoload.php');
use HeadlessChromium\BrowserFactory;
$browserFactory = new BrowserFactory();
$browser = $browserFactory->createBrowser();
$page = $browser->createPage();
$page->navigate('https://baidu.com')->waitForNavigation();
$pageTitle = $page->evaluate('document.title')->getReturnValue();
$page->screenshot()->saveToFile('./test.png');
$browser->close();
```

### API代码解释:

```php
<?php
require_once('vendor/autoload.php');
use HeadlessChromium\BrowserFactory;
//超时与跳转 
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Exception\NavigationExpired;
//Cookie操作
use HeadlessChromium\Cookies\Cookie;
//默认chrome浏览器
 $browserFactory = new BrowserFactory();
 //或使用chromium,对应usr/bin/chromium
 $browserFactory = new BrowserFactory('chromium');
//设置项支持enableImages,windowSize,userAgent,userDataDir,proxy
$browser_option=[
        'enableImages'=> false, //禁止图像,可加速
        'windowSize'=>[1920, 1000],//窗口尺寸
        'userAgent'=>'Your UA',//例如设为手机浏览器
        'userDataDir'=>'/path/',//如果往页面调试跨域js等信息必须.随便空目录路径
        'proxy'=>'127.0.0.1:8000'//代理ip:端口
    ]
    
$browser = $browserFactory->createBrowser($browser_option);

//启动浏览器
$browser = $browserFactory->createBrowser();

//打开标签页
$page = $browser->createPage();

//启动导航
$navigation = $page->navigate('https://test.com');

//等待导航的加载完成
try {
//10000表示状态完成后等待10秒
 $navigation->waitForNavigation(Page::DOM_CONTENT_LOADED, 10000)//dom初始化完成
$navigation->waitForNavigation(Page::LOAD,10000);//默认,页面资源初始化完成
 $navigation->waitForNavigation(Page::NETWORK_IDLE, 10000)//页面资源初始化完成并在最后一次网络请求已停止500ms后
} catch (OperationTimedOut $e) {
//超时无响应状态处理...
} catch (NavigationExpired $e) {
//页面跳转到另页处理...
}


//eval如获取dom
$pageTitle = $page->evaluate('document.title')->getReturnValue();

//eval并等待重载后获取内容比如提交form后再获得value
$evaluation = $page->evaluate(
'(() => {
        document.querySelector("#myinput").value = "hello";
        document.querySelector("#myform").submit();
    })()'
);
$evaluation->waitForPageReload();
$value = $page->evaluate('document.querySelector("#value").innerHTML')->getReturnValue();

//eval一个js的[
        'headless'        => false,         // disable headless mode
        'connectionDelay' => 0.8,           // add 0.8 second of delay between each instruction sent to chrome,
        'debugLogger'     => 'php://stdout' // will enable verbose mode
    ]function并传参
    $evaluation = $page->callFunction(
      'function(a, b) {
          window.foo = a + b;
       }', 
      [1, 2]//参数数组
    );
$value = $evaluation->getReturnValue();

//动态加载js在运行eval
$page->addScriptTag([
'content' => file_get_contents('path/to/jquery.js')
])->waitForResponse();
$page->evaluate('$(".my.element").html()');

//预置js到navigate之前
$script = 'function test(){};';
$page->addPreScript($script);

//预置js到navigate之前但在页面onLoad之后执行
$script = 'function test(){};';
$page->addPreScript($script, ['onLoad' => true]);

//改变当前标签页的可视区域
$page->setViewportSize(1024,968)->await();

//整页截图
$page->screenshot()->ssaveToFile('./test.png');

//局部截图(坐标大小x,y,width,height)
$page->screenshot(0,0,100,100)->ssaveToFile('./test.png');

//创建指定域名的Cookie
$page->setCookies([Cookie::create('name', 'value', ['domain' => 'example.com','expires' => time() + 3600])])->await();

//创建当前页面的Cookie
$page->setCookies([Cookie::create('name', 'value', ['expires'])])->await();

//获得浏览器所有的Cookie
 $cookies = $page->getAllCookies();
 
//获得当前页面的Cookie
$cookies = $page->getCookies();

//按名称内含的关键词筛选cookie
$cookiesFoo = $cookies->filterBy('name', 'foo'); 

//按名称起始筛选Cookie
$cookieBar = $cookies->findOneBy('name', 'bar');

//除了BrowserFactory中设置,还可以动态设置UA.
$page->setUserAgent('my user agent');

//关闭浏览器
$browser->close();

//鼠标操作
$page->mouse()
->move(10, 20)//移动鼠标到x=10,y=20
->click() //左击
->move(100, 200, ['steps' => 5])//移动鼠标到x=100,y=200过程中分5段点击(例如ajax的tab选项卡)
->click(['button' => Mouse::BUTTON_RIGHT]; //右击
//点击完成后等待加载
$page->waitForReload();

//不常用的高级项


//与devtools调试器进行通信
use HeadlessChromium\Communication\Connection;
use HeadlessChromium\Communication\Message;
$webSocketUri = 'ws://127.0.0.1:9222/devtools/browser/xxx';
$connection = new Connection($webSocketUri);
$connection->connect();
$responseReader = $connection->sendMessage(new Message('Target.activateTarget', ['targetId' => 'xxx']));
$response = $responseReader->waitForResponse(1000);
if ($response) {
//成功处理
}else {
//失败处理
}
//还可以创建个Session给指定target
$session = $connection->createSession('target_id');
$response = $session->sendMessageSync(new Message('Page.reload'));
//延迟500毫秒再通信
$connection->setConnectionDelay(500);

//新开一个带通信的浏览器
use HeadlessChromium\Communication\Connection;
use HeadlessChromium\Browser;
$webSocketUri = 'ws://127.0.0.1:9222/devtools/browser/xxx';
$connection = new Connection($webSocketUri);
$connection->connect();
$browser = new Browser($connection);



```
