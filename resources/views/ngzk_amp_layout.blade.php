<!doctype html>
<html ⚡>
<head>
    <meta charset="utf-8">
    <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
    <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <title>@yield('title')</title>
    <link rel='icon' href='/nogizaka46.ico' type='image/x-icon' />
    <link rel="canonical" href="@yield('canonical')">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1,maximum-scale=1.0, user-scalable=no">
    <style amp-custom>
        html, body, blockquote {margin: 0;padding: 0;line-height: 1.5em;}
        body {background-color: #7e1083;}
        button:focus {outline: none;}
        p {min-height: 1.5em;line-height: 1.5em;margin: 0;}
        div.font-size-1 {font-size: 14px;display: inline;}
        amp-img.contain img {object-fit: contain;}
        .fixed-height-container {position: relative;width: 100%;height: 300px;}
        ul.site-menu {
            padding: 0;
            margin: 0;
            background: #ffffff;
        }
        ul.site-menu li {
            list-style: none;
            border-bottom: solid 1px #ededed;
        }
        ul.site-menu li a {
            font-size: 1.25em;
            display: block;
            width: 100px;
            height: 50px;
            line-height: 50px;
            color: #7e1083;
            text-decoration: none;
            text-align: center;
        }
        ul.site-menu li a.mini {
            font-size: 12px;
        }
        ul.site-menu a:visited {
            color: #7e1083;
            text-decoration: none;
        }
        ul.site-menu .ampclose-btn {
            display: block;
            width: 100px;
            height: 60px;
            background: url(/images/close.svg) no-repeat center/30px 24px transparent;
        }
        .header {width: 100%;background-color: #fff;box-shadow: 0 0 40px 0 rgba(0,0,0,0.5);font-size: 14px;}
        .header .logo {margin-top: 10px;width: 60px;height: 60px;position: relative;background-color: rgb(241,241,243);border-radius: 30px;overflow: hidden;float: left;margin-left: 15px;}
        .header .site-meta {display: inline-block;height: 60px;margin-top: 10px;vertical-align: top;margin-left: 15px;}
        .header .site-name {font-size: 1.5em;color: #333333;height: 30px;line-height: 30px;}
        .header .site-desc {font-size: 16px;color: #777777;height: 30px;line-height: 30px;}
        header .meta {
            vertical-align: top;
            width: 100%;
            padding: 10px 20px 10px 85px;
            box-sizing: border-box;
        }
        article .meta {
            width: 100%;
            padding: 0 0 0 70px;
            box-sizing: border-box;
        }
        .author-name, .post-date {
            display: inline-block;
            font-size: 16px;
            color: #777777;
            height: 30px;
            line-height: 30px;
        }
        .ampstart-btn {
            position: absolute;
            top: 10px;
            right: 4px;
            border: 0;
            background: url(/images/hamburger.svg) no-repeat center/30px 24px transparent;
            width: 40px;
            height: 40px;
        }
        footer {
            padding: 0 15px 20px 15px;
            display: inline-block;
            width: 100%;
            background-color: #7e1083;;
            box-sizing: border-box;
        }
        footer a {
            padding: 10px 20px;
            font-size: 14px;
            background-color: #ffffff;
            text-decoration: none;
            box-shadow: 0 5px 40px 0 rgba(0,0,0,0.5);
            border-radius: 2px;
        }
        footer .prev {
            align-self: flex-start;
        }
        footer .next {
            align-self: flex-end;
            float: right;
        }
        .readmore {
            font-size: 16px;
            padding: 5px 10px;
            border-radius: 3px;
            border: 1px solid #7e1083;
            color: #7e1083;
            display: inline-block;
            text-decoration: none;
            margin-top: 20px;
        }
        a.readmore:visited {
            text-decoration: none;
            color: #7e1083;
        }
        @yield('custom-style')
    </style>


    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
    <script type="application/ld+json"><?php echo json_encode($schema,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)?></script>
</head>
<body>
    @yield('body')
    <amp-sidebar id="sidebar" layout="nodisplay" side="right">
        <ul class="site-menu">
            <li><div class="ampclose-btn" on="tap:sidebar.close"></div></li>
            <li><a href="/">首页</a></li>
            <li><a href="https://telegram.me/NGZK46DiaryBot" target="_blank" class="mini">Telegram 订阅</a></li>
        </ul>
    </amp-sidebar>

    <button on="tap:sidebar.toggle" class="ampstart-btn"></button>
</body>
<amp-analytics type="googleanalytics" id="analytics1">
    <script type="application/json">
{
  "vars": {
    "account": "UA-52774518-3"
  },
  "triggers": {
    "trackPageview": {
      "on": "visible",
      "request": "pageview"
    }
  }
}
</script>
</amp-analytics>
</html>